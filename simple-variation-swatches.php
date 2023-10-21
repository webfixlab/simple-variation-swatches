<?php
/*
Plugin Name: Simple Variation Swatches for WooCommerce
Description: A truly lightweight EASY to use and super FAST WooCommerce variation swatches solution to replace default variation dropdown with button, color, image & radio button fields.
Author: WebFix Lab
Author URI: https://webfixlab.com/
Version: 1.1.1
Requires at least: 4.9
Tested up to: 6.3.1
Requires PHP: 7.0
WC requires at least: 3.6
WC tested up to: 8.0.3
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: simple-variation-swatches
*/
defined( 'ABSPATH' ) || exit;

// plugin path
define( 'SVSW', __FILE__ ); // single product total
define( 'SVSW_PATH', plugin_dir_path( SVSW ) );

include( SVSW_PATH . 'includes/loader.php');
