/**
 * Handles our comments actions
 */
var utils  = require('./../utils');

var Comments = {

    initialize: function() {

        // Sets the value
        jQuery('.comment-form-rating input').change( function() {
            utils.rangeValue(this);
        });

        jQuery('#wfr-reply').change( function() {
            if( this.checked ) {
                jQuery(this).closest('.comment-form-reply').prevAll('.comment-form-rating').slideUp();
            } else {
                jQuery(this).closest('.comment-form-reply').prevAll('.comment-form-rating').slideDown();   
            }  
        });

    },

};

module.exports = Comments;