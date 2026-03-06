/**
 * Frontend JavaScript
 *
 * @package    Wordpress
 * @subpackage Simple Variation Swatches
 * @since      3.0
 */

(function($, window, document){
    class simpeVariationSwatches{
        constructor(){
            this.$state = {}; // current variation attributes state.
            $(document).ready(() => {
                this.initSwatchs();
            });
        }
        initSwatchs(){
            const self = this;
            this.initialStateHandler();
            $(document).on('click change', '.svsw-swatch', function(){
                self.swatchClickedEventHandler($(this));
            });
            $(document).on('click', '.svsw-reset', () => {
                this.clearVariations();
            });
        }
        initialStateHandler(){
            this.getState();
            // use default attribute values, if any.
            this.imposeStateOnSwatches($(document).find('.svsw-frontend-wrap .svsw-attr-wrap'), 'swatch');
            this.clearVariationsBtn();
        }
        getState(){ // get initial attribute -> 
            const self = this;
            const atts = $(document).find('table.variations select');
            if(!atts || 0 === atts.length) return;

            atts.each(function(){
                const attName = $(this).attr('data-attribute_name');
                self.$state[attName] = $(this).find('option:selected').val() ?? '';
            });
        }
        clearVariationsBtn(){
            let add = false; // add clear variations button or not.
            $.each(this.$state, function(attName, attValue){
                if(attValue && attValue.length > 0) add = true;
            });

            const resetWrap = $(document).find('.svsw-reset');
            if(add && resetWrap && resetWrap.length > 0) return;

            if(add){
                $(document).find('.svsw-frontend-wrap').append('<a class="svsw-reset reset_variations" href="#" style="visibility: visible;">Clear</a>');
            }else{
                resetWrap.remove();
            }
        }
        imposeStateOnSwatches(items, type){
            const self = this;
            items.each(function(){
                self.imposeStateOnSwatch($(this), type);
            });
        }
        imposeStateOnSwatch(swatchWrap, type){
            const attName  = swatchWrap.attr('data-attribute_name');
            const attValue = this.$state[attName];

            if('swatch' === type && !attValue) this.removeDisabledClass(swatchWrap);

            if('default' === type || swatchWrap.hasClass('svsw-swatch-dropdown')){ // select.
                swatchWrap.val(attValue).trigger('change');
                return;
            }

            swatchWrap.find('.svsw-swatch').each(function(){
                if($(this).hasClass('svsw-swatch-radio')){
                    const field = $(this).find('input[type="radio"]');
                    field.prop('checked', field.val() === attValue);
                    
                    $(this).toggleClass('svsw-selected', field.val() === attValue);
                }else{
                    $(this).toggleClass('svsw-selected', $(this).attr('data-attribute_value') === attValue);
                }
            });
        }
        removeDisabledClass(swatchWrap){
            if(swatchWrap.hasClass('svsw-swatch-dropdown')){ // select.
                swatchWrap.find('option').each(function(){
                    $(this).removeClass('svsw-disabled');
                });
                return;
            }

            swatchWrap.find('.svsw-swatch').each(function(){
                $(this).removeClass('svsw-disabled');
            });
        }
        swatchClickedEventHandler(item){ // swatch item click
            this.updateState(item);

            this.imposeStateOnEverything();
            this.clearVariationsBtn();

            setTimeout(() => {
                this.availableVariationsHandler(item);
            }, 100);
        }
        updateState(item){ // update current state data.
            const attName = item.closest('.svsw-attr-wrap').attr('data-attribute_name');
            this.$state[attName] = item.hasClass('svsw-swatch-drodpwn') ? item.find('option:selected').val() : item.attr('data-attribute_value');
        }
        imposeStateOnEverything(){
            this.imposeStateOnSwatches($(document).find('.svsw-frontend-wrap .svsw-attr-wrap'), 'swatch');
            this.imposeStateOnSwatches($(document).find('table.variations select'), 'default');
        }
        clearVariations(){
            const self = this;
            $.each(this.$state, function(attName, attValue){ // clear state values.
                self.$state[attName] = '';
            });
            this.imposeStateOnEverything();
            $(document).find('.svsw-reset').remove();
        }
        availableVariationsHandler(item){
            let availableVariations = {};
            $(document).find('table.variations select').each(function(){
                const attName = $(this).attr('data-attribute_name');
                $(this).find('option').each(function(){
                    const attValue = $(this).val();
                    if(!availableVariations[attName]) availableVariations[attName] = [];
                    if(attValue && attValue.length > 0) availableVariations[attName].push(attValue);
                });
            });
            this.filterAvailableVariations(availableVariations);
        }
        filterAvailableVariations(availableVariations){
            const self = this;
            $(document).find('.svsw-frontend-wrap .svsw-attr-wrap').each(function(){
                const attName = $(this).attr('data-attribute_name');
                $(this).find('.svsw-swatch').each(function(){
                    self.filterAvailableVariation(availableVariations[attName], $(this));
                });
            });
        }
        filterAvailableVariation(availableVariations, item){
            if(item.hasClass('svsw-swatch-dropdown')){ // select.
                item.find('option').each(function(){
                    const attValue = $(this).val();
                    $(this).toggleClass('svsw-disabled', -1 === availableVariations.indexOf(attValue)); // $(this).toggle(!!availableVariations);
                });
            }else{
                const attValue = item.attr('data-attribute_value');
                item.toggleClass('svsw-disabled', -1 === availableVariations.indexOf(attValue));
            }
        }
    }
    new simpeVariationSwatches();
})(jQuery, window, document);
