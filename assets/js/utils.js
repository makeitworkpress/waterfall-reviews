/**
 * A shorthand for executing jQuery ajax functions
 * @param {object} options The ajax options
 */
module.exports.ajax = function(options) {
    
    var options = options;
    
    options.data.nonce = wfr.nonce;
    options.type = 'POST';
    options.url = wfr.ajaxUrl;
    
    return jQuery.ajax(options);
    
};

/**
 * Automatically set the value of a range field to a nearby span field
 * 
 * @param {object} input The range input slider
 */
module.exports.rangeValue = function(input) {

    var style = jQuery(input).data('style'),
        value = jQuery(input).val();

    if( style === '' ) {
        return;
    }

    if( style === 'percentages' ) {
        value = parseInt( (value/jQuery(input).attr('max')) * 100 ) + '%';
    }

    jQuery(input).next('.wfr-range-value').html(value);

};