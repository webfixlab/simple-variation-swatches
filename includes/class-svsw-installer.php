<?php
/**
 * Swatch plugin installer class
 *
 * @package    WordPress
 * @subpackage Simple Variation Swatches
 * @since      2.0
 */

if ( ! class_exists( 'SVSW_Installer' ) ) {

	/**
	 * Simple variation swatch loader class
	 */
	class SVSW_Installer {

        public static function install(){
            if( !self::has_wc() ){
                add_action( 'admin_notices', array( __CLASS__, 'missing_wc' ) );
                return false;
            }

            // register plugin activation hooks.
            register_activation_hook( SVSW, array( __CLASS__, 'activate' ) );
			register_deactivation_hook( SVSW, array( __CLASS__, 'deactivate' ) );

            // add extra links right under plug.
			add_filter( 'plugin_action_links_' . plugin_basename( SVSW ), array( __CLASS__, 'action_links' ) );
			add_filter( 'plugin_row_meta', array( __CLASS__, 'desc_meta' ), 10, 2 );

            return true;
        }

        public static function has_wc(){
			if( !function_exists( 'is_plugin_active' ) ){
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugin           = 'simple-variation-swatches/simple-variation-swatches.php';
			$is_base_active   = is_plugin_active( 'woocommerce/woocommerce.php' );
			$is_plugin_active = is_plugin_active( $plugin );

			// if base plugin is active but woocommer is not, skip.
			if ( ! $is_base_active && $is_plugin_active ) {
				deactivate_plugins( $plugin );
				return false;
			}

			return true;
		}

        /**
		 * Notice for base plugin missing
		 */
		public static function missing_wc() {
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
		 * Activate plugin functionality
		 */
		public static function activate() {
			// self::do_activate();
			flush_rewrite_rules();
		}

		/**
		 * Deactivate plugin functionlity
		 */
		public static function deactivate() {
			flush_rewrite_rules();
		}

        /**
		 * Add plugin action links on all plugins page
		 *
		 * @param array $links current plugin action links.
		 */
		public static function action_links( $links ) {
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
		public static function desc_meta( $links, $file ) {
			global $svsw__;

			// if it's not Role Based Product plugin, return.
			if ( plugin_basename( SVSW ) !== $file ) {
				return $links;
			}

			$row_meta = array();

			$row_meta['apidocs'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $svsw__['urls']['support'] ),
				esc_html__( 'Support', 'simple-variation-swatches' )
			);

			return array_merge( $links, $row_meta );
		}
    }
}
