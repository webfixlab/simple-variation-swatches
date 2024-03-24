<?php
/**
 * Frontend Swatch Class
 *
 * @package    WordPress
 * @subpackage Simple Variation Swatches
 * @since      2.0
 */

if ( ! class_exists( 'SVSW' ) ) {

	/**
	 * Display swatch frontend class
	 */
	class SVSW {



		/**
		 * Swatch settings data
		 *
		 * @var array
		 */
		private $data;

		/**
		 * Swatch attributes quantity pair data
		 *
		 * @var array
		 */
		private $pack; // Pack data | PRO.



		/**
		 * Initialize class
		 */
		public function __construct() {
			$this->data = get_option( 'svsw_settings' );
		}

		/**
		 * Initialize content
		 */
		public function init() {
			add_action( 'woocommerce_variable_add_to_cart', array( $this, 'init_swatch' ), 29 );
		}



		/**
		 * Initialize swatch functionlity
		 */
		public function init_swatch() {
			global $product;

			// swatch attributes quantity pair data.
			$this->pack = apply_filters( 'svsw_data_pack', array() );

			// get product attributes.
			$attributes = $product->get_variation_attributes();

			// get saved settings data, if any.
			$data = $this->data;

			// wheather to display attribute name.
			$display_name = false;
			$hide_attr    = 'svsw-hide-attr';
			if ( ! isset( $data['hide_attr_name'] ) || ( isset( $data['hide_attr_name'] ) && 'on' !== $data['hide_attr_name'] ) ) {
				$display_name = true;
				$hide_attr    = '';
			}

			?>
			<div class="svsw-frontend-wrap <?php echo esc_attr( $hide_attr ); ?>">
				<?php

				foreach ( $attributes as $attribute_name => $options ) {
					$this->atts_to_swatch( $attribute_name, $options, $display_name );
				}

				?>
			</div>
			<?php

			do_action( 'svsw_after_atts', $attributes, $this->pack );
		}

		/**
		 * Convert attribute dropdowns to swatch items
		 *
		 * @param string  $attribute_name product attribute name.
		 * @param array   $options        attribute options.
		 * @param boolean $show_name      whether to show attribute name or not.
		 */
		public function atts_to_swatch( $attribute_name, $options, $show_name ) {
			$att_name = $attribute_name;

			// first check if it's global attribute or not.
			$terms = get_terms( $attribute_name );

			if ( ! is_wp_error( $terms ) ) {
				// to find actual name, get taxonomy object.
				$tax_obj = get_taxonomy( $attribute_name );

				if ( isset( $tax_obj->labels ) && isset( $tax_obj->labels->singular_name ) ) {
					$att_name = $tax_obj->labels->singular_name;
				}
			}

			// if use underline under attribute name.
			$att_name_underline = isset( $this->data['att_name_underline'] ) && 'on' === $this->data['att_name_underline'] ? true : false;

			// attribute name design class.
			$att_name_cls = isset( $this->data['att_name_design'] ) ? $this->data['att_name_design'] : 'demo-default';

			// attribute block design.
			$block_design = isset( $this->data['att_block_design'] ) ? 'att-' . $this->data['att_block_design'] : '';

			?>
			<div class="svsw-wrap <?php echo esc_attr( $block_design ); ?>">
				<?php if ( $show_name ) : ?>
					<label class="attr-name <?php echo esc_attr( $att_name_cls ); ?>">
						<?php echo esc_html( $att_name ); ?>
						<?php do_action( 'svsw_after_att_name', $attribute_name, $this->pack ); ?>
					</label>
				<?php endif; ?>
				<?php if ( $att_name_underline ) : ?>
					<hr>
				<?php endif; ?>
				<div class="svsw-attr-wrap" data-taxonomy="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>">
					<?php

					// display swatches.
					if ( is_wp_error( $terms ) ) {
						$this->skipped_atts( $options, $attribute_name );
					} else {
						$this->display_swatches( $terms, $options, $att_name );
					}

					?>
				</div>
			</div>
			<?php
		}



		/**
		 * Display swatch items
		 *
		 * @param object $terms          WP Term objects of the product attribute.
		 * @param array  $options        product attribute options.
		 * @param string $attribute_name product attribute name.
		 */
		public function display_swatches( $terms, $options, $attribute_name ) {
			// list of unavailable swatch items.
			$skipped_terms = array();

			if ( ! is_array( $this->data ) ) {
				$this->data = array();
			}

			// get term object from term here.
			foreach ( $terms as $term ) {
				if ( ! in_array( $term->slug, $options, true ) ) {
					continue;
				}

				// swatch type.
				$type = get_term_meta( $term->term_id, 'attribute_type', true );

				// if no type found - skip this.
				if ( empty( $type ) ) {
					$skipped_terms[ $term->slug ] = $term->name;
					continue;
				}

				// swatch value - like button text or image file url.
				$value = get_term_meta( $term->term_id, 'svsw_' . $type, true );

				if ( empty( $value ) ) {
					if ( 'image' === $type || 'color' === $type ) {
						$skipped_terms[ $term->slug ] = $term->name;
						continue;
					} else {
						$value = $term->name;
					}
				}

				$tooltip = '';
				if ( 'color' === $type ) {
					$tooltip = get_term_meta( $term->term_id, 'svsw_color_tooltip', true );
				} elseif ( 'image' === $type ) {
					$tooltip = get_term_meta( $term->term_id, 'svsw_image_tooltip', true );
				}

				$this->data['tooltip'] = $tooltip;

				// display swatch html element.
				$this->render_swatch( $term->slug, $type, $value );
			}

			$this->skipped_atts( $skipped_terms, $attribute_name );
		}

		/**
		 * Display skipped options that didn't have any swatch data
		 *
		 * @param object $skipped_terms  attribute option WP Term objects that didn't have any swatch settings data.
		 * @param string $attribute_name product attribute name.
		 */
		public function skipped_atts( $skipped_terms, $attribute_name ) {
			if ( empty( $skipped_terms ) ) {
				return;
			}

			// display everything dropdown.
			$variation_to = 'default';

			// if settings enabled to convert dropdown attributes to swatches.
			if ( isset( $this->data['attr_to_swatches'] ) && ! empty( $this->data['attr_to_swatches'] ) ) {
				$variation_to = $this->data['attr_to_swatches'];
			}

			$font_size = 18;
			if ( isset( $this->data['svsw_font_size'] ) && ! empty( $this->data['svsw_font_size'] ) ) {
				$font_size = $this->data['svsw_font_size'];
			}

			if ( 'default' === $variation_to ) {
				printf(
					'<select name="%s" data-term="%s" class="svsw-swatch-dropdown" style="font-size:%spx;">',
					esc_attr( $attribute_name ),
					esc_attr( $attribute_name ),
					esc_attr( $font_size )
				);
				echo '<option value="">Choose an option</option>';
			}

			foreach ( $skipped_terms as $opt_name => $opt_value ) {

				if ( is_numeric( $opt_name ) ) {
					$opt_name = $opt_value;
				}

				if ( 'default' === $variation_to ) {
					printf( '<option value="%s">%s</option>', esc_attr( $opt_name ), esc_html( $opt_value ) );
				} elseif ( 'radio' === $variation_to ) {
					$this->render_swatch( $opt_name, 'radio', $opt_value );
				} elseif ( 'button' === $variation_to ) {
					$this->render_swatch( $opt_name, 'button', $opt_value );
				}
			}

			if ( 'default' === $variation_to ) {
				echo '</select>';
			}
		}



		/**
		 * Render swatch fields
		 *
		 * @param string $slug  attribute option slug.
		 * @param string $type  swatch type.
		 * @param string $value saved swatch settings data.
		 */
		public function render_swatch( $slug, $type, $value ) {

			$data = $this->data;

			?>
			<div class="svsw-swatch-content svsw-type-<?php echo esc_attr( $type ); ?>">
			<?php

			$image_shape = ' square';
			if ( isset( $data['image_swatch_style'] ) && ! empty( $data['image_swatch_style'] ) ) {
				$image_shape = ' ' . $data['image_swatch_style'];
			}

			$color_shape = ' square';
			if ( isset( $data['color_swatch_style'] ) && ! empty( $data['color_swatch_style'] ) ) {
				$color_shape = ' ' . $data['color_swatch_style'];
			}

			$image_size = 31;
			if ( isset( $data['svsw_size_image'] ) && ! empty( $data['svsw_size_image'] ) ) {
				$image_size = $data['svsw_size_image'];
			}

			$color_size = 31;
			if ( isset( $data['svsw_size_color'] ) && ! empty( $data['svsw_size_color'] ) ) {
				$color_size = $data['svsw_size_color'];
			}

			$tooltip = '';
			if ( isset( $data['tooltip'] ) && ! empty( $data['tooltip'] ) ) {
				$tooltip = $data['tooltip'];
			}

			$font_size = 18;
			if ( isset( $data['svsw_font_size'] ) && ! empty( $data['svsw_font_size'] ) ) {
				$font_size = $data['svsw_font_size'];
			}

			if ( 'color' === $type ) {
				// dynamic border width for selected and un-selected swatch.
				$border = (int) ( $color_size / 15 );

				printf(
					'<span class="svsw-swatch svsw-color-image %s" style="background-color: %s; width: %spx; height: %spx; border: %spx solid" data-term="%s" data-tooltip="%s" data-term="%s"></span>',
					esc_attr( $color_shape ),
					esc_html( $value ),
					esc_attr( $color_size ),
					esc_attr( $color_size ),
					esc_attr( $border ),
					esc_attr( $slug ),
					esc_html( $tooltip ),
					esc_attr( $slug )
				);
			} elseif ( 'image' === $type ) {
				// dynamic border width for selected and un-selected swatch.
				$border = (int) ( $image_size / 20 );

				// without any image set, use default woocommerce placeholder image.
				if ( ! isset( $value ) || empty( $value ) ) {
					$value = $this->wc_placeholder_imgs();
				}

				printf(
					'<span class="svsw-swatch svsw-color-image %s" style="background: url(%s) no-repeat; background-size: cover; width: %spx; height: %spx; border: %spx solid;" data-term="%s" data-tooltip="%s" data-img="%s"></span>',
					esc_attr( $image_shape ),
					esc_url( $value ),
					esc_attr( $image_size ),
					esc_attr( $image_size ),
					esc_attr( $border ),
					esc_attr( $slug ),
					esc_html( $tooltip ),
					esc_attr( $value )
				);
			} elseif ( 'button' === $type ) {
				printf(
					'<span class="svsw-swatch svsw-btn" style="font-size: %spx; border: 1px solid;" data-term="%s">%s</span>',
					esc_attr( $font_size ),
					esc_attr( $slug ),
					esc_html( $value )
				);
			} elseif ( 'radio' === $type ) {
				?>
				<div class="svsw-swatch svsw-swatch-radio" data-term="<?php echo esc_attr( $slug ); ?>">
					<input type="radio" name="svsw_radio_swatch" value="<?php echo esc_html( $value ); ?>">
					<label style="font-size: <?php echo esc_attr( $font_size ); ?>px;"><?php echo esc_html( $value ); ?></label>
				</div>
				<?php
			}

			?>
			</div>
			<?php
		}



		/**
		 * Display woocommerce placeholder image for missing swatch settings image data
		 */
		public function wc_placeholder_imgs() {
			global $svsw__;

			// if already found, use that image.
			if ( isset( $svsw__['wc_placeholder_img'] ) && ! empty( $svsw__['wc_placeholder_img'] ) ) {
				return $svsw__['wc_placeholder_img'];
			}

			$wc_img = '';
			$updir  = wp_get_upload_dir();
			$files  = glob( $updir['basedir'] . '/woocommerce-placeholder*.png' );

			// keep a backup copy of original/uncompressed image.
			$wc_img = $updir['basedir'] . '/woocommerce-placeholder.png';

			$sizes = array( 100, 150, 300, 600 );
			foreach ( $sizes as $size ) {
				$newpath = $updir['basedir'] . '/woocommerce-placeholder-' . $size . 'x' . $size . '.png';

				if ( in_array( $newpath, $files, true ) ) {
					$wc_img = $updir['baseurl'] . '/woocommerce-placeholder-' . $size . 'x' . $size . '.png';
					break;
				}
			}

			// keep a backup copy.
			$svsw__['wc_placeholder_img'] = $wc_img;

			return $wc_img;
		}
	}
}

$svsw_front_class = new SVSW();
$svsw_front_class->init();
