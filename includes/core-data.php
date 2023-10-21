<?php

// Plugin core data
global $svsw__;

$svsw__ = array(
    'notice' => array(), // for keeping a copy of admin notices
);

$svsw__['plugin'] = array(
    'name' => 'Simple Variation Swatches', // plugin name
    'version'=> '1.1.1', // current plugin version
    'notice_interval' => 15, // show info notice at this interval | in days
);

// add external links
$svsw__['plugin']['wporg_url'] = 'https://webfixlab.com/plugins/simple-variation-swatches-woocommerce/?wporg_url'; // Plugin URL | WordPress.org domain
$svsw__['plugin']['review_url'] = 'https://wordpress.org/support/plugin/simple-variation-swatches/reviews/?rate=5#new-post'; // URL for posting plugin review | WordPress.org domain
$svsw__['plugin']['request_quote'] = 'https://webfixlab.com/contact/'; // URL to ask for customization | WebFix Lab.com domain
$svsw__['plugin']['documentation'] = 'https://docs.webfixlab.com/kb/simple-variation-swatches-for-woocommerce/'; // Plugin documentation URL | WebFix Lab.com domain

$svsw__['plugin']['wc_url'] = 'https://webfixlab.com/plugins/simple-variation-swatches-woocommerce/?wc_url'; // WooCommerce plugin URL | WordPress.org domain

// screens
$svsw__['plugin']['screen'] = array(
    'toplevel_page_svsw-settings', // simple variation swatches settings page
    'product' // product related page here
);

// for attribute screen only
$svsw__['plugin']['screen_bases'] = array(
    'product_page_product_attributes',
    'edit-tags',
    'term'
);

// product attribute types
$svsw__['attribute_types'] = array(
    'select' => __( 'Select', 'woocommerce' ),
    'color' => 'Color',
    'image' => 'Image',
    'button' => 'Button',
    'radio' => 'Radio',
);

// hook to modify global $svsw__ data variable
do_action( 'svsw_modify_core_data' );
