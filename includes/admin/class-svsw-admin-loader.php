<?php
/**
 * Swatch loader class
 *
 * @package    WordPress
 * @subpackage Simple Variation Swatches
 * @since      2.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'SVSW_Admin_Loader' ) ) {

	/**
	 * Simple variation swatch loader class
	 */
	class SVSW_Admin_Loader {

		/**
		 * Plugin activation process
		 */
		public static function init() {
			add_action( 'admin_head', array( __CLASS__, 'admin_head' ) );
			add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );

			self::ask_feedback();
		}

		/**
		 * Admin head functionlity : move notices and add menu css
		 */
		public static function admin_head() {
			self::move_admin_notice();
			self::menu_icon_css();

			self::save_setting();
		}

		/**
		 * Move admin notices and remove all for displaying them later in the intended position
		 */
		public static function move_admin_notice() {
			global $svsw__;

			// Buffer only the notices.
			ob_start();

			do_action( 'admin_notices' );

			$content = ob_get_clean();

			// Keep the notices in global $svsw__.
			array_push( $svsw__['notice'], $content );

			// Remove all admin notices as we don't need to display in it's place.
			remove_all_actions( 'admin_notices' );
		}

		/**
		 * Add admin bar menu css style
		 */
		public static function menu_icon_css() {
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
		 * Save admin settings
		 */
		public static function save_setting() {
			global $svsw__;

			if ( ! isset( $_POST['svsw_nonce_field'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['svsw_nonce_field'] ) ), 'svsw_save' ) ) {
				return;
			}

			$data = array();
			foreach ( $svsw__['fields'] as $key => $type ) {
				if ( isset( $_POST[ $key ] ) ) {
					$data[ $key ] = 'text' === $type ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : sanitize_key( wp_unslash( $_POST[ $key ] ) );
				}
			}

			update_option( 'svsw_settings', $data );
		}

		/**
		 * Add admin bar menu of the plugin
		 */
		public static function admin_menu() {
			add_menu_page( // main admin menu.
				esc_html__( 'Variation Swatch', 'simple-variation-swatches' ),
				esc_html__( 'Variation Swatch', 'simple-variation-swatches' ),
				'manage_options',
				'svsw-settings',
				array( __CLASS__, 'settings_page' ),
				plugin_dir_url( SVSW ) . 'assets/images/admin-icon.svg',
				57
			);

			add_submenu_page( // settings - submenu.
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
		public static function settings_page() {
			// check user capabilities.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// show error/update messages.
			settings_errors( 'wporg_messages' );

			// render settings page.
			SVSW_Admin_Page::settings_page();
		}

		/**
		 * Ask user feedback notice in every 15 days
		 */
		public static function ask_feedback() {
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
			if ( self::if_show_notice( 'rate_svsw' ) ) {
				add_action( 'admin_notices', array( __CLASS__, 'feedback_notice' ) );
			}
		}

		/**
		 * Check if the 15 days period passed for the notice key or is it done displaying
		 *
		 * @param string $key option meta key to determing the notice type.
		 */
		public static function if_show_notice( $key ) {
			global $svsw__;

			$value = get_option( $key );

			if ( empty( $value ) ) {
				update_option( $key, gmdate( 'Y-m-d' ) );
				return false;
			}

			// if notice is done displaying forever?
			if ( 'done' === $value ) {
				return false;
			}

			// see if interval period passed.
			$difference  = date_diff( date_create( gmdate( 'Y-m-d' ) ), date_create( $value ) );
			$days_passed = (int) $difference->format( '%d' );

			return $days_passed < $svsw__['notice_interval'] ? false : true;
		}

		/**
		 * User feedback notice
		 */
		public static function feedback_notice() {
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
	}
}
