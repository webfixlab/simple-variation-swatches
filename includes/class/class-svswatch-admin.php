<?php

/**
 * Simple Variation Swatch Frontend Functions.
 */

if ( ! class_exists( 'SVSwatchAdmin' ) ) {
    class SVSwatchAdmin {
        // private $data; // swatch settings data.

        function __construct(){}
        public function init(){
            add_action( 'admin_init', array( $this, 'init_swatch' ) );

            add_action( 'create_term', array( $this, 'pre_swatch_save' ), 10, 3 );
            add_action( 'created_term', array( $this, 'pre_swatch_save' ), 10, 3 );
            add_action( 'edited_term', array( $this, 'pre_swatch_save' ), 10, 3 );

            add_action( 'add_tag_form_fields', array( $this, 'add_swatch_metabox' ), 20, 1 );
        }


        public function init_swatch(){
        
            // attribute type selection options
            add_filter( 'product_attributes_type_selector', array( $this, 'set_swatch_types' ), 20 );
            
            // get all woocomerce attributes
            $atts = wc_get_attribute_taxonomies();
            
            // add custom hook to support our plugin stuff here
            foreach( $atts as $tax ){
        
                // edit term input fields, display
                $name = wc_attribute_taxonomy_name( $tax->attribute_name );
        
                // add custom field to attribute edit form
                add_action( 'pa_' . $tax->attribute_name . '_edit_form', array( $this, 'pre_swatch_field' ), 20, 2 );
        
                // custom column - for color and image type attribute only
                if( !empty( $tax->attribute_type ) && ( $tax->attribute_type == 'color' || $tax->attribute_type == 'image' ) ){
        
                    // add content to custom column created
                    add_filter( 'manage_' . $name . '_custom_column', array( $this, 'attribute_custom_column' ), 10, 3 );
        
                    // add new custom column to attribute list
                    add_filter( 'manage_edit-' . $name . '_columns', array( $this, 'attribute_column_list' ), 20, 1 );
        
                }
            }

        }
        public function pre_swatch_save( $term_id, $tt_id, $taxonomy ){

            // save attribute type
            if( isset( $_POST['attribute_type'] ) ){
                update_term_meta( $term_id, 'attribute_type', sanitize_text_field( $_POST['attribute_type'] ) );
            }
            
            // save other custom input fields
            $this->save_swatch( $term_id );

        }
        public function add_swatch_metabox( $taxonomy ){

            $atts = wc_get_attribute_taxonomies();
        
            foreach( $atts as $tax ){
                // get attribute name
                $name = wc_attribute_taxonomy_name( $tax->attribute_name );
        
                if( $name == $taxonomy ){
                    // display attribute types select dropdown
                    $this->swatch_type( $tax->attribute_type );
        
                    //display color here
                    $this->input_field( $tax->attribute_type );
                }
            }
            
        }


        public function save_swatch( $term_id ){

            // color field
            if( isset( $_POST['svsw_color'] ) ) update_term_meta( $term_id, 'svsw_color', sanitize_text_field( $_POST['svsw_color'] ) );
            
            // button field
            if( isset( $_POST['svsw_button'] ) ) update_term_meta( $term_id, 'svsw_button', sanitize_text_field( $_POST['svsw_button'] ) );
            
            // radio button field
            if( isset( $_POST['svsw_radio'] ) ) update_term_meta( $term_id, 'svsw_radio', sanitize_text_field( $_POST['svsw_radio'] ) );
            
            // handle uploading image
            if( isset( $_POST['svsw_uploaded_image'] ) ) update_term_meta( $term_id, 'svsw_image', sanitize_url( $_POST['svsw_uploaded_image'] ) );
            
            // color field tooltip
            if( isset( $_POST['svsw_color_tooltip'] ) ) update_term_meta( $term_id, 'svsw_color_tooltip', sanitize_text_field( $_POST['svsw_color_tooltip'] ) );
        
            // image field tooltip
            if( isset( $_POST['svsw_image_tooltip'] ) ) update_term_meta( $term_id, 'svsw_image_tooltip', sanitize_text_field( $_POST['svsw_image_tooltip'] ) );

        }
        public function set_swatch_types(){

            global $svsw__;
        
            // if anything goes wrong, return this
            $blank = array( 'select' => __( 'Select', 'woocommerce' ) );
        
            $screen = get_current_screen();
        
            if( empty( $screen ) ) return $blank;
        
            // only allow type for admin product attribute section
            if( $screen->post_type != 'product' || ! in_array( $screen->base, $svsw__['plugin']['screen_bases'] ) ) return $blank;
        
            return $svsw__['attribute_types'];

        }
        public function pre_swatch_field( $tag, $taxonomy ){

            global $svsw__;
        
            // get attribute/term type
            $type = get_term_meta( $tag->term_id, 'attribute_type', true );
        
            // display attribute/term types select dropdown
            $this->swatch_type( $type );
        
            $values = array(
                'button' => get_term_meta( $tag->term_id, 'svsw_button', true ),
                'color' => get_term_meta( $tag->term_id, 'svsw_color', true ),
                'svsw_color_tooltip' => get_term_meta( $tag->term_id, 'svsw_color_tooltip', true ),
                'radio' => get_term_meta( $tag->term_id, 'svsw_radio', true ),
                'image' => get_term_meta( $tag->term_id, 'svsw_image', true ),
                'svsw_image_tooltip' => get_term_meta( $tag->term_id, 'svsw_image_tooltip', true )
            );
            
            //display color here
            $this->input_field( $type, $values );

        }
        public function attribute_custom_column( $content, $column_name, $term_id ){

            $type = $value = $html = '';
        
            // get attribute type and respective input field value
            $type = get_term_meta( $term_id, 'attribute_type', true );
            if( !empty( $type ) ) $value = get_term_meta( $term_id, 'svsw_' . $type, true );
        
            if( !empty( $value ) ){
                // color or image field html
                $html = '<div class="svsw-value">';
            
                if( $type == 'color' ) $html .= '<div style="background-color: ' . esc_html( $value ) . ';"></div>';
                elseif( $type == 'image' ) $html .= '<div style="background: url(' . esc_url( $value ) . ') no-repeat; background-size: cover;"></div>';
            
                $html .= '</div>';
            }
        
            return $content . $html;
            
        }
        public function attribute_column_list( $columns ) {

            return array_merge( array(
                'cb'    => '',
                'color' => ''
            ), $columns );

        }
        


        public function input_field( $type, $values = array() ){
    
            // get saved values - if any
            $button = $radio = $color = $colortooltip = $image = $imagetooltip = '';
        
            if( isset( $values ) ){
                if( isset( $values['button'] ) ) $button = $values['button'];
                if( isset( $values['radio'] ) ) $radio = $values['radio'];
                if( isset( $values['color'] ) ) $color = $values['color'];
                if( isset( $values['image'] ) ) $image = $values['image'];
                if( isset( $values['svsw_color_tooltip'] ) ) $colortooltip = $values['svsw_color_tooltip'];
                if( isset( $values['svsw_image_tooltip'] ) ) $imagetooltip = $values['svsw_image_tooltip'];
            }
        
            // set default values
            // if( empty( $type ) ) $type = 'button';
            if( empty( $color ) ) $color = '#effeff';
        
            ?>
            <div class="svsw-edit-tag-wrap" data-type="<?php echo esc_attr( $type ); ?>">
                <div class="form-field svsw-input-field svsw-input-button"<?php echo $type != 'button' ? ' style="display: none;"' : ''; ?>>
                    <label for="tag-name">Label</label>
                    <input name="svsw_button" type="text" value="<?php echo esc_html( $button ); ?>">
                </div>
                <div class="form-field svsw-input-field svsw-input-color"<?php echo $type != 'color' ? ' style="display: none;"' : ''; ?>>
                    <label for="tag-name">Color</label>
                    <input name="svsw_color" type="text" value="<?php echo esc_html( $color ); ?>" class="svsw-color-field" data-default-color="<?php echo esc_html( $color ); ?>">
                    <input name="svsw_color_tooltip" type="text" value="<?php echo esc_html( $colortooltip ); ?>" placeholder="Tooltip" class="admin-tooltip">
                </div>
                <div class="form-field svsw-input-field svsw-input-radio"<?php echo $type != 'radio' ? ' style="display: none;"' : ''; ?>>
                    <label for="tag-name">Label</label>
                    <input name="svsw_radio" type="text" value="<?php echo esc_html( $radio ); ?>">
                </div>
                <div class="form-field svsw-input-field svsw-input-image"<?php echo $type != 'image' ? ' style="display: none;"' : ''; ?>>
                    <input type="hidden" name="svsw_uploaded_image" class="svsw-uploaded-image regular-text" value="<?php echo esc_url( $image ); ?>">
                    <input type="button" name="svsw_upload_image" class="svsw-upload-image button-secondary" value="Upload Image">
                    <input name="svsw_image_tooltip" type="text" value="<?php echo esc_html( $imagetooltip ); ?>" placeholder="Tooltip" class="admin-tooltip">
                    <?php
                    if( !empty( $image ) ) echo sprintf( '<img src="%s" class="svsw-admin-img">', esc_url( $image ) );
                    ?>
                </div>
            </div>
            <?php
        
        }
        public function swatch_type( $att_type = '' ){

            // get all types
            $types = apply_filters( 'product_attributes_type_selector', array() );
        
            // skip if no types found
            if( empty( $types ) || ! is_array( $types ) ) return;
        
            // display attribute types dropdown
            ?>
            <div class="form-field">
                <label for="attribute_type">Type</label>
                <select name="attribute_type" class="svsw-att-type">
                    <!-- <option value="select">Select</option> -->
                    <?php foreach( $types as $key => $value ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $att_type, $key ); ?>><?php echo esc_html( $value ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php
        }
    }
}

$cls = new SVSwatchAdmin();
$cls->init();
