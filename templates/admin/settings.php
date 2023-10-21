<?php

global $svsw__;

// get tab if given via URL
$tab = 'general';
if( isset( $_GET['svsw_tab'] ) ) $tab = sanitize_key( $_GET['svsw_tab'] );
elseif( isset( $_POST['svsw_tab'] ) ) $tab = sanitize_key( $_POST['svsw_tab'] );

// handle admin settings value here
$data = get_option( 'svsw_settings' );

$attr_swtaches = $hide_attr_name = '';

if( isset( $data['attr_to_swatches'] ) ) $attr_swtaches = $data['attr_to_swatches'];

if( isset( $data['hide_attr_name'] ) && $data['hide_attr_name'] == 'on' ) $hide_attr_name = ' checked';

$size = 30; 
$font_size = 18;

if( isset( $data['svsw_size'] ) && !empty( $data['svsw_size'] ) ) $size = $data['svsw_size'];

if( isset( $data['svsw_font_size'] ) && !empty( $data['svsw_font_size'] ) ) $font_size = $data['svsw_font_size'];

$options = array(
    'svsw_square' => 'Square',
    'svsw_circle' => 'Circle',
    'svsw_round_corner' => 'Round Corner'
);

?>
<div class="svsw-wrap">
    <div class="svsw-heading">
        <h1 class=""><?php echo esc_html( $svsw__['plugin']['name'] ); ?> - Settings</h1>
        <div class="heading-desc">
            <p>
                <a href="https://docs.webfixlab.com/kb/simple-variation-swatches-for-woocommerce/" target="_blank">DOCUMENTATION</a> | <a href="https://webfixlab.com/request-quote/" target="_blank">SUPPORT</a>
            </p>
        </div>
    </div>
    <div class="svsw-notice">
        <?php svsw_display_notice(); ?>
    </div>
    <div class="svsw-content-wrap">
        <div class="svsw-main">
            <form action="" method="POST">
                <div class="row">
                    <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
                        <a class="nav-tab<?php if( $tab == 'general' ) echo ' nav-tab-active'; ?>" data-target="general">
                            <span class="dashicons dashicons-admin-settings"></span> General
                        </a>
                        <a class="nav-tab<?php if( $tab == 'appearance' ) echo ' nav-tab-active'; ?>" data-target="appearance">
                            <span class="dashicons dashicons-admin-appearance"></span> Appearance
                        </a>
                    </nav>
                </div>
                <div class="svsw-sections">
                    <div class="section svsw-general"<?php echo $tab != 'general' ? ' style="display: none;"' : ''; ?>>
                        <h3>General settings</h3>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Default variation type</label>
                                </th>
                                <!-- attr_to_swatches -->
                                <td class="forminp forminp-text">
                                    <select name="attr_to_swatches">
                                        <option value="">Select</option>
                                        <option value="radio" <?php echo $attr_swtaches == 'radio' ? 'selected' : ''; ?>>Radio Button</option>
                                        <option value="button" <?php echo $attr_swtaches == 'button' ? 'selected' : ''; ?>>Button</option>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Attribute label</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input name="hide_attr_name" type="checkbox"<?php echo esc_html( $hide_attr_name ); ?>>
                                    <label>Hide</label>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="section svsw-appearance"<?php echo $tab != 'appearance' ? ' style="display: none;"' : ''; ?>>
                        <h3>Appearence</h3>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Color and image swatches style</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <select name="svsw_admin_type">
                                        <?php
                                        foreach( $options as $slug => $label ){
                                            $s = '';
                                            if( isset( $data['svsw_admin_type'] ) && $slug == $data['svsw_admin_type'] ) $s = ' selected';

                                            echo sprintf( '<option value="%s"%s>%s</option>', esc_attr( $slug ), esc_html( $s ), esc_html( $label ) );
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Color and image swatches size</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input name="svsw_size" type="number" style="" value="<?php echo esc_attr( $size ); ?>" min="10" max="100"> px
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Font size</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input name="svsw_font_size" type="number" style="" value="<?php echo esc_attr( $font_size ); ?>" min="8" max="50"> px
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php do_action( 'svsw_extra_section' ); ?>
                </div>
                <div class="">
                    <?php wp_nonce_field( 'svsw_save', 'svsw_nonce_field' ); ?>
                    <input type="hidden" name="svsw_tab" value="<?php echo esc_attr( $tab ); ?>">  
                    <input type="submit" value="Save changes" class="button-primary woocommerce-save-button svsw-save">
                </div>
            </form>
        </div>
        <div class="svsw-side">
            <?php include( SVSW_PATH . 'templates/admin/sidebar.php' ); ?>
        </div>
    </div>
</div>