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
            this.getState();
            $(document).on('click change', '.svsw-swatch', function(){
                self.swatchClickedEventHandler($(this));
            });
        }
        getState(){ // get initial attribute -> 
            //
            console.log('initial state', this.$state);
        }
        updateState(item){ // update current state data.
            //
        }
        swatchClickedEventHandler(item){ // swatch item click
            console.log('[swatch clicked]');
            this.updateState(item);
            console.log('state', this.$state);
        }
    }
    new simpeVariationSwatches();
})(jQuery, window, document);
