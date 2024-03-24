(function($) {

	/**
	 * using global variable
	 * @param svsw_admin_data
	 */
    
	// sticky header/menu
    $(window).on( 'scroll', function(){
        if( $(window).scrollTop() > 40 ){
            $( '.svsw-wrap' ).addClass( 'svsw-sticky-top' );
        }else{
            if( $( '.svsw-wrap' ).hasClass( 'svsw-sticky-top' ) ){
                $( '.svsw-wrap' ).removeClass( 'svsw-sticky-top' );
            }
        }
    });
    
    $( document ).ready( function(){
        $( '.svsw-color-field' ).wpColorPicker();

        // handle settings nav
        $( '.nav-tab' ).on( 'click', function(){
            // if has active class - skip
            // else remove from others and add to this
            if( ! $(this).hasClass( 'nav-tab-active' ) ){
                $( '.nav-tab' ).removeClass( 'nav-tab-active' );
                $(this).addClass( 'nav-tab-active' );

                var target = $(this).data( 'target' );

                $( '.section' ).hide();
                $( '.svsw-' + target ).show();

                // change input value also
                $( 'input[name="svsw_tab"]' ).val( target );
            }
        });
        
        // when this is changed, load color picker
        $( '#variable_product_options' ).on( 'change', function(){
            $( '.svsw-color-field' ).wpColorPicker();
        });

        $( 'body' ).on( 'change', '.svsw-type', function(){
            var type = $(this).val();

            // display fields accordingly to selection
            $( '.svsw-input' ).hide();

            if( typeof type != 'undefined' && type.length > 3 ){
                $( '.svsw-' + type ).show();
            }
        });

        /**
         * both of this and the one immediately above it, are doing the same thing
         * make it to one
         */
        function svsw_type_contingency( type ){
            $( '.svsw-input-field' ).hide();
            $( '.svsw-input-' + type ).show();
            
            // remove all input fields required property
            $( '.svsw-input-field input' ).removeAttr( 'required' );

            // for button and radio type swatch - make it required
            if( type == 'radio' || type == 'button' ){
                $( '.svsw-input-' + type + ' input' ).attr( 'required', true );
            }
        }

        $( 'body' ).on( 'change', 'select[name="attribute_type"]', function(){
            var type = $(this).val();
            svsw_type_contingency( type );
        });

        var type = $( '.svsw-att-type option:selected' ).val();
        
        // for button and radio type swatch - make it required
        if( type == 'radio' || type == 'button' ){
            $( '.svsw-input-' + type + ' input' ).attr( 'required', true );
        }

        // handle file uploading pre processing on load
        function add_enctype(){
            // check if document has our image uploading input fiel
            var has_input = false;
            var input = $( 'body' ).find( 'input[name="svsw_image"]' );
            if( typeof input != 'undefined' && input.length > 0 ){
                has_input = true;
            }
            if( has_input == false ) return;
            
            // check it's wrapping form, if it has enctype attribute | enctype="multipart/form-data"
            var has_form = false;
            var form = input.closest( 'form' );
            if( typeof form != 'undefined' && form.length > 0 ){
                has_form = true;
            }
            if( has_form == false ) return;            
            
            // check if has form attribute
            var has_attr = false;            
            var attr = form.attr( 'enctype' );            
            if( typeof attr != 'undefined' && attr !== false ){
                has_attr = true;
            }

            // if no attribute found, add that
            if( has_attr == false ){
                form.attr( 'enctype', 'multipart/form-data' );
            }
        }

        add_enctype();
        
        // media uploader
        $( '.svsw-upload-image' ).on( 'click', function(e){
            e.preventDefault();
            var wrap  = $(this).closest( '.svsw-input-image' );

            var image = wp.media({ 
                title    : 'Upload Image',
                multiple : false
            }).open().on( 'select', function(e){
                var uploaded_image = image.state().get( 'selection' ).first();
                var url            = uploaded_image.toJSON().url;
                
                if( url.length ){
                    if( wrap.find( 'img' ).length ){
                        wrap.find( 'img' ).attr( 'src', url );
                    }else{
                        wrap.append( '<img src="' + url + '" class="svsw-admin-img"><span class="dashicons dashicons-remove svsw-remove-img"></span>' );
                    }                    

                    $( '.svsw-uploaded-image' ).val( url );
                }
            });
        });

        // image undo button clicked event
        $( 'body' ).on( 'click', '.svsw-remove-img', function(e){
            if( confirm( svsw_admin_data.img_delete ) ){
                var img = $(this).closest( '.svsw-input-image' ).find( 'img' );
                $(this).remove();
                img.hide( 'slow', function () {
                    img.remove();
                });
            }
        });
    });
})(jQuery);