<?php
/**
 * Plugin Name:          Simple Variation Swatches for WooCommerce
 * Plugin URI:           https://webfixlab.com/plugins/role-based-pricing-woocommerce/
 * Description:          Variation swatches for WooCommerce
 * Author:               WebFix Lab
 * Author URI:           https://webfixlab.com/
 * Version:              3.0.0
 * Requires at least:    4.9
 * Tested up to:         6.9.1
 * Requires PHP:         7.0
 * WC requires at least: 3.6
 * WC tested up to:      10.5.3
 * License:              GPL2
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins:     woocommerce
 * Text Domain:          simple-variation-swatches
 *
 * @package              Simple variation swatches
 */

defined( 'ABSPATH' ) || exit;

// plugin path.
define( 'SVSW', __FILE__ );
define( 'SVSW_VER', '3.0.0' );
define( 'SVSW_PATH', plugin_dir_path( SVSW ) );

require SVSW_PATH . 'includes/class-svsw-loader.php';
