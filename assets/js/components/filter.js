/**
 * Handles our chart actions
 */

var utils  = require('./../utils');

var Filter = {

    /**
     * Contains our filter arguments
     */
    arguments: {},
    currentFilter: null,
    page: 0,

    initialize: function() {

        var self = this;

        jQuery('.wfr-filter').each( function() { 
            self.listener(this);
        });
        this.ratingSlider();
    },

    /**
     * Filters our result
     * 
     * @param object object The current form object that is initiating the filter action
     */
    filter: function(object) {

        this.arguments = new FormData( jQuery(object).get(0) );
        this.arguments.append('nonce', wfr.nonce); // The object wfr is available in global scope, being localized on waterfall-reviews.js
        this.arguments.append('action', 'filter_reviews');

        var target = jQuery(object).data('target');

        // Add the page if we are filtering for pages
        if( this.page ) {
            this.arguments.append('page', this.page);
            this.page = 0;
        }

        // Execute our ajax action
        utils.ajax({
            beforeSend: function() {
                jQuery('div[data-id="' + target + '"]').addClass('components-loading');
            },
            complete: function() {
                jQuery('div[data-id="' + target + '"]').removeClass('components-loading');    
            },
            contentType: false,
            data: this.arguments,
            processData: false,
            success: function(response) {

                if( wfr.debug ) {
                    console.log(response);
                }

                if( ! response.success ) {
                    return;
                }

                if( typeof(response.data.html) !== 'undefined' ) {
                    jQuery('div[data-id="' + target + '"]').replaceWith(response.data.html);

                    if( typeof(lazyload) !== "undefined" ) {
                        lazyload.update();
                    } else if( typeof wpOptimizeLazyLoad !== 'undefined' ) {
                        wpOptimizeLazyLoad.update();   
                    } else if( typeof wpComponentsLazyLoad !== 'undefined' ) {
                        wpComponentsLazyLoad.update();  
                    }

                }

                // Emit a chart change - our chart object will listen to this change
                if( typeof(response.data.posts) !== 'undefined' ) {

                    var object = jQuery('div[data-id="' + target + '"]').closest('.atom-tabs-content').find('.wfr-chart-selector select').get(0);
                    
                    App.components.charts.posts = response.data.posts;
                    App.components.charts.listener(object);

                }

            }
        });

    },

    /**
     * Listens to form changes, in order to filter
     * 
     * @param object object The current form object that is initiating the filter action
     */
    listener: function(object) {

        var self = this;

        // If the input changes, we filter, but only if we have instant filtering
        if( jQuery(object).hasClass('wfr-instant-filter') ) {
            jQuery(object).find(':input').change(function(event) {
                self.filter(object);
            }); 
        }

        // If we submit, we filter
        jQuery(object).submit(function(event) {
            self.filter(object);

            event.preventDefault();

        });

    },

    /**
     * Sets the value of our rating slider
     */
    ratingSlider: function() {

        // Also set value on page load
        utils.rangeValue('.wfr-filter-rating input');

        // Sets the value
        jQuery('.wfr-filter-rating input').change( function() {
            utils.rangeValue(this);
        });

    }

};

module.exports = Filter;