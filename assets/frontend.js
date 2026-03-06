;(function ($, window, document, undefined) {
	/**
	 * SVSwatch class which handles custom form interactions and attributes.
	 *
	 * Uses local data svsw_front
	 */
	// console.log( 'svswatch', svsw_front );
	var SVSwatch = function () {
		var self = this;

		// Properties
		self.$swatch = $( document ).find( '.svsw-swatch' );

		// Methods
		self.onLoad     = self.onLoad.bind( self );
		self.mouseEnter = self.mouseEnter.bind( self );
		self.mouseLeave = self.mouseLeave.bind( self );

		self.onClick        = self.onClick.bind( self );
		self.hideVariations = self.hideVariations.bind( self );
		self.attrSelection  = self.attrSelection.bind( self );
		self.setAttr        = self.setAttr.bind( self );
		self.disableAttr    = self.disableAttr.bind( self );
		self.addClearBtn    = self.addClearBtn.bind( self );

		self.resetVariations = self.resetVariations.bind( self );
		self.onClickRadio    = self.onClickRadio.bind( self );
		self.changeSelect    = self.changeSelect.bind( self );

		// Events
		$( document ).ready( self.onLoad );
		self.$swatch.on(
			'mouseenter',
			function () {
				self.mouseEnter( $( this ) );
			}
		).on(
			'mouseleave',
			function () {
				self.mouseLeave( $( this ) )
			}
		);
		self.$swatch.on(
			'click',
			function () {
				self.onClick( $( this ) );
			}
		);
		$( 'body' ).on( 'click', '.svsw-reset', self.resetVariations );
		$( '.svsw-swatch-radio label' ).on( 'click', self.onClickRadio );
		$( '.svsw-swatch-dropdown' ).on(
			'change',
			function () {
				self.changeSelect( $( this ) );
			}
		);

		// add class svsw-invalid when select is empty value
		$( '.svsw-attr-wrap select' ).each(
			function () {
				if ( ! $( this ).val() ) {
					$( this ).addClass( 'select-invalid' );
				}
			}
		);
		$( '.svsw-attr-wrap select' ).on(
			'change',
			function () {
				if ( ! $( this ).val() ) {
					$( this ).addClass( 'select-invalid' );
				} else {
					$( this ).removeClass( 'select-invalid' );
				}
			}
		);
	};

	SVSwatch.prototype.onLoad     = function () {
		$( '.svsw-attr-wrap' ).each(
			function () {
				var AN = $( this ).data( 'taxonomy' ); // attribute name
				var AV = 'undefined' !== typeof $( '#' + AN ).val() ? $( '#' + AN ).val() : '';

				$( this ).find( '.svsw-swatch-content, .svsw-swatch-dropdown option, input[type="radio"]' ).each(
					function () {
						var av = $( this ).find( '.svsw-swatch' ).data( 'term' );
						if ( typeof av === 'undefined' || av.length === 0 ) {
							if ( $( this ).val().toString() === AV ) {
								// $(this).val( AV ).trigger( 'chagne' );
								$( this ).prop( 'selected', true ).trigger( 'change' );
							}
						} else if ( av.toString() === AV ) {
							$( this ).find( '.svsw-swatch' ).trigger( 'click' );
						}
					}
				);
			}
		);
	}
	SVSwatch.prototype.mouseEnter = function ( item ) {
		// remove any remaining tooltip.
		$( 'body' ).find( '.svsw-tooltip' ).remove();

		if ( item.hasClass( 'svsw-color-image' ) ) {
			var tooltip = item.data( 'tooltip' ) ?? '';
			var img     = item.data( 'img' ) || '';

			var html = img.length ? '<img src="' + img + '">' : '';
			html    += tooltip.length ? '<p>' + tooltip + '</p>' : '';

			if ( html.length ) {
				item.html( '<div class="svsw-tooltip">' + html + '</div>' );
			}
		}
	}
	SVSwatch.prototype.mouseLeave = function ( item ) {
		$( 'body' ).find( '.svsw-tooltip' ).remove();
	}

	SVSwatch.prototype.onClick        = function ( element ) {
		var self = this;
		var val  = element.data( 'term' );
		var name = element.closest( '.svsw-attr-wrap' ).data( 'taxonomy' );

		// handle attribute selection
		self.attrSelection( element, name, val );
		self.addClearBtn();

		self.hideVariations( element );
	}
	SVSwatch.prototype.attrSelection  = function ( item, name, val ) {
		// for radio buttons remove checked status.
		item.closest( '.svsw-attr-wrap' ).find( 'input[type="radio"]' ).each(
			function () {
				$( this ).prop( 'checked', false );
			}
		);

		// select only current item and remove all attribute items selected from current attribute.
		if ( item.hasClass( 'svsw-selected' ) ) {
			item.removeClass( 'svsw-selected' );
			$( '#' + name ).val( '' ).trigger( 'change' );
		} else {
			item.closest( '.svsw-attr-wrap' ).find( '.svsw-swatch' ).removeClass( 'svsw-selected' );
			item.addClass( 'svsw-selected' );
			$( '#' + name ).val( val ).trigger( 'change' );
		}

		if ( item.find( 'input[type="radio"]' ) ) {
			item.find( 'input[type="radio"]' ).prop( 'checked', true );
		}

		// remove dropdown selection as well.
		item.closest( '.svsw-wrap' ).find( 'select' ).val( '' );
	}
	SVSwatch.prototype.setAttr        = function ( name, val ) {
		if ( ! name.length && ! val.length ) {
			return;
		}

		$( '#' + name ).val( val ).trigger( 'change' );
	}
	SVSwatch.prototype.disableAttr    = function ( item ) {
		var is_disabled = false;

		// check if has disable flag | from woocommerce
		if ( typeof $( '.woocommerce-variation-availability' ).find( '.out-of-stock' ) != 'undefined' && $( '.woocommerce-variation-availability' ).find( '.out-of-stock' ).length > 0 ) {
			item.addClass( 'svsw-disabled' ); // globally disabled
		} else {
			// if any item was disabled - enable it | could be this item also
			$( '.svsw-swatch' ).each(
				function () {
					$( this ).removeClass( 'svsw-disabled' );
				}
			);
		}
	}
	SVSwatch.prototype.addClearBtn    = function () {
		// add clear button
		if ( typeof $( '.svsw-frontend-wrap' ).find( '.svsw-reset' ) != 'undefined' && $( '.svsw-frontend-wrap' ).find( '.svsw-reset' ).length == 0 ) {
			$( '.svsw-frontend-wrap' ).append( '<a class="svsw-reset reset_variations" href="#" style="visibility: visible;">Clear</a>' );
		}
	}
	SVSwatch.prototype.hideVariations = function ( element ) {
		var AN = element.closest( '.svsw-attr-wrap' ).data( 'taxonomy' );
		var AV = element.data( 'term' );
		AV     = typeof element.val() !== 'undefined' && element.val().length > 0 ? element.val() : AV;
		// console.log( AV );

		var behave = svsw_front.settings.variation_behavior;
		behave     = 'undefined' === typeof behave ? 'avail' : behave;

		var selectedAtts = {}; // selected attributes with value
		$( '.svsw-attr-wrap' ).each(
			function () {
				var an = $( this ).data( 'taxonomy' );
				$( this ).find( '.svsw-swatch-content, .svsw-swatch-dropdown option, input[type="radio"]' ).each(
					function () {
						var av         = $( this ).find( '.svsw-swatch' ).data( 'term' ) || $( this ).val(); // option value
						var isSelected = $( this ).find( '.svsw-swatch' ).hasClass( 'svsw-selected' ) || $( this ).is( ':checked' );
						if ( isSelected && av.length > 0 ) {
							selectedAtts[ an ] = av.toString();
						}
					}
				);
			}
		);
		// console.log( 'selected attributes', selectedAtts );

		// if element is selected, determine other available attributes which has element's attribute value
		var Unavailable = {};
		$.each(
			svsw_front.variations,
			function ( i, d ) {
				var selectMatch = '';
				$.each(
					d.attributes,
					function ( an, av ) {
						av = av.toString();
						an = an.replace( 'attribute_', '' );

						var isSelected = typeof selectedAtts[an] !== 'undefined' && selectedAtts[an] === av;
						var hasValue   = typeof av !== 'undefined' && av.length > 0;
						if ( hasValue && isSelected ) {
							selectMatch = true;
						}
					}
				);
				// console.log( 'select matched?', selectMatch );
				if ( ! d.is_in_stock && selectMatch ) {
					$.each(
						d.attributes,
						function ( an, av ) {
							av             = av.toString();
							an             = an.replace( 'attribute_', '' );
							var isCurrent  = an === AN && av === AV;
							var isSelected = typeof selectedAtts[an] !== 'undefined' && selectedAtts[an] === av;
							var hasValue   = typeof av !== 'undefined' && av.length > 0;
							// console.log( an, av, 'current', isCurrent, 'sel', isSelected );
							if ( hasValue && ! isCurrent && ! isSelected ) {
								// console.log( 'okay' );
								if ( typeof Unavailable[an] === 'undefined' ) {
									// console.log( 'adding', av );
									Unavailable[an] = [av];
								} else if ( Unavailable[an].indexOf( av ) === -1 ) {
									// console.log( 'adding extra', av );
									Unavailable[an].push( av );
								}
							}
						}
					);
				}
			}
		);
		// console.log( 'Unavailable atts', Unavailable );

		var anySelected = typeof selectedAtts[ AN ] === 'undefined' || selectedAtts[ AN ].length === 0 ? false : true;
		console.log( 'any selected', anySelected );
		var Available = {};
		$.each(
			svsw_front.variations,
			function ( i, d ) {
				var avs = d.attributes[ AN ] || d.attributes[ 'attribute_' + AN ];
				// has at least one option of selected attribute
				var inSelected = 'undefined' !== typeof avs && avs.toString() === AV ? true : false;
				inSelected     = typeof avs === 'undefined' || avs.length === 0 ? true : inSelected;
				console.log( AN, avs, '/', AV, 'in selected?', inSelected, typeof avs, );

				$.each(
					d.attributes,
					function ( an, av ) {
						av = av.toString();
						an = an.replace( 'attribute_', '' );

						var notAvailable = typeof Unavailable[an] !== 'undefined' && Unavailable[an].indexOf( av ) !== -1;
						var hasValue     = typeof av !== 'undefined' && av.length > 0;
						var s            = '';
						console.log( an, av, an !== AN, inSelected, hasValue );
						if ( ! anySelected || ( an !== AN && inSelected && hasValue ) ) {
							// not in stock and not selected
							s += '1';
							console.log( an, av, 'stock', d.is_in_stock, 'not avail?', notAvailable );
							if ( notAvailable && hasValue && typeof Available[an] === 'undefined' ) {
								s            += '+2';
								Available[an] = [];
							}

							// in stock and not in unavailableable
							if ( ( d.is_in_stock && ! notAvailable ) ||
							( notAvailable && 'disable' === behave ) ||
							( ! d.is_in_stock && ! notAvailable && ! anySelected ) ) {
								s += '+3';
								if ( hasValue && typeof Available[an] === 'undefined' ) {
									s            += '+4';
									Available[an] = [av];
								} else if ( hasValue && Available[an].indexOf( av ) === -1 ) {
									s += '+4.1';
									Available[an].push( av );
								}
							}
						}
						console.log( an, av, s );
					}
				);
			}
		);
		console.log( 'Availableable atts', Available );

		$( '.svsw-attr-wrap' ).each(
			function () {
				var an = $( this ).data( 'taxonomy' ).toString();
				// console.log( an );
				$( this ).find( '.svsw-swatch-content, .svsw-swatch-dropdown option, input[type="radio"]' ).each(
					function () {
						var swatch = $( this );
						var av     = swatch.find( '.svsw-swatch' ).data( 'term' );
						if ( 'undefined' === typeof av || 0 === av.length ) {
							av = swatch.val();
						} else {
							swatch = swatch.find( '.svsw-swatch' );
						}
						av = av.toString();

						var isAvailable   = 'undefined' !== typeof Available[ an ] && -1 !== Available[ an ].indexOf( av );
						var isUnavailable = typeof Unavailable[ an ] !== 'undefined' && Unavailable[ an ].indexOf( av ) !== -1;

						isAvailable = typeof Available[ an ] === 'undefined' ? true : isAvailable;

						var s = '';
						if ( an !== AN ) {
							// ! Available = hide
							s += '1';
							if ( isAvailable ) {
								s += ' > 2';
								swatch.removeClass( 'svsw-disabled' ).prop( 'disabled', false );
								swatch.show();
								if ( isUnavailable && 'disable' === behave ) {
									s += ' > 3';
									swatch.addClass( 'svsw-disabled' ).prop( 'disabled', true );
								}
							} else {
								s += ' > 2.1';
								swatch.hide();
							}
						}
						console.log( av, s );
					}
				);
			}
		);
	}

	SVSwatch.prototype.resetVariations = function (e) {
		e.preventDefault();

		// clear selection
		$( '.svsw-swatch' ).removeClass( 'svsw-selected' );

		// remove radion button selection
		$( '.svsw-swatch-radio input' ).prop( 'checked', false );

		// variation dropdown selection
		$( 'table.variations .reset_variations' ).trigger( 'click' );
		$( '.svsw-swatch-dropdown' ).val( '' );

		// remove reset button
		$( '.svsw-frontend-wrap' ).find( '.svsw-reset' ).remove();

		$( '.svsw-swatch' ).show();
		$( '.svsw-disabled' ).prop( 'disabled', false );
		$( '.svsw-swatch' ).removeClass( 'svsw-disabled' );
		$( '.svsw-swatch-dropdown option' ).show();
	}
	SVSwatch.prototype.onClickRadio    = function () {
		var parent = $( this ).closest( '.svsw-swatch-content' );
		parent.find( '.svsw-swatch-radio input' ).prop( 'checked', true );
	}
	SVSwatch.prototype.changeSelect    = function ( element ) {
		var self = this;
		var val  = element.find( 'option:selected' ).val();
		var name = element.closest( '.svsw-attr-wrap' ).data( 'taxonomy' );

		self.setAttr( name, val );
		self.addClearBtn();

		// remove swatch item selection as well.
		element.closest( '.svsw-wrap' ).find( '.svsw-swatch' ).each(
			function () {
				if ( $( this ).hasClass( 'svsw-selected' ) ) {
					$( this ).removeClass( 'svsw-selected' );
				}
			}
		);

		self.hideVariations( element );
	}

	/**
	 * Initialize product total class
	 */
	$(
		function () {
			new SVSwatch();
		}
	);
})( jQuery, window, document );
