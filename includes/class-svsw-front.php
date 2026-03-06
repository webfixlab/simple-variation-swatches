<?php
/**
 * Frontend Swatch Class
 *
 * @package    WordPress
 * @subpackage Simple Variation Swatches
 * @since      3.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'SVSW_Front' ) ) {

	/**
	 * Display swatch frontend class
	 */
	class SVSW_Front {

		/**
		 * Swatch settings data
		 *
		 * @var array
		 */
		private static $data;

		/**
		 * Swatch attributes quantity pair data
		 *
		 * @var array
		 */
		private static $pack; // Pack data | PRO.

		/**
		 * Current taxonomy
		 *
		 * @var string
		 */
		private static $taxonomy;

		/**
		 * Initialize class
		 */
		public static function setup() {
			self::$data     = get_option( 'svsw_settings' );
			self::$taxonomy = '';
		}

		/**
		 * Initialize content
		 */
		public static function init() {
			add_action( 'woocommerce_variable_add_to_cart', array( __CLASS__, 'init_swatch' ), 29 );
		}

		/**
		 * Initialize swatch functionlity
		 */
		public static function init_swatch() {
			global $product;

			self::setup();

			// swatch attributes quantity pair data.
			self::$pack = apply_filters( 'svsw_data_pack', array() );

			// get product attributes.
			$attributes = $product->get_variation_attributes();

			// get saved settings data, if any.
			$data = self::$data;

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
					self::$taxonomy = $attribute_name;
					self::atts_to_swatch( $attribute_name, $options, $display_name );
				}
				?>
			</div>
			<?php

			do_action( 'svsw_after_atts', $attributes, self::$pack );
		}

		/**
		 * Convert attribute dropdowns to swatch items
		 *
		 * @param string  $attribute_name product attribute name.
		 * @param array   $options        attribute options.
		 * @param boolean $show_name      whether to show attribute name or not.
		 */
		public static function atts_to_swatch( $attribute_name, $options, $show_name ) {
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
			$att_name_underline = isset( self::$data['att_name_underline'] ) && 'on' === self::$data['att_name_underline'] ? true : false;

			// attribute name design class.
			$anc = isset( self::$data['att_name_color'] ) ? self::$data['att_name_color'] : '';
			$anb = isset( self::$data['att_name_background'] ) ? self::$data['att_name_background'] : '';

			$no_padding = empty( $anb ) ? 'an-no-pad' : '';
			$anc        = ( empty( $anb ) && '#ffffff' === $anc ) || $anc === $anb ? '' : $anc;

			$anc = ! empty( $anc ) ? 'color: ' . esc_attr( $anc ) . ';' : '';
			$anb = ! empty( $anb ) ? 'background-color: ' . esc_attr( $anb ) . ';margin-bottom: 10px;' : '';

			// attribute block design.
			$block_design = isset( self::$data['att_block_design'] ) ? 'att-' . self::$data['att_block_design'] : '';
			?>
			<div class="svsw-wrap <?php echo esc_attr( $block_design ); ?>">
				<?php if ( $show_name ) : ?>
					<?php
						echo wp_kses_post(
							sprintf(
								'<label class="attr-name %s" style="%s%s%s">',
								esc_attr( $no_padding ),
								esc_html( $anc ),
								esc_html( $anb ),
								$att_name_underline ? 'border-bottom: 1px solid #888;' : ''
							)
						);

						echo esc_html( $att_name );
						do_action( 'svsw_after_att_name', $attribute_name, self::$pack );
					?>
					</label>
				<?php endif; ?>
				<div class="svsw-attr-wrap" data-attribute_name="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>">
					<?php
						// display swatches.
					if ( is_wp_error( $terms ) ) {
						self::skipped_atts( $options, $attribute_name );
					} else {
						self::display_swatches( $terms, $options, $att_name );
					}
					?>
				</div>
			</div>
			<?php
		}

		/**
		 * Display swatch items
		 *
		 * @param object | array $terms          WP Term objects of the product attribute.
		 * @param array          $options        product attribute options.
		 * @param string         $attribute_name product attribute name.
		 */
		public static function display_swatches( $terms, $options, $attribute_name ) {
			// list of unavailable swatch items.
			$skipped_terms = array();

			if ( ! is_array( self::$data ) ) {
				self::$data = array();
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

				self::$data['tooltip'] = $tooltip;

				// display swatch html element.
				self::render_swatch( $term->slug, $type, $value );
			}

			self::skipped_atts( $skipped_terms, $attribute_name );
		}

		/**
		 * Display skipped options that didn't have any swatch data
		 *
		 * @param array  $skipped_terms  attribute options that didn't have any swatch settings data.
		 * @param string $attribute_name product attribute name.
		 */
		public static function skipped_atts( $skipped_terms, $attribute_name ) {
			if ( empty( $skipped_terms ) ) {
				return;
			}

			// display everything dropdown.
			$variation_to = 'default';

			// if settings enabled to convert dropdown attributes to swatches.
			if ( isset( self::$data['attr_to_swatches'] ) && ! empty( self::$data['attr_to_swatches'] ) ) {
				$variation_to = self::$data['attr_to_swatches'];
			}

			$font_size = '';
			if ( isset( self::$data['svsw_font_size'] ) && ! empty( self::$data['svsw_font_size'] ) ) {
				$font_size = 'font-size: ' . esc_attr( self::$data['svsw_font_size'] ) . 'px;';
			}

			if ( 'default' === $variation_to ) {
				printf(
					'<select name="%s" class="svsw-swatch svsw-swatch-dropdown" style="%s">',
					esc_attr( $attribute_name ),
					esc_attr( $attribute_name ),
					esc_html( $font_size )
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
					self::render_swatch( $opt_name, 'radio', $opt_value );
				} elseif ( 'button' === $variation_to ) {
					self::render_swatch( $opt_name, 'button', $opt_value );
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
		public static function render_swatch( $slug, $type, $value ) {
			$data = self::$data;
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

			$color_size = isset( $data['svsw_size_color'] ) && ! empty( $data['svsw_size_color'] ) ? $data['svsw_size_color'] : 31;
			// dynamic border width for selected and un-selected swatch.
			$border = (int) ( $color_size / 15 );

			$tooltip = '';
			if ( isset( $data['tooltip'] ) && ! empty( $data['tooltip'] ) ) {
				$tooltip = $data['tooltip'];
			}

			$font_size = '';
			if ( isset( $data['svsw_font_size'] ) && ! empty( $data['svsw_font_size'] ) ) {
				$font_size = 'font-size: ' . esc_attr( $data['svsw_font_size'] ) . 'px;';
			}

			if ( 'color' === $type ) {
				$value = 'background-color: ' . esc_attr( $value ) . ';';

				printf(
					'<span class="svsw-swatch svsw-color-image %s" style="%s width: %spx; height: %spx; border: %spx solid #ffffff" data-attribute_value="%s" data-tooltip="%s"></span>',
					esc_attr( $color_shape ),
					esc_html( $value ),
					esc_html( $color_size ),
					esc_html( $color_size ),
					esc_attr( $border ),
					esc_attr( $slug ),
					esc_attr( $slug ),
					esc_html( $tooltip )
				);
			} elseif ( 'image' === $type ) {
				// dynamic border width for selected and un-selected swatch.
				$border = (int) ( $image_size / 20 );

				// without any image set, use default woocommerce placeholder image.
				if ( ! isset( $value ) || empty( $value ) ) {
					$value = self::wc_placeholder_imgs();
				}

				printf(
					'<span class="svsw-swatch svsw-color-image %s" style="background: url(%s) no-repeat; background-size: cover; width: %spx; height: %spx; border: %spx solid #ffffff;" data-attribute_value="%s" data-tooltip="%s" data-img="%s"></span>',
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
					'<span class="svsw-swatch svsw-btn" style="%s border: 1px solid;" data-attribute_value="%s">%s</span>',
					esc_html( $font_size ),
					esc_attr( $slug ),
					esc_html( $value )
				);
			} elseif ( 'radio' === $type ) {
				?>
				<div class="svsw-swatch svsw-swatch-radio" data-attribute_value="<?php echo esc_attr( $slug ); ?>">
					<input type="radio" name="svsw_radio_swatch_<?php echo esc_attr( self::$taxonomy ); ?>" value="<?php echo esc_html( $slug ); ?>">
					<label style="<?php echo esc_html( $font_size ); ?>"><?php echo esc_html( $value ); ?></label>
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
		public static function wc_placeholder_imgs() {
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

SVSW_Front::init();
