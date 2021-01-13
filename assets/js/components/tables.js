/**
 * Handles our chart actions
 */
var utils       = require('./../utils');

var Tables = {

    reviews: [],
    initialize: function() {

        var self = this;        

        // Draw charts based upon changing the select form
        jQuery(document).on('click', '.wfr-tables-form li', function() {

            var review = this.dataset.target

            // Change class
            jQuery(this).toggleClass('active');

            // Remove the item if it's already there
            if( self.reviews.includes(review) ) {
                for(var i = self.reviews.length - 1; i >= 0; i--) {
                    if(self.reviews[i] === review) {
                        self.reviews.splice(i, 1);
                    }
                }
            } else {
                self.reviews.push(review);   
            }

            self.loadTables(this);

        });

        // Prevent submitting of the default form
        jQuery('.wfr-tables-form').submit( function(event) {
            event.preventDefault();
        });       

    },

    /**
     * Creates our table loader - upon changes it will load a new table
     * 
     * @param object The item for the current objective
     */
    loadTables: function(object) {

        if( this.reviews.length < 2 ) {
            return;
        }

        var form = jQuery(object).closest('.wfr-tables-form'),
            self = this,
            view = jQuery(form).next('.wfr-tables-view');

        console.log(view);

        utils.ajax({
            beforeSend: function() {
                jQuery(view).addClass('components-loading');
            },
            complete: function() {
                jQuery(view).removeClass('components-loading');
            },
            data: {
                action: 'loadTables', 
                attributes: jQuery(form).data('attributes'),
                categories: jQuery(form).data('categories'),
                groups: jQuery(form).data('groups'),
                reviews: self.reviews,
                nonce: wfr.nonce,
                properties: jQuery(form).data('properties'),
                tags: jQuery(form).data('tags'),
                view: jQuery(form).data('view'),
                weight: jQuery(form).data('weight')
            },
            success: function(response) {
                
                if( wfr.debug ) {
                    console.log(response);
                }

                if( ! response.success ) {
                    return;
                }

                jQuery(view).replaceWith( jQuery(response.data).find('.wfr-tables-view') );

            }
        });

    }
    
};

module.exports = Tables;