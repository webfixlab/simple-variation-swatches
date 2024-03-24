(function($) {

    /**
     * using global variable
     * @param svsw_frontend
     */

    // swatch on hover show tooltip.
    $( '.svsw-swatch' ).on( 'mouseenter', function(){
        // remove any remaining tooltip.
        $( 'body' ).find( '.svsw-tooltip' ).remove();
        
        if( $(this).hasClass( 'svsw-color-image' ) ){
            var tooltip = '';
            if( typeof $(this).data( 'tooltip' ) != 'undefined' && $(this).data( 'tooltip' ).length > 0 ){
                tooltip = $(this).data( 'tooltip' );
            }

            var img = '';
            if( typeof $(this).data( 'img' ) != 'undefined' && $(this).data( 'img' ).length > 0 ){
                img = $(this).data( 'img' );
            }

            var html = '';
            if( img.length ){
                html = '<img src="' + img + '">'
            }
            if( tooltip.length ){
                html += '<p>' + tooltip + '</p>';
            }

            if( html.length ){
                $(this).html( '<div class="svsw-tooltip">' + html + '</div>' );
            }
        }

        
    }).on( 'mouseleave', function(){
        $( 'body' ).find( '.svsw-tooltip' ).remove();
    });

    // handle if this event has any disbabled variation swatches
    function handle_if_swatch_is_disabled( item ){
        var is_disabled = false;
        
        // check if has disable flag | from woocommerce
        if( typeof $( '.woocommerce-variation-availability' ).find( '.out-of-stock' ) != 'undefined' && $( '.woocommerce-variation-availability' ).find( '.out-of-stock' ).length > 0 ){

            // globally disabled
            item.addClass( 'svsw-disabled' );

        }else{

            // if any item was disabled - enable it | could be this item also
            $( '.svsw-swatch' ).each(function(){
                $(this).removeClass( 'svsw-disabled' );
            });

        }
    }

    // handle attribute selection.
    function handle_att_selection( item ){

        // select only current item and remove all attribute items selected from current attribute.
        item.closest( '.svsw-attr-wrap' ).find( '.svsw-swatch' ).removeClass( 'svsw-selected' );
        
        item.addClass( 'svsw-selected' );

        // remove dropdown selection as well.
        item.closest( '.svsw-wrap' ).find( 'select' ).val( '' );
    }

    // set woocommerce dropdown value
    function change_wc_dropdown( taxonomy, term ){
        // taxonomy | attribute name
        // term     | attribute option value
        if( ! taxonomy.length && ! term.length ){
            return;
        }
        
        $( '#' + taxonomy ).val( term );
        $( '#' + taxonomy ).trigger( 'change' );
    }
    // handle clear variation dropdown selection button
    function add_clear_button(){

        // add clear button
        if( typeof $( '.svsw-frontend-wrap' ).find( '.svsw-reset' ) != 'undefined' && $( '.svsw-frontend-wrap' ).find( '.svsw-reset' ).length == 0 ){
            $( '.svsw-frontend-wrap' ).append( '<a class="svsw-reset reset_variations" href="#" style="visibility: visible;">Clear</a>' );
        }
    }
    // on swatch item clicked event
    $( '.svsw-swatch' ).on( 'click', function(){
        
        // handle attribute selection
        handle_att_selection( $(this) );

        var term     = $(this).data( 'term' );
        var taxonomy = $(this).closest( '.svsw-attr-wrap' ).data( 'taxonomy' );
        change_wc_dropdown( taxonomy, term );

        handle_if_swatch_is_disabled( $(this) );

        add_clear_button();
    });

    // on reset button click event
    $( 'body' ).on( 'click', '.svsw-reset', function(e){
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
    });

    // radio button label click event | auto check radio button input field
    $( '.svsw-swatch-radio label' ).on( 'click', function(){
        var parent = $(this).closest( '.svsw-swatch-content' );

        parent.find( '.svsw-swatch-radio input' ).prop( 'checked', true );
    });

    // on svsw dropdown item change event
    $( '.svsw-swatch-dropdown' ).on( 'change', function(){
        var term     = $(this).find( 'option:selected' ).val();
        var taxonomy = $(this).closest( '.svsw-attr-wrap' ).data( 'taxonomy' );
        change_wc_dropdown( taxonomy, term );

        add_clear_button();

        // remove swatch item selection as well.
        $(this).closest( '.svsw-wrap' ).find( '.svsw-swatch' ).each(function(){
            if( $(this).hasClass( 'svsw-selected' ) ){
                $(this).removeClass( 'svsw-selected' );
            }
        });
    });

    // add class svsw-invalid when select is empty value
    $( '.svsw-attr-wrap select' ).each(function() {
        if( !$(this).val() ) {
            $(this).addClass( 'select-invalid' );
        }
    });
    $( '.svsw-attr-wrap select' ).on( 'change', function() {
        if( !$(this).val() ) {
            $(this).addClass( 'select-invalid' );
        } else {
            $(this).removeClass( 'select-invalid' );
        }
    });

})(jQuery);