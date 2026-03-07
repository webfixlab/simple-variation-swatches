/**
 * Admin settings page function
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
			$('.svsw-colorpicker').wpColorPicker(); // color swatches.
			
			$(window).on('scroll', () => { // sticky header.
				this.stickyTopBar();
			});
			
			$('.nav-tab').on('click', (e) => { // option page tab navigation.
				this.navigationTabsHandler($(e.currentTarge));
			});

			$(document).on('change', '.svsw-att-type', () => { // load input on attribute type changed.
				this.displayAttTypeInputFeild();
			});

			$(document).on('click', '.svsw-upload-image', (e) => { // media uploader.
				e.preventDefault();
				this.setSwatchImage();
			});

			$(document).on('click', '.svsw-remove-img', (e) => { // on remove swatch image.
				if(confirm(svsw_admin_data.img_delete)){
					this.removeImage();
				}
			});
		}
		stickyTopBar(){
			$(document).find('.svsw-wrap').toggleClass('svsw-sticky-top', $(window).scrollTop() > 40);
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
		displayAttTypeInputFeild(){
			const swatchType = $(document).find('.svsw-att-type option:selected').val();
			if(!swatchType || 0 === swatchType.length) return;

			// hide all input fields except current one.
			$('.svsw-input-field').hide();
			$(`.svsw-input-${swatchType}`).show();
		}
		setSwatchImage(){
			const mediaObj = wp.media({
				title    : 'Upload Image',
				multiple : false
			})
			.open()
			.on('select', (e) => {
				this.attachImageToSwatch(mediaObj.state().get('selection').first()); // get only first media item.
			});
		}
		attachImageToSwatch(imageObj){
			const imageData = imageObj.toJSON();
			console.log('url', imageData.url, 'id', imageData.id);

			if(!imageData.url.length) return;

			const imageWrap = $(document).find('.svsw-input-image img');
			if(imageWrap && imageWrap.length > 0) imageWrap.attr('src', imageData.url);
			else $(document).find('.svsw-input-image').append(`<img src="${imageData.url}" class="svsw-admin-img"><span class="dashicons dashicons-remove svsw-remove-img"></span>`);
			
			this.setImageInputValue(imageData.url);
		}
		setImageInputValue(imageUrl){
			$(document).find('input[name="svsw_image"]').val(imageUrl);
		}
		removeImage(){
			this.setImageInputValue('');
			$(document).find('.svsw-input-image img').remove();
		}
    }
    new SimpleVariationSwatchsAdmin();
})(jQuery, window, document);
