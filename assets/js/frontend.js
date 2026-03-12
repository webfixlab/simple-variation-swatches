/**
 * Frontend JavaScript
 *
 * @package    Wordpress
 * @subpackage Simple Variation Swatches
 * @since      3.0
 */

( function ( $, window, document ) {
	class simpeVariationSwatches{
		constructor(){
			this.$state = {}; // current variation attributes state.
			$( () => this.initSwatchs() );
		}
		initSwatchs(){
			this.initialStateHandler();
			$( document ).on(
				'mouseenter mouseleave',
				'.svsw-color-image',
				e =>
				this.toolTip(
					$( e.currentTarget ),
					e.type === 'mouseleave'
				)
			);
			$( document ).on(
				'click change',
				'.svsw-swatch',
				( e ) =>
				{
					const el = $( e.currentTarget );
					// when not clicked on a select dropdown.
					if ( ( 'click' === e.type ) !== el.hasClass( 'svsw-swatch-dropdown' ) ) {
						this.swatchClickedEventHandler( el );
					}
				}
			);
			$( document ).on(
				'click',
				'.svsw-reset',
				() => this.clearVariations()
			);
		}
		initialStateHandler(){
			this.getState();
			// use default attribute values, if any.
			this.imposeStateOnSwatches( $( document ).find( '.svsw-frontend-wrap .svsw-attr-wrap' ), 'swatch' );
			this.clearVariationsBtn();
		}
		getState(){
			// get initial attribute.
			const atts = $( document ).find( 'table.variations select' );
			if ( ! atts || 0 === atts.length ) {
				return;
			}

			// Respecting default attribute values.
			atts.each(
				( _, el ) => this.$state[ $( el ).attr( 'data-attribute_name' ) ] = $( el ).find( 'option:selected' ).val() ?? '' // phpcs:ignore.
			);
		}
		clearVariationsBtn(){
			let add = false; // add clear variations button or not.
			$.each(
				this.$state,
				function ( attName, attValue ) {
					if ( attValue && attValue.length > 0 ) {
						add = true;
					}
				}
			);

			const resetWrap = $( document ).find( '.svsw-reset' );
			if ( add && resetWrap && resetWrap.length > 0 ) {
				return;
			}

			// add clear variation button or remove it, based on flag.
			if ( add ) {
				$( document ).find( '.svsw-frontend-wrap' ).append( '<a class="svsw-reset reset_variations" href="#" style="visibility: visible;">Clear</a>' );
			} else {
				resetWrap.remove();
			}
		}
		imposeStateOnSwatches( items, type ) {
			items.each(
				( _, el ) =>
				this.imposeStateOnSwatch(
					$( el ),
					type
				)
			);
		}
		imposeStateOnSwatch( swatchWrap, type ) {
			const attName  = swatchWrap.attr( 'data-attribute_name' );
			const attValue = this.$state[ attName ];

			// for swatches, if the state value is empty, make options available again.
			if ( 'swatch' === type && ! attValue ) {
				this.removeDisabledClass( swatchWrap );
			}

			// for no swatch, set default attribute select dropdown values.
			if ( 'default' === type || swatchWrap.hasClass( 'svsw-swatch-dropdown' ) ) { // select.
				swatchWrap.val( attValue ).trigger( 'change' );
				return;
			}

			// handle selecting a swatch.
			swatchWrap.find( '.svsw-swatch' ).each(
				( _, el ) =>
				{
					if ( $( el ).hasClass( 'svsw-swatch-dropdown' ) ) {
						$( el ).val( attValue ).trigger( 'change' );
					} else {
						const isRadioBtn = $( el ).hasClass( 'svsw-swatch-radio' );
						const checkingAttValue = isRadioBtn ? $( el ).find( 'input[type="radio"]' ).val() : $( el ).attr( 'data-attribute_value' );
						
						$( el ).toggleClass( 'svsw-selected', checkingAttValue === attValue ); // to select swatch.

						if ( isRadioBtn ) {
							$( el ).find( 'input[type="radio"]' ).prop( 'checked', checkingAttValue === attValue ); // to select radio button.
						}
					}
				}
			);
		}
		removeDisabledClass( swatchWrap ) {
			if ( swatchWrap.hasClass( 'svsw-swatch-dropdown' ) ) { // select.
				swatchWrap.find( 'option' ).each(
					( _, el ) =>
					this.hideOrDisableSwatch(
						$( el ),
						false
					)
				);
				return;
			}

			swatchWrap.find( '.svsw-swatch' ).each(
				( _, el ) =>
				this.hideOrDisableSwatch(
					$( el ),
					false
				)
			);
		}
		swatchClickedEventHandler( item ){
			this.updateState( item );

			this.imposeStateOnEverything();
			this.clearVariationsBtn();

			setTimeout(
				() => this.availableVariationsHandler(),
				100
			);
		}
		updateState( item ){
			// update current state data.
			const attName          = item.closest( '.svsw-attr-wrap' ).attr( 'data-attribute_name' );
			this.$state[ attName ] = item.hasClass( 'svsw-swatch-dropdown' ) ? item.find( 'option:selected' ).val() : item.attr( 'data-attribute_value' );
		}
		imposeStateOnEverything(){
			this.imposeStateOnSwatches( $( document ).find( '.svsw-frontend-wrap .svsw-attr-wrap' ), 'swatch' );
			this.imposeStateOnSwatches( $( document ).find( 'table.variations select' ), 'default' );
		}
		clearVariations(){
			// clear state values.
			Object.keys( this.$state ).forEach(
				key => this.$state[ key ] = ''
			);
			this.imposeStateOnEverything();
			$( document ).find( '.svsw-reset' ).remove(); // remove clear variation or reset button.
			// make all options available again.
			$( document ).find( '.svsw-swatch' ).each(
				( _, el ) =>
				{
					if ( $( el ).hasClass( 'svsw-swatch-dropdown' ) ) {
						$( el ).find( 'option' ).each(
							( _, cel ) =>
							this.hideOrDisableSwatch(
								$( cel ),
								false
							)
						);
						$( el ).val( '' );
					} else {
						this.hideOrDisableSwatch( $( el ), false );
					}
				}
			);
		}
		availableVariationsHandler(){
			let availableVariations        = {};
			$( document ).find( 'table.variations select' ).each(
				( _, pel ) =>
				{
					const attName          = $( pel ).attr( 'data-attribute_name' );
					$( pel ).find( 'option' ).each(
						( _, el ) =>
						{
							const attValue = $( el ).val();
							if ( ! availableVariations[ attName ] ) {
								availableVariations[ attName ] = [];
							}
							if ( attValue && attValue.length > 0 ) {
								availableVariations[ attName ].push( attValue );
							}
						}
					);
				}
			);
			this.filterAvailableVariations( availableVariations );
		}
		filterAvailableVariations( availableVariations ) {
			$( document ).find( '.svsw-frontend-wrap .svsw-attr-wrap' ).each(
				( _, pel ) =>
				{
					const attName = $( pel ).attr( 'data-attribute_name' );
					$( pel ).find( '.svsw-swatch' ).each(
						( _, el ) =>
						this.filterAvailableVariation(
							availableVariations[ attName ],
							$( el )
						)
					);
				}
			);
		}
		filterAvailableVariation( availableVariations, item ) {
			if ( item.hasClass( 'svsw-swatch-dropdown' ) ) { // select.
				item.find( 'option' ).each(
					( _, el ) =>
					{
						const attValue = $( el ).val();
						if ( attValue && attValue.length > 0 ) {
							this.hideOrDisableSwatch( $( el ), -1 === availableVariations.indexOf( attValue ) );
						}
					}
				);
			} else {
				const attValue = item.attr( 'data-attribute_value' );
				this.hideOrDisableSwatch( item, -1 === availableVariations.indexOf( attValue ) );
			}
		}
		hideOrDisableSwatch( swatchItem, isDisabled ) {
			if ( 'disable' === svsw_front.settings.variation_behavior ) {
				if ( swatchItem.is( 'option' ) ) {
					swatchItem.prop( 'disabled', isDisabled );
				} else {
					swatchItem.toggleClass( 'svsw-disabled', isDisabled );
				}
			} else {
				swatchItem.toggleClass( 'svsw-hidden', isDisabled );
			}
		}
		toolTip( item, ifHide ) {
			let toolTip  = item.attr( 'data-tooltip' );
			const imgSrc = item.attr( 'data-img' );

            toolTip = imgSrc && imgSrc.length > 0 ? `<img src="${imgSrc}" />` : `<p>${toolTip}</p>`; // phpcs:ignore.

			const toolTipWrap = item.find( '.svsw-tooltip' );
			if ( ifHide ) {
				toolTipWrap.remove();
			} else if ( ! toolTipWrap || 0 === toolTipWrap.length ) {
                item.append( `<div class="svsw-tooltip">${toolTip}</div>` ); // phpcs:ignore
			}
		}
	}
	new simpeVariationSwatches();
} )( jQuery, window, document );
