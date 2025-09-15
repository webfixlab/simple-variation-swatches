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
		public function init() {
			add_action( 'init', array( $this, 'do_activate' ) );
			register_activation_hook( SVSW, array( $this, 'activate' ) );
			register_deactivation_hook( SVSW, array( $this, 'deactivate' ) );

			add_action( 'before_woocommerce_init', array( $this, 'wc_init' ) );
		}

		/**
		 * Activate plugin functionality
		 */
		public function activate() {
			$this->do_activate();
			flush_rewrite_rules();
		}

		/**
		 * Deactivate plugin functionlity
		 */
		public function deactivate() {
			flush_rewrite_rules();
		}

		/**
		 * Plugin activation process
		 */
		public function do_activate() {
			include SVSW_PATH . 'includes/core-data.php';

			// check prerequisits.
			if ( ! $this->should_activate() ) {
				return;
			}

			// add extra links right under plug.
			add_filter( 'plugin_action_links_' . plugin_basename( SVSW ), array( $this, 'action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'desc_meta' ), 10, 2 );

			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			include SVSW_PATH . 'includes/class/admin/class-svsw-admin-swatch.php';
			include SVSW_PATH . 'includes/class/admin/class-svsw-settings.php';

			include SVSW_PATH . 'includes/class/class-svsw.php';
		}

		/**
		 * If we should activate the plugin
		 */
		public function should_activate() {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			$plugin           = 'simple-variation-swatches/simple-variation-swatches.php';
			$is_base_active   = is_plugin_active( 'woocommerce/woocommerce.php' );
			$is_plugin_active = is_plugin_active( $plugin );

			// if base plugin is active but woocommer is not, skip.
			if ( ! $is_base_active && $is_plugin_active ) {
				deactivate_plugins( $plugin );
				add_action( 'admin_notices', array( $this, 'base_inactive_notice' ) );

				return false;
			}

			$this->ask_feedback();
			return true;
		}

		/**
		 * WooCommerce High-Performance Order Storage (HPOS) compatibility enable
		 */
		public function wc_init() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SVSW, true );
			}
		}

		/**
		 * Add frontend scripts and styles
		 */
		public function frontend_scripts() {
			if ( ! $this->in_front_scope() ) {
				return;
			}

			wp_enqueue_style( 'svsw-frontend', plugin_dir_url( SVSW ) . 'assets/frontend.css', array(), SVSW_VER, 'all' );

			wp_register_script( 'svsw-frontend', plugin_dir_url( SVSW ) . 'assets/frontend.js', array( 'jquery' ), SVSW_VER, true );
			wp_enqueue_script( 'svsw-frontend' );

			// localize script.
			$local_var = array(
				'svsw' => 'yes',
			);
			wp_localize_script( 'svsw-frontend', 'wcsvw_frontend', $local_var );
		}

		/**
		 * Add admin scripts and styles
		 */
		public function admin_scripts() {
			if ( ! $this->is_in_scope() ) {
				return;
			}

			// enqueue style.
			wp_register_style( 'svsw-admin-style', plugin_dir_url( SVSW ) . 'assets/admin/admin.css', array(), SVSW_VER );
			wp_enqueue_style( 'svsw-admin-style' );
			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_script( 'jquery' );

			// load media uploader script.
			wp_enqueue_media();

			wp_register_script( 'svsw-admin-script', plugin_dir_url( SVSW ) . 'assets/admin/admin.js', array( 'wp-color-picker' ), SVSW_VER, true );
			wp_enqueue_script( 'svsw-admin-script' );

			$var = array(
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'ajax-nonce' ),
				'img_delete' => esc_html__( 'Are you sure you want to delete the image?', 'simple-variation-swatches' ),
			);

			// apply hook for editing localized variables in admin script.
			$var = apply_filters( 'svsw_update_admin_local_val', $var );

			wp_localize_script( 'svsw-admin-script', 'svsw_admin_data', $var );
		}

		/**
		 * Admin head functionlity : move notices and add menu css
		 */
		public function admin_head() {
			$this->move_admin_notice();
			$this->menu_icon_css();
		}

		/**
		 * Add admin bar menu of the plugin
		 */
		public function admin_menu() {
			// Main menu.
			add_menu_page(
				esc_html__( 'Variation Swatch', 'simple-variation-swatches' ),
				esc_html__( 'Variation Swatch', 'simple-variation-swatches' ),
				'manage_options',
				'svsw-settings',
				array( $this, 'settings_page' ),
				plugin_dir_url( SVSW ) . 'assets/images/admin-icon.svg',
				57
			);

			// settings submenu - settings.
			add_submenu_page(
				'svsw-settings',
				esc_html__( 'Variation Swatch - Settings', 'simple-variation-swatches' ),
				'Settings',
				'manage_options',
				'svsw-settings'
			);
		}



		/**
		 * Render plugin settings page
		 */
		public function settings_page() {
			// check user capabilities.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// show error/update messages.
			settings_errors( 'wporg_messages' );

			// Display admin html content.
			$settings_class = new SVSW_Settings();
			$settings_class->settings_page();
		}



		/**
		 * Add plugin action links on all plugins page
		 *
		 * @param array $links current plugin action links.
		 */
		public function action_links( $links ) {
			$action_links = array();

			$action_links['settings'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=svsw-settings' ),
				esc_html__( 'Settings', 'simple-variation-swatches' )
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Add plugin description meta data on all plugins page
		 *
		 * @param array  $links all meta data.
		 * @param string $file  plugin base file name.
		 */
		public function desc_meta( $links, $file ) {
			global $svsw__;

			// if it's not Role Based Product plugin, return.
			if ( plugin_basename( SVSW ) !== $file ) {
				return $links;
			}

			$row_meta         = array();
			$row_meta['docs'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $svsw__['urls']['docs'] ),
				esc_html__( 'Docs', 'simple-variation-swatches' )
			);

			$row_meta['apidocs'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $svsw__['urls']['support'] ),
				esc_html__( 'Support', 'simple-variation-swatches' )
			);

			return array_merge( $links, $row_meta );
		}

		/**
		 * Ask user feedback notice in every 15 days
		 */
		public function ask_feedback() {
			global $svsw__;

			if ( isset( $_GET['ntnonce'] ) && ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['ntnonce'] ) ), 'svsw_rating_nonce' ) ) {
				return;
			}

			$task = isset( $_GET['rate_svsw'] ) ? sanitize_text_field( wp_unslash( $_GET['rate_svsw'] ) ) : '';

			if ( 'done' === $task ) {
				update_option( 'rate_svsw', 'done' );
			} elseif ( 'cancel' === $task ) {
				update_option( 'rate_svsw', gmdate( 'Y-m-d' ) );
			}

			if ( ! empty( $task ) ) {
				return;
			}

			// show notice to rate us every 15 days.
			if ( $this->if_show_notice( 'rate_svsw' ) ) {
				add_action( 'admin_notices', array( $this, 'feedback_notice' ) );
			}
		}

		/**
		 * Move admin notices and remove all for displaying them later in the intended position
		 */
		public function move_admin_notice() {
			global $svsw__;

			// check scope, without it return.
			if ( ! $this->is_in_scope() ) {
				return;
			}

			// Buffer only the notices.
			ob_start();
			do_action( 'admin_notices' );
			$content = ob_get_contents();
			ob_get_clean();

			// Keep the notices in global $svsw__.
			array_push( $svsw__['notice'], $content );

			// Remove all admin notices as we don't need to display in it's place.
			remove_all_actions( 'admin_notices' );
		}

		/**
		 * Add admin bar menu css style
		 */
		public function menu_icon_css() {
			?>
			<style>
				#toplevel_page_svsw-settings img {
					width: 18px;
					opacity: 1 !important;
				}
				.notice h3{
					margin-top: .5em;
					margin-bottom: 0;
				}
			</style>
			<?php
		}



		/**
		 * Notice for base plugin missing
		 */
		public function base_inactive_notice() {
			global $svsw__;

			$plugin = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( $svsw__['urls']['plugin'] ),
				esc_html( $svsw__['name'] )
			);
			$base   = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( $svsw__['urls']['wc'] ),
				esc_html__( 'WooCommerce', 'simple-variation-swatches' )
			);
			?>
			<div class="error">
				<p>
					<?php
						printf(
							// translators: %1$s: plugin name with url, %2$s: base plugin with url.
							esc_html__( '%1$s plugin has been deactivated due to deactivation of %2$s plugin', 'simple-variation-swatches' ),
							wp_kses_post( $plugin ),
							wp_kses_post( $base )
						);
					?>
				</p>
			</div>
			<?php
		}

		/**
		 * User feedback notice
		 */
		public function feedback_notice() {
			global $svsw__;

			$page  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$page .= false !== strpos( $page, '?' ) ? '&' : '?';
			$page .= 'ntnonce=' . wp_create_nonce( 'svsw_rating_nonce' ) . '&';

			$plugin = sprintf(
				'<strong><a href="%s">%s</a></strong>',
				esc_url( $svsw__['urls']['plugin'] ),
				esc_html( $svsw__['name'] )
			);

			$review = sprintf(
				'<strong><a href="%s">%s</a></strong>',
				esc_url( $svsw__['urls']['review'] ),
				esc_html__( 'WordPress.org', 'simple-variation-swatches' )
			);
			?>
			<div class="notice notice-info is-dismissible">
				<h3><?php echo esc_html( $svsw__['name'] ); ?></h3>
				<p>
					<?php
						printf(
							// translators: %1$s: plugin name with url, %2$s: plugin review url on WordPress.
							esc_html__( 'Excellent! You\'ve been using %1$s for a while. We\'d appreciate if you kindly rate us on %2$s', 'simple-variation-swatches' ),
							wp_kses_post( $plugin ),
							wp_kses_post( $review )
						);
					?>
				</p>
				<p>
					<?php
						printf(
							'<a href="%s" class="button-primary">%s</a>&nbsp;',
							esc_url( $svsw__['urls']['plugin'] ),
							esc_html__( 'Rate it', 'simple-variation-swatches' )
						);
						printf(
							'<a href="%srate_svsw=done" class="button">%s</a>&nbsp;',
							esc_url( $page ),
							esc_html__( 'Already Did', 'simple-variation-swatches' )
						);
						printf(
							'<a href="%srate_svsw=cancel" class="button">%s</a>',
							esc_url( $page ),
							esc_html__( 'Cancel', 'simple-variation-swatches' )
						);
					?>
				</p>
			</div>
			<?php
		}



		/**
		 * Check if the plugin is in intended scope
		 */
		public function is_in_scope() {
			global $svsw__;

			$screen = get_current_screen();

			// check with our plugin screens.
			if ( in_array( $screen->base, $svsw__['admin_scopes'], true ) || 'product' === $screen->post_type ) {
				return true;
			}

			return false;
		}

		/**
		 * Check scopes to load scripts and styles frontend
		 */
		public function in_front_scope() {
			global $post;

			if ( ! isset( $post ) || ! isset( $post->post_type ) ) {
				return false;
			}

			if ( 'product' === $post->post_type && is_single() ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if the 15 days period passed for the notice key or is it done displaying
		 *
		 * @param string $key option meta key to determing the notice type.
		 */
		public function if_show_notice( $key ) {
			global $svsw__;

			$value = get_option( $key );

			if ( empty( $value ) ) {
				return true;
			}

			// if notice is done displaying forever?
			if ( 'done' === $value ) {
				return false;
			}

			// see if interval period passed.
			$difference  = date_diff( date_create( gmdate( 'Y-m-d' ) ), date_create( $value ) );
			$days_passed = (int) $difference->format( '%d' );

			return $days_passed <= $svsw__['notice_interval'] ? false : true;
		}
	}
}

$svsw_loader_class = new SVSW_Loader();
$svsw_loader_class->init();
