<?php
/**
 * Admin settings page function
 *
 * @package    WordPress
 * @subpackage Simple Variation Swatches
 * @since      3.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'SVSW_Admin_Page' ) ) {

	/**
	 * Swatch admin settings functionlity class
	 */
	class SVSW_Admin_Page {

		/**
		 * Settings data
		 *
		 * @var array
		 */
		private static $data;

		/**
		 * Initialize class and get saved settings data
		 */
		public static function setup() {
			self::$data = get_option( 'svsw_settings' );
		}

		/**
		 * Display settings page
		 */
		public static function settings_page() {
			self::setup();
			?>
			<div class="svsw-wrap">
				<?php self::settings_header(); ?>
				<div class="svsw-content-wrap">
					<div class="svsw-main">
						<form action="" method="POST">
							<?php self::settings_content(); ?>
						</form>
					</div>
					<div class="svsw-side">
						<?php include SVSW_PATH . 'templates/admin/sidebar.php'; ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Settings page header
		 */
		public static function settings_header() {
			global $svsw__;
			?>
			<div class="svsw-heading">
				<?php self::get_title(); ?>
				<div class="heading-desc">
					<p>
						<a href="<?php echo esc_url( $svsw__['urls']['support'] ); ?>" target="_blank"><?php echo esc_html__( 'SUPPORT', 'simple-variation-swatches' ); ?></a>
					</p>
				</div>
			</div>
			<div class="svsw-notice">
				<?php self::display_notice(); ?>
			</div>
			<?php
		}

		/**
		 * Settings page content
		 */
		public static function settings_content() {
			$tab = self::get_tab();
			?>
			<div class="row">
				<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
					<?php self::get_menu(); ?>
				</nav>
			</div>
			<div class="svsw-sections">
				<div class="section svsw-general"<?php echo 'general' !== $tab ? ' style="display: none;"' : ''; ?>>
					<h3><?php echo esc_html__( 'General settings', 'simple-variation-swatches' ); ?></h3>
					<table class="form-table">
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Convert attributes to', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php self::att_to_swatch(); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Attribute label', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php self::hide_att_name(); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Out of stock options', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php self::variation_behavior(); ?>
							</td>
						</tr>
					</table>
				</div>
				<div class="section svsw-appearance"<?php echo 'appearance' !== $tab ? ' style="display: none;"' : ''; ?>>
					<h3><?php echo esc_html__( 'Appearance', 'simple-variation-swatches' ); ?></h3>
					<table class="form-table">
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Image swatches style', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php self::swatch_design( 'image' ); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Color swatches style', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php self::swatch_design( 'color' ); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Image swatches size', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php self::swatch_size( 'image' ); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Color swatches size', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php self::swatch_size( 'color' ); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Font size', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php self::font_size(); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Attribute name under line', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php $checked = isset( self::$data['att_name_underline'] ) && 'on' === self::$data['att_name_underline'] ? 'checked' : ''; ?>
								<input name="att_name_underline" type="checkbox"<?php echo esc_attr( $checked ); ?>>
								<label><?php echo esc_html__( 'Show', 'simple-variation-swatches' ); ?></label>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Attribute Name Color', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php $att_name_color = isset( self::$data['att_name_color'] ) ? self::$data['att_name_color'] : ''; ?>
								<input name="att_name_color" type="text" class="svsw-colorpicker" value="<?php esc_html( $att_name_color ); ?>" data-default-color="">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Attribute Name Background', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php $att_name_background = isset( self::$data['att_name_background'] ) ? self::$data['att_name_background'] : ''; ?>
								<input name="att_name_background" type="text" class="svsw-colorpicker" value="<?php esc_html( $att_name_background ); ?>" data-default-color="">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Attribute block design', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php
									$design = isset( self::$data['att_block_design'] ) && ! empty( self::$data['att_block_design'] ) ? self::$data['att_block_design'] : 'default';

									$options = array(
										'default' => __( 'None', 'simple-variation-swatches' ),
										'block-1' => __( 'Round corner', 'simple-variation-swatches' ),
										'block-2' => __( 'Square', 'simple-variation-swatches' ),
									);

									echo '<select name="att_block_design">';
									foreach ( $options as $key => $value ) {
										printf(
											'<option value="%s" %s>%s</option>',
											esc_attr( $key ),
											$key === $design ? 'selected' : '',
											esc_html( $value ),
										);
									}
									echo '</select>';
									?>
							</td>
						</tr>
					</table>
				</div>
				<?php do_action( 'svsw_extra_section' ); ?>
			</div>
			<div class="">
				<?php wp_nonce_field( 'svsw_save', 'svsw_nonce_field' ); ?>
				<input type="hidden" name="svsw_tab" value="<?php echo esc_attr( $tab ); ?>">  
				<input type="submit" value="<?php echo esc_html__( 'Save changes', 'simple-variation-swatches' ); ?>" class="button-primary woocommerce-save-button svsw-save">
			</div>
			<?php
		}

		/**
		 * Get current settings tab
		 */
		public static function get_tab() {
			// default tab.
			$tab = 'general';

			if ( ! isset( $_POST['svsw_nonce_field'] ) ) {
				return $tab;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['svsw_nonce_field'] ) ), 'svsw_save' ) ) {
				return $tab;
			}

			if ( isset( $_GET['svsw_tab'] ) ) {
				$tab = sanitize_key( wp_unslash( $_GET['svsw_tab'] ) );
			} elseif ( isset( $_POST['svsw_tab'] ) ) {
				$tab = sanitize_key( wp_unslash( $_POST['svsw_tab'] ) );
			}

			return $tab;
		}

		/**
		 * Display settings page title
		 */
		public static function get_title() {
			global $svsw__;

			$title = sprintf(
				// translators: Placeholder %1$s is plugin name.
				__( '%1$s - Settings', 'simple-variation-swatches' ),
				esc_html( $svsw__['name'] )
			);

			printf( '<h1 class="">%s</h1>', esc_html( $title ) );
		}

		/**
		 * Display navigation tabs
		 */
		public static function get_menu() {
			// get current tab.
			$tab = self::get_tab();

			$menu = array(
				'general'    => array(
					'label' => __( 'General', 'simple-variation-swatches' ),
					'icon'  => 'admin-settings',
				),
				'appearance' => array(
					'label' => __( 'Appearance', 'simple-variation-swatches' ),
					'icon'  => 'admin-appearance',
				),
			);

			foreach ( $menu as $slug => $item ) {
				printf(
					'<a class="nav-tab %s" data-target="%s"><span class="dashicons dashicons-%s"></span> %s</a>',
					$tab === $slug ? 'nav-tab-active' : '',
					esc_attr( $slug ),
					esc_attr( $item['icon'] ),
					esc_html( $item['label'] )
				);
			}
		}

		/**
		 * Display auto convert attribute options dropdown
		 */
		public static function att_to_swatch() {
			$att_to_swatch = isset( self::$data['attr_to_swatches'] ) ? self::$data['attr_to_swatches'] : '';

			$options = array(
				'radio'  => __( 'Radio Button', 'simple-variation-swatches' ),
				'button' => __( 'Button', 'simple-variation-swatches' ),
			);
			?>
			<select name="attr_to_swatches">
				<option value=""><?php echo esc_html__( 'Choose type', 'simple-variation-swatches' ); ?></option>
				<?php
				foreach ( $options as $val => $label ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $val ),
						$att_to_swatch === $val ? esc_attr( 'selected' ) : '',
						esc_html( $label )
					);
				}
				?>
			</select>
			<?php
		}

		/**
		 * Display hide attribute name settings field
		 */
		public static function hide_att_name() {
			$checked = '';
			if ( isset( self::$data['hide_attr_name'] ) && 'on' === self::$data['hide_attr_name'] ) {
				$checked = 'checked';
			}
			?>
			<input name="hide_attr_name" type="checkbox"<?php echo esc_attr( $checked ); ?>>
			<label><?php echo esc_html__( 'Hide', 'simple-variation-swatches' ); ?></label>
			<?php
		}

		/**
		 * Display variation options behavior
		 */
		public static function variation_behavior() {
			$behave = isset( self::$data['variation_behavior'] ) ? self::$data['variation_behavior'] : '';

			$options = array(
				'avail'   => __( 'Hide', 'simple-variation-swatches' ),
				'disable' => __( 'Show but disabled', 'simple-variation-swatches' ),
			);
			?>
			<select name="variation_behavior">
				<?php
				foreach ( $options as $val => $label ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $val ),
						$behave === $val ? esc_attr( 'selected' ) : '',
						esc_html( $label )
					);
				}
				?>
			</select>
			<?php
		}

		/**
		 * Display swatch types dropdown
		 *
		 * @param string $type either image or color type swatch.
		 */
		public static function swatch_design( $type ) {
			$key = $type . '_swatch_style';

			$design = isset( self::$data[ $key ] ) ? self::$data[ $key ] : '';

			$options = array(
				'svsw_square'       => __( 'Square', 'simple-variation-swatches' ),
				'svsw_circle'       => __( 'Circle', 'simple-variation-swatches' ),
				'svsw_round_corner' => __( 'Round Corner', 'simple-variation-swatches' ),
			);
			?>
			<select name="<?php echo esc_attr( $key ); ?>">
				<option value=""><?php echo esc_html( __( 'Choose shape', 'simple-variation-swatches' ) ); ?></option>
				<?php

				foreach ( $options as $val => $label ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $val ),
						$design === $val ? esc_attr( 'selected' ) : '',
						esc_html( $label )
					);
				}
				?>
			</select>
			<?php
		}

		/**
		 * Display swatch item dimension sized
		 *
		 * @param string $type either image or color type swatch.
		 */
		public static function swatch_size( $type ) {
			$key  = 'svsw_size_' . $type;
			$size = isset( self::$data[ $key ] ) ? self::$data[ $key ] : 30;
			?>
			<input name="<?php echo esc_attr( $key ); ?>" type="number" style="" value="<?php echo esc_attr( $size ); ?>" min="10" max="100"> <?php echo esc_html__( 'px', 'simple-variation-swatches' ); ?>
			<?php
		}

		/**
		 * Display swatch button and radio buttion font size
		 */
		public static function font_size() {
			$font_size = isset( self::$data['svsw_font_size'] ) ? self::$data['svsw_font_size'] : 18;
			?>
			<input name="svsw_font_size" type="number" style="" value="<?php echo esc_attr( $font_size ); ?>" min="8" max="50"> <?php echo esc_html__( 'px', 'simple-variation-swatches' ); ?>
			<?php
		}

		/**
		 * Display admin notices and settings form submission notice
		 */
		public static function display_notice() {
			global $svsw__;

			// display admin notices.
			if ( isset( $svsw__['notice'] ) ) {
				foreach ( $svsw__['notice'] as $notice ) {
					echo wp_kses_post( $notice );
				}
			}

			if ( ! isset( $_POST['svsw_nonce_field'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['svsw_nonce_field'] ) ), 'svsw_save' ) ) {
				return;
			}

			// if no settings form data found, skip as we need to show saved notice.
			if ( ! isset( $_POST['svsw_tab'] ) ) {
				return;
			}
			?>
			<div class="notice notice-success is-dismissible updated">
				<p>
					<?php echo esc_html__( 'Settings saved successfully.', 'simple-variation-swatches' ); ?>
				</p>
				<button type="button" class="notice-dismiss"></button>
			</div>
			<?php
		}
	}
}
