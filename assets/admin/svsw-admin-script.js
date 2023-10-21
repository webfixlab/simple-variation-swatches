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
            console.log( type );

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

        $( 'input[type="submit"' ).on( 'click', function(e){
            // e.preventDefault();

            // check if it's possible to keep structural variation data - make it possible
        });

        // media uploader
        $( '.svsw-upload-image' ).click(function(e){
            e.preventDefault();

            var img_wrap = $(this).closest( '.svsw-input-image' );

            var image = wp.media({ 
                title: 'Upload Image',
                // mutiple: true if you want to upload multiple files at once
                multiple: false
            }).open().on( 'select', function(e){
                // This will return the selected image from the Media Uploader, the result is an object
                var uploaded_image = image.state().get( 'selection' ).first();
                
                // We convert uploaded_image to a JSON object to make accessing it easier
                // Output to the console uploaded_image
            
                // console.log( uploaded_image );

                var image_url = uploaded_image.toJSON().url;
                
                if( typeof image_url != 'undefined' && image_url.length > 0 ){
                    // console.log( image_url );
                    
                    // add a reset button
                    // img_wrap.find( '.svsw-unde-img' ).data( 'img_url', image_url );
                    // img_wrap.find( '.svsw-unde-img' ).show();

                    // check if img tag exists, else add html
                    if( typeof img_wrap.find( 'img' ) != 'undefined' && img_wrap.find( 'img' ).length > 0 ){
                        // change image source
                        var old_url = $( '.svsw-input-image' ).find( 'img' ).attr( 'src' );

                        // keep old image to unde image selection
                        $( '.svsw-input-image' ).find( 'img' ).after( '<span class="dashicons dashicons-undo svsw-undo-img" data-img_url="' + old_url + '"></span>' );
                    }else{
                        // add html
                        img_wrap.append( '<img src="' + image_url + '" class="svsw-admin-img">' );
                    }                    

                    // set image url to the input field (uploaded) value
                    $( '.svsw-uploaded-image' ).val( image_url );

                    // change image viewer
                    img_wrap.find( 'img' ).attr( 'src', image_url );
                }
            });
        });

        // image undo button clicked event
        $( 'body' ).on( 'click', '.svsw-undo-img', function(){
            var img_wrap = $(this).closest( '.svsw-input-image' );

            var old_img_url = $(this).data( 'img_url' ); // or attr function
            // console.log( old_img_url );

            // validate url
            if( typeof old_img_url == 'undefined' || old_img_url.length == 0 ) return;

            // set old image source to selection
            img_wrap.find( '.svsw-uploaded-image' ).val( old_img_url );
            img_wrap.find( 'img' ).attr( 'src', old_img_url );

            // finally remove this item
            $(this).remove();
        });
    });
})(jQuery);