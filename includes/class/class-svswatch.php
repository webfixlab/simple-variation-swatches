<?php

/**
 * Simple Variation Swatch Frontend Functions.
 */

if ( ! class_exists( 'SVSwatch' ) ) {
    class SVSwatch {
        private $data; // swatch settings data.
        private $default; // for default attribute value selection purpose.
        private $atname; // currently working on attribute name.

        function __construct(){

            // get saved settings data, if any
            $this->data = get_option( 'svsw_settings' );

        }
        public function init(){

            add_action( 'woocommerce_variable_add_to_cart', array( $this, 'init_swatch' ), 29 );

        }


        public function init_swatch(){

            global $product;
        
            // get product attributes
            $attributes = $product->get_variation_attributes();
            $this->default = $product->get_default_attributes();
        
            // get saved settings data, if any
            $data = $this->data;
        
            // wheather to display attribute name
            $show_display_name = false;
            $hide_attr = 'svsw-hide-attr';
            if( !isset( $data['hide_attr_name'] ) || ( isset( $data['hide_attr_name'] ) && $data['hide_attr_name'] != 'on' ) ){
                $show_display_name = true;
                $hide_attr = '';
            }
        
            echo '<div class="svsw-frontend-wrap ' . esc_attr( $hide_attr ) . '">';
        
            foreach( $attributes as $attribute_name => $options ){

                $this->atname = $attribute_name;
                $this->display_swatch( $attribute_name, $options, $show_display_name );

            }
        
            echo '</div>';

        }
        public function display_swatch( $attribute_name, $options, $show_display_name ){

            /**
             * @param $taxonomy - attribute name
             * @param $term_list | attribute options | array
             * @param $show_display_name | wheather to display attribute name or not | boolean
             */
            $att_name = $attribute_name;
        
            // first check if it's global attribute or not
            $terms = get_terms( $attribute_name );
        
            if( ! is_wp_error( $terms ) ){
                // to find actual name, get taxonomy object
                $tax_obj = get_taxonomy( $attribute_name );
        
                if( isset( $tax_obj->labels ) && isset( $tax_obj->labels->singular_name ) ){
                    $att_name = $tax_obj->labels->singular_name;
                }
            }
        
            // scope for global attributes end here
            // if( empty( $terms ) || !is_array( $terms ) ) return;
        
            // get saved settings data, if any
            $data = $this->data;
        
            echo '<div class="svsw-wrap">';
        
            // wheather to display attribute name or not
            if( $show_display_name ){
                echo '<label class="attr-name">' . esc_html( $att_name ) . '</label>';
            }
        
            echo '<div class="svsw-attr-wrap" data-taxonomy="' . esc_attr( sanitize_title( $attribute_name ) ) . '">';
        
            // display swatches
            if( is_wp_error( $terms ) ){
                $this->skipped_swatch( $options, $data, $attribute_name );
            }else{
                $this->swatch_item( $terms, $options, $data, $att_name );
            } 
        
            echo '</div></div>';

        }



        public function skipped_swatch( $skipped_terms, $settings, $attribute_name ){

            if( empty( $skipped_terms ) ) return;
        
            // display everything dropdown
            $variation_to = 'default';
        
            // if settings enabled to convert dropdown attributes to swatches
            if( isset( $settings['attr_to_swatches'] ) && !empty( $settings['attr_to_swatches'] ) ) $variation_to = $settings['attr_to_swatches'];
        
            
            
            if( $variation_to == 'default' ){
                echo sprintf( '<select name="%s" data-term="%s" class="svsw-swatch-dropdown">', esc_attr( $attribute_name ), esc_attr( $attribute_name ) );
        
                echo '<option value="">Choose an option</option>';
            }
        
            foreach( $skipped_terms as $opt_name => $opt_value ){

                if( is_numeric( $opt_name ) ){
                    $opt_name = $opt_value;
                }
        
                if( $variation_to == 'default' ){

                    $checked = isset( $this->default[ $this->atname ] ) && $opt_name === $this->default[ $this->atname ] ? 'selected' : '';

                    echo sprintf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr( $opt_name ),
                        esc_attr( $checked ),
                        esc_html( $opt_value ) 
                    );

                }elseif( $variation_to == 'radio' ){
                    $this->swatch_field( $settings, $opt_name, 'radio', $opt_value );
                }elseif( $variation_to == 'button' ){
                    $this->swatch_field( $settings, $opt_name, 'button', $opt_value );
                }
            }
        
            if( $variation_to == 'default' ){
                echo '</select>';
            }

        }
        public function swatch_item( $terms, $options, $data, $attribute_name ){

            // list of unavailable swatch items
            $skipped_terms = array();

            $terms = $this->sort_terms( $terms, $options, $attribute_name );
        
            // get term object from term here
            foreach( $terms as $term ){
                // if( ! in_array( $term->slug, $options, true ) ) continue;
        
                // swatch type
                $type = get_term_meta( $term->term_id, 'attribute_type', true );
        
                // if no type found - skip this
                if( empty( $type ) ){
                    $skipped_terms[ $term->slug ] = $term->name;                
                    continue;
                }
        
                // swatch value - like button text or image file url
                $value = get_term_meta( $term->term_id, 'svsw_' . $type, true );
                
                if( empty( $value ) ){
                    $skipped_terms[ $term->slug ] = $term->name;
                    continue;
                }
        
                // for color type - get tooltip text
                $color_tooltip = get_term_meta( $term->term_id, 'svsw_color_tooltip', true );

                if( ! empty( $color_tooltip ) && ! empty( $data ) ){
                    $data['tooltip'] = $color_tooltip;
                }
        
                // display swatch html element
                $this->swatch_field( $data, $term->slug, $type, $value );
            }
        
            $this->skipped_swatch( $skipped_terms, $data, $attribute_name );

        }



        public function swatch_field( $data, $slug, $type, $value ){

            $checked = isset( $this->default[ $this->atname ] ) && $slug === $this->default[ $this->atname ] ? 'svsw-selected' : '';

            ?>
            <div class="svsw-swatch-content svsw-type-<?php echo esc_attr( $type ); ?>">
            <?php
        
            if( $type == 'color' ){
                
                // button style
                $type = ' square';
                if( isset( $data['svsw_admin_type'] ) && !empty( $data['svsw_admin_type'] ) ) $type = $data['svsw_admin_type'];
        
                // button width
                $size = 31;
                if( isset( $data['svsw_size'] ) && !empty( $data['svsw_size'] ) ) $size = $data['svsw_size'];
        
                // tooltip
                $tooltip = '';
                if( isset( $data['tooltip'] ) && !empty( $data['tooltip'] ) ) $tooltip = $data['tooltip'];

                echo sprintf(
                    '<span class="svsw-swatch svsw-color-image %s %s" style="background-color: %s; width: %spx; height: %spx;" data-term="%s" data-tooltip="%s" data-term="%s"></span>',
                    esc_attr( $type ),
                    esc_attr( $checked ),
                    esc_html( $value ),
                    esc_attr( $size ),
                    esc_attr( $size ),
                    esc_attr( $slug ),
                    esc_html( $tooltip ),
                    esc_attr( $slug )
                );
        
            }elseif( $type == 'image' ){
                
                // image swatch shape
                $type = ' square';
                if( isset( $data['svsw_admin_type'] ) && !empty( $data['svsw_admin_type'] ) ) $type = $data['svsw_admin_type'];
        
                // image width
                $size = 31;
                if( isset( $data['svsw_size'] ) && !empty( $data['svsw_size'] ) ) $size = $data['svsw_size'];
        
                // tooltip
                $tooltip = '';
                if( isset( $data['tooltip'] ) && !empty( $data['tooltip'] ) ) $tooltip = $data['tooltip'];
        
                /**
                 * without any image set, use default woocommerce placeholder image
                 * 
                 */
                if( !isset( $value ) || empty( $value ) ) $value = $this->img_placeholder();
                
                echo sprintf(
                    '<span class="svsw-swatch svsw-color-image %s %s" style="background: url(%s) no-repeat; background-size: cover; width: %spx; height: %spx;" data-term="%s" data-img="%s" data-tooltip="%s" data-term="%s"></span>',
                    esc_attr( $type ),
                    esc_attr( $checked ),
                    esc_url( $value ),
                    esc_attr( $size ),
                    esc_attr( $size ),
                    esc_attr( $slug ),
                    esc_url( $value ),
                    esc_html( $tooltip ),
                    esc_attr( $slug )
                );
                
            }elseif( $type == 'button' ){
                
                // button text size
                $font_size = 18;
                if( isset( $data['svsw_font_size'] ) && !empty( $data['svsw_font_size'] ) ) $font_size = $data['svsw_font_size'];

                echo sprintf(
                    '<span class="svsw-swatch svsw-btn %s" style="font-size: %spx;" data-term="%s">%s</span>',
                    esc_attr( $checked ),
                    esc_attr( $font_size ),
                    esc_attr( $slug ),
                    esc_html( $value )
                );
        
            }elseif( $type == 'radio' ){
        
                $font_size = 18;
                if( isset( $data['svsw_font_size'] ) && !empty( $data['svsw_font_size'] ) ) $font_size = $data['svsw_font_size'];
                
                ?>
                <div class="svsw-swatch svsw-swatch-radio <?php echo esc_attr( $checked ); ?>" data-term="<?php echo esc_attr( $slug ); ?>">
                    <input type="radio" name="svsw_radio_swatch" value="<?php echo esc_html( $value ); ?>" <?php echo ! empty( $checked ) ? esc_attr( 'checked' ) : ''; ?>>
                    <label style="font-size: <?php echo esc_attr( $font_size ); ?>px;"><?php echo esc_html( $value ); ?></label>
                </div>
                <?php
        
            }
        
            ?>
            </div>
            <?php

        }
        public function img_placeholder(){
            global $svsw__;
        
            // if already found, use that image
            if( isset( $svsw__['wc_placeholder_img'] ) && !empty( $svsw__['wc_placeholder_img'] ) ) return $svsw__['wc_placeholder_img'];
        
            $wc_img = '';
        
            $updir = wp_get_upload_dir();
            $files = glob( $updir['basedir'] . '/woocommerce-placeholder*.png' );
        
            // keep a backup copy of original/uncompressed image    
            $wc_img = $updir['basedir'] . '/woocommerce-placeholder.png';
        
            $sizes = array( 100, 150, 300, 600 );
            foreach( $sizes as $size ){
                $newpath = $updir['basedir'] . '/woocommerce-placeholder-' . $size . 'x' . $size . '.png';
                
                if( in_array( $newpath, $files ) ){
                    $wc_img = $updir['baseurl'] . '/woocommerce-placeholder-' . $size . 'x' . $size . '.png';
                    break;
                }
            }
        
            // keep a backup copy
            $svsw__['wc_placeholder_img'] = $wc_img;
        
            return $wc_img;
        
        }


        public function sort_terms( $terms, $options, $attribute_name ){

            global $product;

            $attribute_name = 'pa_' . $attribute_name;
            $ans = sanitize_title( $attribute_name ); // attribute name sanitized.

            $ids = $product->get_attributes()[ $ans ]->get_options();
        
            global $wpdb;
            $ids_string = implode( ',', $ids );

            $query = "SELECT term_id, meta_value FROM {$wpdb->termmeta} WHERE term_id IN ({$ids_string}) AND meta_key='order' ORDER BY meta_value";
            $result = $wpdb->get_results( $query, ARRAY_A   ); // WPCS: db call ok. // WPCS: cache ok.
        
            // new sorted attribute options.
            $sorted_terms = array();
        
            // which term ids have already been sorted.
            $sorted_ids = array();
        
            if( empty( $result ) || ! is_array( $result ) ){

                $sorted_ids = $ids;

            }else{

                foreach( $result as $row ){
                    array_push( $sorted_ids, $row['term_id'] );
                }

                $sorted_ids = array_merge( $sorted_ids, $ids );
                $sorted_ids = array_unique( $sorted_ids );

            }
        
            foreach( $sorted_ids as $id ) {

                foreach ( $terms as $term ) {

                    if ( $term->term_id === (int) $id ) {
                        $sorted_terms[] = $term;
                    }

                }

            }
        
            return $sorted_terms;

        }
    }
}

$cls = new SVSwatch();
$cls->init();   
