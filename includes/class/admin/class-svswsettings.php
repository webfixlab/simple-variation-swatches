<?php
/**
 * Admin Settings Class
 *
 * @package    WordPress
 * @subpackage Simple Variation Swatches
 * @since      2.0
 */

if ( ! class_exists( 'SVSWSettings' ) ) {

	/**
	 * Swatch admin settings functionlity class
	 */
	class SVSWSettings {



		/**
		 * Settings data
		 *
		 * @var array
		 */
		private $data;

		/**
		 * Initialize class and get saved settings data
		 */
		public function __construct() {
			$this->data = get_option( 'svsw_settings' );
		}

		/**
		 * Initialize hook of settings class
		 */
		public function init() {
			add_action( 'admin_head', array( $this, 'save_settings' ) );
		}



		/**
		 * Save admin settings
		 */
		public function save_settings() {

			if ( ! isset( $_POST['svsw_nonce_field'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['svsw_nonce_field'] ) ), 'svsw_save' ) ) {
				return;
			}

			$data = array();

			if ( isset( $_POST['attr_to_swatches'] ) ) {
				$data['attr_to_swatches'] = sanitize_key( $_POST['attr_to_swatches'] );
			}

			if ( isset( $_POST['hide_attr_name'] ) ) {
				$data['hide_attr_name'] = sanitize_key( $_POST['hide_attr_name'] );
			}

			if ( isset( $_POST['image_swatch_style'] ) ) {
				$data['image_swatch_style'] = sanitize_key( $_POST['image_swatch_style'] );
			}
			if ( isset( $_POST['color_swatch_style'] ) ) {
				$data['color_swatch_style'] = sanitize_key( $_POST['color_swatch_style'] );
			}

			if ( isset( $_POST['svsw_size_image'] ) ) {
				$data['svsw_size_image'] = sanitize_key( $_POST['svsw_size_image'] );
			}

			if ( isset( $_POST['svsw_size_color'] ) ) {
				$data['svsw_size_color'] = sanitize_key( $_POST['svsw_size_color'] );
			}

			if ( isset( $_POST['svsw_font_size'] ) ) {
				$data['svsw_font_size'] = sanitize_key( $_POST['svsw_font_size'] );
			}

			if ( isset( $_POST['att_name_underline'] ) ) {
				$data['att_name_underline'] = sanitize_key( $_POST['att_name_underline'] );
			}

			// attribute name design.
			if ( isset( $_POST['att_name_design'] ) ) {
				$data['att_name_design'] = sanitize_text_field( wp_unslash( $_POST['att_name_design'] ) );
			}

			// attribute block design.
			if ( isset( $_POST['att_block_design'] ) ) {
				$data['att_block_design'] = sanitize_text_field( wp_unslash( $_POST['att_block_design'] ) );
			}

			update_option( 'svsw_settings', $data );
		}



		/**
		 * Display settings page
		 */
		public function settings_page() {
			?>
			<div class="svsw-wrap">
				<?php $this->settings_header(); ?>
				<div class="svsw-content-wrap">
					<div class="svsw-main">
						<form action="" method="POST">
							<?php $this->settings_content(); ?>
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
		public function settings_header() {
			global $svsw__;

			?>
			<div class="svsw-heading">
				<?php $this->get_title(); ?>
				<div class="heading-desc">
					<p>
						<a href="<?php echo esc_url( $svsw__['urls']['docs'] ); ?>" target="_blank"><?php echo esc_html__( 'DOCUMENTATION', 'simple-variation-swatches' ); ?></a> | <a href="<?php echo esc_url( $svsw__['urls']['support'] ); ?>" target="_blank"><?php echo esc_html__( 'SUPPORT', 'simple-variation-swatches' ); ?></a>
					</p>
				</div>
			</div>
			<div class="svsw-notice">
				<?php $this->display_notice(); ?>
			</div>
			<?php
		}

		/**
		 * Settings page content
		 */
		public function settings_content() {

			$tab = $this->get_tab();

			?>
			<div class="row">
				<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
					<?php $this->get_menu(); ?>
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
								<?php $this->att_to_swatch(); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Attribute label', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php $this->hide_att_name(); ?>
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
								<?php $this->swatch_design( 'image' ); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Color swatches style', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php $this->swatch_design( 'color' ); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Image swatches size', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php $this->swatch_size( 'image' ); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Color swatches size', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php $this->swatch_size( 'color' ); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Font size', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php $this->font_size(); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Attribute name under line', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php
								$checked = '';

								if ( isset( $this->data['att_name_underline'] ) && 'on' === $this->data['att_name_underline'] ) {
									$checked = 'checked';
								}

								?>
								<input name="att_name_underline" type="checkbox"<?php echo esc_attr( $checked ); ?>>
								<label><?php echo esc_html__( 'Show', 'simple-variation-swatches' ); ?></label>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Attribute name design', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php
									$design = '';

								if ( isset( $this->data['att_name_design'] ) ) {
									$design = $this->data['att_name_design'];
								}

								if ( empty( $design ) ) {
									$design = 'demo-default';
								}

									$designs = array(
										'demo-default' => __( 'Default', 'simple-variation-swatches' ),
										'demo-block-1' => __( 'Design 1', 'simple-variation-swatches' ),
										'demo-block-2' => __( 'Design 2', 'simple-variation-swatches' ),
									);

									foreach ( $designs as $val => $label ) {
										$checked = $val === $design ? 'checked' : '';

										printf(
											'<input name="att_name_design" type="radio" %s value="%s"><label>%s</label>',
											esc_attr( $checked ),
											esc_attr( $val ),
											esc_html( $label )
										);

										printf(
											'<div class="%s"><span>%s</span></div>',
											esc_attr( $val ),
											esc_html__( 'Attribute Name...', 'simple-variation-swatches' )
										);
									}
									?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Attribute block design', 'simple-variation-swatches' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<?php
									$design = '';

								if ( isset( $this->data['att_block_design'] ) ) {
									$design = $this->data['att_block_design'];
								}

								if ( empty( $design ) ) {
									$design = 'demo-default';
								}

									$designs = array(
										'default' => __( 'Default', 'simple-variation-swatches' ),
										'block-1' => __( 'Design 1', 'simple-variation-swatches' ),
									);

									foreach ( $designs as $val => $label ) {
										$checked = $val === $design ? 'checked' : '';

										printf(
											'<input name="att_block_design" type="radio" %s value="%s"><label>%s</label>',
											esc_attr( $checked ),
											esc_attr( $val ),
											esc_html( $label )
										);

										printf(
											'<div class="att-design-%s"><span>%s</span></div>',
											esc_attr( $val ),
											esc_html__( 'Attribute section...', 'simple-variation-swatches' )
										);
									}
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
		public function get_tab() {
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
		public function get_title() {
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
		public function get_menu() {
			// get current tab.
			$tab = $this->get_tab();

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
		public function att_to_swatch() {

			$att_to_swatch = isset( $this->data['attr_to_swatches'] ) ? $this->data['attr_to_swatches'] : '';

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
		public function hide_att_name() {
			$checked = '';

			if ( isset( $this->data['hide_attr_name'] ) && 'on' === $this->data['hide_attr_name'] ) {
				$checked = 'checked';
			}

			?>
			<input name="hide_attr_name" type="checkbox"<?php echo esc_attr( $checked ); ?>>
			<label><?php echo esc_html__( 'Hide', 'simple-variation-swatches' ); ?></label>
			<?php
		}

		/**
		 * Display swatch types dropdown
		 *
		 * @param string $type either image or color type swatch.
		 */
		public function swatch_design( $type ) {
			$key = $type . '_swatch_style';

			$design = isset( $this->data[ $key ] ) ? $this->data[ $key ] : '';

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
		public function swatch_size( $type ) {
			$key  = 'svsw_size_' . $type;
			$size = isset( $this->data[ $key ] ) ? $this->data[ $key ] : 30;

			?>
			<input name="<?php echo esc_attr( $key ); ?>" type="number" style="" value="<?php echo esc_attr( $size ); ?>" min="10" max="100"> <?php echo esc_html__( 'px', 'simple-variation-swatches' ); ?>
			<?php
		}

		/**
		 * Display swatch button and radio buttion font size
		 */
		public function font_size() {

			$font_size = isset( $this->data['svsw_font_size'] ) ? $this->data['svsw_font_size'] : 18;

			?>
			<input name="svsw_font_size" type="number" style="" value="<?php echo esc_attr( $font_size ); ?>" min="8" max="50"> <?php echo esc_html__( 'px', 'simple-variation-swatches' ); ?>
			<?php
		}

		/**
		 * Display admin notices and settings form submission notice
		 */
		public function display_notice() {
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
			<div id="message" class="updated notice notice-success">
				<p>
					<?php echo esc_html__( 'Settings saved successfully.', 'simple-variation-swatches' ); ?>
				</p>
				<button type="button" class="notice-dismiss">
			</div>
			<?php
		}
	}
}

$svsw_settings = new SVSWSettings();
$svsw_settings->init();
