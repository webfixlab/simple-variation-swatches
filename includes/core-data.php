<?php
/**
 * Plugin data struction
 *
 * @package    WordPress
 * @subpackage Simple Variation Swatches
 * @since      2.0
 */

global $svsw__;

$svsw__ = array(
	'name'            => __( 'Simple Variation Swatches', 'simple-variation-swatches' ),
	'notice'          => array(),
	'notice_interval' => 15, // in days.
);

// admin scopes to allow this plugin.
$svsw__['admin_scopes'] = array(
	'toplevel_page_svsw-settings', // simple variation swatches settings page.
	'product_page_product_attributes',
	'edit-tags',
	'term',
);

$svsw__['urls'] = array(
	'plugin'  => 'https://webfixlab.com/plugins/simple-variation-swatches-woocommerce/',
	'support' => 'https://webfixlab.com/contact/',
	'review'  => 'https://wordpress.org/support/plugin/simple-variation-swatches/reviews/?rate=5#new-post',
	'wc'      => 'https://wordpress.org/plugins/woocommerce/',
);

// product attribute types.
$svsw__['attribute_types'] = array(
	'select' => __( 'Select', 'simple-variation-swatches' ),
	'color'  => __( 'Color', 'simple-variation-swatches' ),
	'image'  => __( 'Image', 'simple-variation-swatches' ),
	'button' => __( 'Button', 'simple-variation-swatches' ),
	'radio'  => __( 'Radio', 'simple-variation-swatches' ),
);

// swatch settings fields.
$svsw__['fields'] = array(
	'attr_to_swatches'    => 'select',
	'variation_behavior'  => 'select',
	'hide_attr_name'      => 'checkbox',
	'image_swatch_style'  => 'select',
	'color_swatch_style'  => 'select',
	'svsw_size_image'     => 'number',
	'svsw_size_color'     => 'number',
	'svsw_font_size'      => 'number',
	'att_name_underline'  => 'checkbox',
	'att_name_color'      => 'text',
	'att_name_background' => 'text',
	'att_block_design'    => 'radio',
);

// hook to modify global data variable.
do_action( 'svsw_modify_core_data' );
