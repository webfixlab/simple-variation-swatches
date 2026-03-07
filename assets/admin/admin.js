/**
 * Admin settings page function
 *
 * @uses svsw_admin_data Admin localized data
 *
 * @package    WordPress
 * @subpackage Simple Variation Swatches
 * @since      3.0.0
 */

(function($, window, document){
    class SimpleVariationSwatchsAdmin{
        constructor(){
            $(document).ready(() => {
                this.initEventTriggers();
            });
        }
		initEventTriggers(){
			const self = this;

			$('.svsw-colorpicker').wpColorPicker();

			$(window).on('scroll', () => { // sticky header/menu.
				this.stickyTopBar();
			});

			$(document).on('click', '.notice.is-dismissible', function(){
				$(this).hide('slow').remove();
			});

			// handle settings nav.
			$('.nav-tab').on('click', function(){
				self.navigationTabsHandler($(this));
			});

			// when this is changed, load color picker.
			$('#variable_product_options').on('change', function(){
				$('.svsw-color-field').wpColorPicker();
			});

			$('body').on('change', '.svsw-type', function(){
				self.showTargetDiv($(this));
			});
			
			$('body').on('change', 'select[name="attribute_type"]', function(){
				self.isRequiredField($(this));
			});

			// media uploader.
			$('.svsw-upload-image').on('click', function(e){
				e.preventDefault();
				self.setSwatchImage($(this));
			});

			// image undo button clicked event.
			$('body').on('click', '.svsw-remove-img', function(e){
				if(confirm(svsw_admin_data.img_delete)){
					self.removeImage($(this));
				}
			});




			var type = $('.svsw-att-type option:selected').val();

			// for button and radio type swatch - make it required.
			if(type == 'radio' || type == 'button'){
				$('.svsw-input-' + type + ' input').attr('required', true);
			}

			this.add_enctype();
		}
		stickyTopBar(){
			$(document).find('.svsw-wrap').toggleClass('svsw-sticky-top', $(window).scrollTop() > 40);
			// if($(window).scrollTop() > 40){
			// 	$('.svsw-wrap').addClass('svsw-sticky-top');
			// }else if($('.svsw-wrap').hasClass('svsw-sticky-top')){
			// 	$('.svsw-wrap').removeClass('svsw-sticky-top');
			// }
		}
		navigationTabsHandler(item){
			if(item.hasClass('nav-tab-active')) return;

			$('.nav-tab').removeClass('nav-tab-active');
			item.addClass('nav-tab-active');

			const target = item.data('target');
			$('.section').hide();
			$(`.svsw-${target}`).show();
			$('input[name="svsw_tab"]').val(target); // for keeping the tab open on save.
		}
		showTargetDiv(item){
			const type = item.val();

			$('.svsw-input').hide();

			const targetDiv = $(document).find(`.svsw-${type}`)
			if(targetDiv && targetDiv.length > 0) targetDiv.show();
		}
		isRequiredField(item){
			const swatchType = item.val();
			$('.svsw-input-field').hide();
			$('.svsw-input-' + swatchType).show();

			// remove all input fields required property.
			$('.svsw-input-field input').removeAttr('required');

			// for button and radio type swatch - make it required.
			if(swatchType == 'radio' || swatchType == 'button'){
				$('.svsw-input-' + swatchType + ' input').attr('required', true);
			}
		}
		setSwatchImage(item){
			const self = this;
			
			const mediaObj = wp.media({
				title    : 'Upload Image',
				multiple : false
			})
			.open()
			.on('select', function(e){
				const imageObj = mediaObj.state().get('selection').first(); // because we set multiple false.
				const imageUrl = imageObj.toJSON().url;
				console.log('media', mediaObj, 'img', imageObj.toJSON());

				self.attachImageToSwatch(item, imageUrl);
			});
		}
		attachImageToSwatch(item, imageUrl){
			if(!imageUrl.length) return;

			const imageSwatchWrap = item.closest('.svsw-input-image');
			const imageWrap = imageSwatchWrap.find('img');

			if(imageWrap && imageWrap.length > 0) imageWrap.attr('src', imageUrl);
			else imageSwatchWrap.append(`<img src="'${imageUrl}" class="svsw-admin-img"><span class="dashicons dashicons-remove svsw-remove-img"></span>`);
			
			this.setImageInputValue(imageUrl);
		}
		setImageInputValue(imageUrl){
			$('.svsw-uploaded-image').val(imageUrl);
		}
		removeImage(imageSwatchWrap){
			const imageWrap = imageSwatchWrap.closest('.svsw-input-image').find('img');
			imageWrap.hide('slow', function(){
				imageWrap.remove();
			});

			imageSwatchWrap.remove();
			
		}



		// handle file uploading pre processing on load.
		add_enctype(){
			// check if document has our image uploading input field.
			var has_input = false;
			var input     = $('body').find('input[name="svsw_image"]');
			if(typeof input != 'undefined' && input.length > 0){
				has_input = true;
			}
			if(has_input == false){
				return;
			}

			// check it's wrapping form, if it has enctype attribute | enctype="multipart/form-data".
			var has_form = false;
			var form     = input.closest('form');
			if(typeof form != 'undefined' && form.length > 0){
				has_form = true;
			}
			if(has_form == false){
				return;
			}

			// check if has form attribute.
			var has_attr = false;
			var attr     = form.attr('enctype');
			if(typeof attr != 'undefined' && attr !== false){
				has_attr = true;
			}

			// if no attribute found, add that.
			if(has_attr == false){
				form.attr('enctype', 'multipart/form-data');
			}
		}
    }
    new SimpleVariationSwatchsAdmin();
})(jQuery, window, document);
