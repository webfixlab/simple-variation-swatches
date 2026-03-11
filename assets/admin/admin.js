/**
 * Admin settings page function
 *
 * @package    WordPress
 * @subpackage Simple Variation Swatches
 * @since      3.0.0
 */

( function ( $, window, document ) {
	class SimpleVariationSwatchsAdmin{
		constructor(){
			$( () => this.initEventTriggers() );
		}
		initEventTriggers(){
			$( window ).on( 'scroll', () => this.stickyTopBar() ); // sticky header.
			$( '.svsw-colorpicker' ).wpColorPicker(); // color swatches.

			const $doc = $( document );
			$doc.on( // navigation tab.
				'click',
				'.nav-tab',
				e => this.settingsPageTabHandler( $( e.currentTarget ) )
			);
			$doc.on( // load input on attribute type changed.
				'change',
				'.svsw-att-type',
				() => this.displayAttTypeInputFeild()
			);
			$doc.on(
				'click',
				'.svsw-upload-image',
				(e) =>
				{ // media uploader.
					e.preventDefault();
					this.setSwatchImage();
				}
			);
			$doc.on( 'click', '.svsw-remove-img', e => confirm( svsw_admin_data.img_delete ) && this.removeImage() ); // on remove swatch image.
		}
		stickyTopBar(){
			$( document ).find( '.svsw-wrap' ).toggleClass( 'svsw-sticky-top', $( window ).scrollTop() > 40 );
		}
		settingsPageTabHandler( item ){
			if ( item.hasClass( 'nav-tab-active' ) ) {
				return;
			}

			$( document ).find( '.nav-tab' ).removeClass( 'nav-tab-active' );
			item.addClass( 'nav-tab-active' );

			$( '.svsw-section' ).hide();
			const target = item.data( 'target' );
			$( document ).find( `.svsw-section-${target}` ).show(); // phpcs:ignore.
			$( document ).find( 'input[name="svsw_tab"]' ).val( target ); // for keeping the tab open on save.
		}
		displayAttTypeInputFeild(){
			const swatchType = $( document ).find( '.svsw-att-type option:selected' ).val();
			if ( ! swatchType || 0 === swatchType.length ) {
				return;
			}

			// hide all input fields except current one.
			$( document ).find( '.svsw-input-field' ).hide();
			$( document ).find( `.svsw-input-${swatchType}` ).show(); // phpcs:ignore.
		}
		setSwatchImage(){
			const mediaObj = wp.media(
				{
					title    : 'Upload Image',
					multiple : false
				}
			)
			.open()
			.on( 'select', e => this.attachImageToSwatch( mediaObj.state().get( 'selection' ).first() ) );
		}
		attachImageToSwatch( imageObj ){
			const imageData = imageObj.toJSON();

			if ( ! imageData.url.length ) {
				return;
			}

			const imageWrap = $( document ).find( '.svsw-input-image img' );
			if ( imageWrap && imageWrap.length > 0 ) {
				imageWrap.attr( 'src', imageData.url );
			} else {
				$( document ).find( '.svsw-input-image' ).append( `<img src="${imageData.url}" class="svsw-admin-img"><span class="dashicons dashicons-remove svsw-remove-img"></span>` ); // phpcs:ignore.
			}

			this.setImageInputValue( imageData.url );
		}
		setImageInputValue( imageUrl ){
			$( document ).find( 'input[name="svsw_image"]' ).val( imageUrl );
		}
		removeImage(){
			this.setImageInputValue( '' );
			$( document ).find( '.svsw-input-image img' ).remove();
		}
	}
	new SimpleVariationSwatchsAdmin();
} )( jQuery, window, document );
