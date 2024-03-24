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
	'version'         => '1.1.2',
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
	'docs'    => 'https://docs.webfixlab.com/kb/simple-variation-swatches-for-woocommerce/',
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

// hook to modify global data variable.
do_action( 'svsw_modify_core_data' );
