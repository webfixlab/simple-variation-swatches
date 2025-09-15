<?php
/**
 * Admin Swatch Class allows swatches to product attributes
 *
 * @package    WordPress
 * @subpackage Simple Variation Swatches
 * @since      2.0
 */

if ( ! class_exists( 'SVSW_Admin_Swatch' ) ) {

	/**
	 * Admin swatch class
	 */
	class SVSW_Admin_Swatch {

		/**
		 * Initialize admin swatch class
		 */
		public function init() {
			add_action( 'admin_init', array( $this, 'init_swatch' ) );
			add_action( 'add_tag_form_fields', array( $this, 'new_term' ), 20, 1 );

			add_action( 'create_term', array( $this, 'save_swatches' ), 10, 3 );
			add_action( 'created_term', array( $this, 'save_swatches' ), 10, 3 );
			add_action( 'edited_term', array( $this, 'save_swatches' ), 10, 3 );
			add_action( 'edit_term', array( $this, 'save_swatches' ), 10, 3 );
		}

		/**
		 * Initialize admin swatch option
		 */
		public function init_swatch() {
			// attribute type selection options.
			add_filter( 'product_attributes_type_selector', array( $this, 'add_att_type' ), 20 );

			// get all woocomerce attributes.
			$atts = wc_get_attribute_taxonomies();

			// add custom hook to support our plugin stuff here.
			foreach ( $atts as $tax ) {
				// edit term input fields, display.
				$name = wc_attribute_taxonomy_name( $tax->attribute_name );

				// add custom field to attribute edit form.
				add_action( 'pa_' . $tax->attribute_name . '_edit_form', array( $this, 'swatch_meta_box' ), 20, 2 );

				// custom column - for color and image type attribute only.
				if ( $this->if_add_term_column( $tax ) ) {
					// add content to custom column created.
					add_filter( 'manage_' . $name . '_custom_column', array( $this, 'term_list_column' ), 10, 3 );

					// add new custom column to attribute list.
					add_filter( 'manage_edit-' . $name . '_columns', array( $this, 'term_list_header' ), 20, 1 );
				}
			}
		}

		/**
		 * If we should add swatch value column to attribute terms
		 *
		 * @param object $attribute WP Term object of product attribute.
		 */
		public function if_add_term_column( $attribute ) {
			// Ensure the attribute name is in the correct taxonomy format.
			$att_name = 'pa_' . $attribute->attribute_name;
			$terms    = get_terms(
				array(
					'taxonomy'   => $att_name,
					'hide_empty' => false,
				)
			);

			// Check if $terms is a WP_Error instance.
			if ( is_wp_error( $terms ) ) {
				return false;
			}

			foreach ( $terms as $term ) {
				$type = get_term_meta( $term->term_id, 'attribute_type', true );

				if ( ! empty( $type ) ) {
					$value = get_term_meta( $term->term_id, 'svsw_' . $type, true );

					if ( ! empty( $value ) && ( 'color' === $type || 'image' === $type ) ) {
						return true;
					}
				}
			}

			// Check parent attribute's type if no term matched.
			$type = isset( $attribute->attribute_type ) ? $attribute->attribute_type : '';

			return ( 'color' === $type || 'image' === $type );
		}



		/**
		 * Add swatch fields to new term page
		 *
		 * @param string $taxonomy attribute taxonomy name.
		 */
		public function new_term( $taxonomy ) {
			$atts = wc_get_attribute_taxonomies();

			foreach ( $atts as $tax ) {
				// get attribute name.
				$name = wc_attribute_taxonomy_name( $tax->attribute_name );

				if ( $name === $taxonomy ) {
					// display attribute types select dropdown.
					$this->dispay_att_types( $tax->attribute_type );

					// display swatch input field.
					$this->display_input( $tax->attribute_type );

					wp_nonce_field( 'svsw_save', 'svsw_nonce_field' );
				}
			}
		}

		/**
		 * Save swatch data
		 *
		 * @param int    $term_id  term id.
		 * @param int    $tt_id    term taxonomy id.
		 * @param string $taxonomy taxonomy slug.
		 *
		 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
		 */
		public function save_swatches( $term_id, $tt_id, $taxonomy ) {
			if ( ! isset( $_POST['svsw_nonce_field'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['svsw_nonce_field'] ) ), 'svsw_save' ) ) {
				return;
			}

			// save attribute type.
			if ( isset( $_POST['attribute_type'] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST['attribute_type'] ) );
				update_term_meta( $term_id, 'attribute_type', $value );
			}

			// save other custom input fields.
			$this->save_inputs( $term_id );
		}

		/**
		 * Save swatch input fields
		 *
		 * @param int $term_id current term id being saved.
		 */
		public function save_inputs( $term_id ) {
			if ( ! isset( $_POST['svsw_nonce_field'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['svsw_nonce_field'] ) ), 'svsw_save' ) ) {
				return;
			}

			// color field.
			if ( isset( $_POST['svsw_color'] ) ) {
				$color = sanitize_text_field( wp_unslash( $_POST['svsw_color'] ) );
				update_term_meta( $term_id, 'svsw_color', $color );
			}

			// button field.
			if ( isset( $_POST['svsw_button'] ) ) {
				$button = sanitize_text_field( wp_unslash( $_POST['svsw_button'] ) );
				update_term_meta( $term_id, 'svsw_button', $button );
			}

			// radio button field.
			if ( isset( $_POST['svsw_radio'] ) ) {
				$radio = sanitize_text_field( wp_unslash( $_POST['svsw_radio'] ) );
				update_term_meta( $term_id, 'svsw_radio', $radio );
			}

			// handle uploaded image url.
			if ( isset( $_POST['svsw_uploaded_image'] ) ) {
				update_term_meta( $term_id, 'svsw_image', sanitize_url( wp_unslash( $_POST['svsw_uploaded_image'] ) ) );
			}

			// color field tooltip.
			if ( isset( $_POST['svsw_color_tooltip'] ) ) {
				$color_tooltip = sanitize_text_field( wp_unslash( $_POST['svsw_color_tooltip'] ) );
				update_term_meta( $term_id, 'svsw_color_tooltip', $color_tooltip );
			}

			// image field tooltip.
			if ( isset( $_POST['svsw_image_tooltip'] ) ) {
				$image_tooltip = sanitize_text_field( wp_unslash( $_POST['svsw_image_tooltip'] ) );
				update_term_meta( $term_id, 'svsw_image_tooltip', $image_tooltip );
			}
		}



		/**
		 * Add swatch to attribute type selection types
		 */
		public function add_att_type() {
			global $svsw__;

			// if anything goes wrong, return this.
			$blank = array(
				'select' => __( 'Select', 'simple-variation-swatches' ),
			);

			$screen = get_current_screen();

			if ( empty( $screen ) ) {
				return $blank;
			}

			// only allow type for admin product attribute section.
			if ( 'product' !== $screen->post_type || ! in_array( $screen->base, $svsw__['admin_scopes'], true ) ) {
				return $blank;
			}

			return $svsw__['attribute_types'];
		}

		/**
		 * Add custom field to attribute edit form.
		 *
		 * @param object $term     product attribute term object.
		 * @param string $taxonomy taxonomy name we are adding.
		 */
		public function swatch_meta_box( $term, $taxonomy ) {
			// get attribute/term type.
			$type = get_term_meta( $term->term_id, 'attribute_type', true );

			// display attribute/term types select dropdown.
			$this->dispay_att_types( $type );

			$values = array(
				'button'             => get_term_meta( $term->term_id, 'svsw_button', true ),
				'color'              => get_term_meta( $term->term_id, 'svsw_color', true ),
				'svsw_color_tooltip' => get_term_meta( $term->term_id, 'svsw_color_tooltip', true ),
				'radio'              => get_term_meta( $term->term_id, 'svsw_radio', true ),
				'image'              => get_term_meta( $term->term_id, 'svsw_image', true ),
				'svsw_image_tooltip' => get_term_meta( $term->term_id, 'svsw_image_tooltip', true ),
			);

			// display color here.
			$this->display_input( $type, $values );

			wp_nonce_field( 'svsw_save', 'svsw_nonce_field' );
		}



		/**
		 * Add new custom column to attribute list
		 *
		 * @param array $columns attribute term list header columns.
		 */
		public function term_list_header( $columns ) {
			return array_merge(
				array(
					'cb'    => '',
					'color' => '',
				),
				$columns
			);
		}

		/**
		 * Add content to custom column created
		 *
		 * @param string $content     term list column content.
		 * @param string $column_name the column which is currently displaying.
		 * @param int    $term_id     term id of the row.
		 */
		public function term_list_column( $content, $column_name, $term_id ) {
			$type  = '';
			$value = '';
			$html  = '';

			// get attribute type and respective input field value.
			$type = get_term_meta( $term_id, 'attribute_type', true );
			if ( ! empty( $type ) ) {
				$value = get_term_meta( $term_id, 'svsw_' . $type, true );
			}

			if ( ! empty( $value ) ) {
				// color or image field html.
				$html = '<div class="svsw-value">';

				if ( 'color' === $type ) {
					$html .= '<div style="background-color: ' . esc_html( $value ) . ';"></div>';
				} elseif ( 'image' === $type ) {
					$html .= '<div style="background: url(' . esc_url( $value ) . ') no-repeat; background-size: cover;"></div>';
				}

				$html .= '</div>';
			}

			return $content . $html;
		}



		/**
		 * Display swatch attribute types dropdown
		 *
		 * @param string $att_type swatch attribute type.
		 */
		public function dispay_att_types( $att_type = '' ) {
			// get all types.
			$types = apply_filters( 'product_attributes_type_selector', array() );

			// skip if no types found.
			if ( empty( $types ) || ! is_array( $types ) ) {
				return;
			}

			// display attribute types dropdown.
			?>
			<div class="form-field">
				<label for="attribute_type">Type</label>
				<select name="attribute_type" class="svsw-att-type">
					<?php foreach ( $types as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $att_type, $key ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php
		}

		/**
		 * Display swatch input field
		 *
		 * @param string $type   swatch input field type.
		 * @param array  $values input field saved value.
		 */
		public function display_input( $type, $values = array() ) {
			// get saved values - if any.
			$button       = '';
			$radio        = '';
			$color        = '';
			$colortooltip = '';
			$image        = '';
			$imagetooltip = '';

			if ( isset( $values ) ) {
				if ( isset( $values['button'] ) ) {
					$button = $values['button'];
				}
				if ( isset( $values['radio'] ) ) {
					$radio = $values['radio'];
				}
				if ( isset( $values['color'] ) ) {
					$color = $values['color'];
				}
				if ( isset( $values['image'] ) ) {
					$image = $values['image'];
				}
				if ( isset( $values['svsw_color_tooltip'] ) ) {
					$colortooltip = $values['svsw_color_tooltip'];
				}
				if ( isset( $values['svsw_image_tooltip'] ) ) {
					$imagetooltip = $values['svsw_image_tooltip'];
				}
			}

			// set default values.
			if ( empty( $color ) ) {
				$color = '#effeff';
			}
			?>
			<div class="svsw-edit-tag-wrap" data-type="<?php echo esc_attr( $type ); ?>">
				<div class="form-field svsw-input-field svsw-input-button"<?php echo 'button' !== $type ? ' style="display: none;"' : ''; ?>>
					<label for="tag-name">Label</label>
					<input name="svsw_button" type="text" value="<?php echo esc_html( $button ); ?>">
				</div>
				<div class="form-field svsw-input-field svsw-input-color"<?php echo 'color' !== $type ? ' style="display: none;"' : ''; ?>>
					<label for="tag-name">Color</label>
					<input name="svsw_color" type="text" value="<?php echo esc_html( $color ); ?>" class="svsw-color-field" data-default-color="<?php echo esc_html( $color ); ?>">
					<input name="svsw_color_tooltip" type="text" value="<?php echo esc_html( $colortooltip ); ?>" placeholder="Tooltip" class="admin-tooltip">
				</div>
				<div class="form-field svsw-input-field svsw-input-radio"<?php echo 'radio' !== $type ? ' style="display: none;"' : ''; ?>>
					<label for="tag-name">Label</label>
					<input name="svsw_radio" type="text" value="<?php echo esc_html( $radio ); ?>">
				</div>
				<div class="form-field svsw-input-field svsw-input-image"<?php echo 'image' !== $type ? ' style="display: none;"' : ''; ?>>
					<input type="hidden" name="svsw_uploaded_image" class="svsw-uploaded-image regular-text" value="<?php echo esc_url( $image ); ?>">
					<input type="button" name="svsw_upload_image" class="svsw-upload-image button-secondary" value="Upload Image">
					<input name="svsw_image_tooltip" type="text" value="<?php echo esc_html( $imagetooltip ); ?>" placeholder="Tooltip" class="admin-tooltip">
					<?php if ( ! empty( $image ) ) : ?>
						<img src="<?php echo esc_url( $image ); ?>" class="svsw-admin-img">
						<span class="dashicons dashicons-remove svsw-remove-img"></span>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
	}
}

$svsw_admin_swatch = new SVSW_Admin_Swatch();
$svsw_admin_swatch->init();
