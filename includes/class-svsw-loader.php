<?php
/**
 * Swatch loader class
 *
 * @package    WordPress
 * @subpackage Simple Variation Swatches
 * @since      2.0
 */

if ( ! class_exists( 'SVSW_Loader' ) ) {

	/**
	 * Simple variation swatch loader class
	 */
	class SVSW_Loader {

		/**
		 * Initialize plugin loader
		 */
		public static function init_plugin() {
			include SVSW_PATH . 'includes/class-svsw-installer.php';

			if( !SVSW_Installer::install() ){
				return;
			}

			add_action( 'init', array( __CLASS__, 'init' ) );
			add_action( 'before_woocommerce_init', array( __CLASS__, 'wc_init' ) );
		}

		/**
		 * Plugin activation process
		 */
		public static function init() {
			load_plugin_textdomain( 'simple-variation-swatches', false, plugin_basename( dirname( SVSW ) ) . '/languages' );
			
			self::includes();

			// load admin navigations and pages.
			SVSW_Admin_Loader::init();

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );
		}

		public static function includes(){
			include SVSW_PATH . 'includes/core-data.php';
			
			include SVSW_PATH . 'includes/admin/class-svsw-admin-page.php';
			include SVSW_PATH . 'includes/admin/class-svsw-admin-loader.php';

			include SVSW_PATH . 'includes/admin/class-svsw-attribute-editor.php';

			include SVSW_PATH . 'includes/class-svsw-front.php';
		}

		/**
		 * WooCommerce High-Performance Order Storage (HPOS) compatibility enable
		 */
		public static function wc_init() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SVSW, true );
			}
		}

		/**
		 * Add admin scripts and styles
		 */
		public static function admin_scripts() {
			global $svsw__;

			if ( ! self::in_admin_scopes() ) {
				return;
			}

			// enqueue style.
			wp_register_style( 'svsw_admin_style', plugin_dir_url( SVSW ) . 'assets/admin/admin.css', array(), SVSW_VER );
			wp_enqueue_style( 'svsw_admin_style' );

			// colorpicker.
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

			// load media uploader script.
			wp_enqueue_media();

			wp_enqueue_script( 'svsw_admin_script', plugin_dir_url( SVSW ) . 'assets/admin/admin.js', array( 'jquery' ), SVSW_VER, true );

			$var = array(
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'ajax-nonce' ),
				'img_delete' => esc_html__( 'Are you sure you want to delete the image?', 'simple-variation-swatches' ),
			);

			// apply hook for editing localized variables in admin script.
			$var = apply_filters( 'svsw_update_admin_local_val', $var );
			wp_localize_script( 'svsw_admin_script', 'svsw_admin_data', $var );
		}

		/**
		 * Check if the plugin is in intended scope
		 */
		public static function in_admin_scopes() {
			global $svsw__;

			$screen = get_current_screen();

			// check with our plugin screens.
			if ( in_array( $screen->base, $svsw__['admin_scopes'], true ) || 'product' === $screen->post_type ) {
				return true;
			}

			return false;
		}

		/**
		 * Add frontend scripts and styles
		 */
		public static function frontend_scripts() {
			global $post;
			global $product;

			if( empty( $post ) || ! isset( $post->ID ) ){
				return;
			}

			if( 'object' !== gettype( $product ) ){
				$product = wc_get_product( $post->ID );
			}

			if ( ! self::in_frontend_scopes() ) {
				return;
			}

			wp_register_style( 'svsw-front-css', plugin_dir_url( SVSW ) . 'assets/frontend.css', array(), SVSW_VER, 'all' );
			wp_enqueue_style( 'svsw-front-css' );
			
			wp_register_script( 'svsw-front-js', plugin_dir_url( SVSW ) . 'assets/frontend.js', array( 'jquery' ), SVSW_VER, true );
			wp_enqueue_script( 'svsw-front-js' );

			// localize script.
			$data = array(
				'svsw'     => 'yes',
				'type'     => $product->get_type(),
				'settings' => get_option( 'svsw_settings' )
			);

			if( 'variable' === $data['type'] ){
				$data[ 'variations' ] = $product->get_available_variations();
			}

			wp_localize_script( 'svsw-front-js', 'svsw_front', $data );
		}

		/**
		 * Check scopes to load scripts and styles frontend
		 */
		public static function in_frontend_scopes() {
			global $post;

			if ( ! isset( $post ) || ! isset( $post->post_type ) ) {
				return false;
			}

			if ( 'product' === $post->post_type && is_single() ) {
				return true;
			}

			return false;
		}
	}
}

SVSW_Loader::init_plugin();
