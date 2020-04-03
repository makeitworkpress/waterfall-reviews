/**
 * Custom Javascript for automatically adding keys to fields
 */
jQuery(document).ready( function() {

    /**
     * 
     * @param {string} value Sanitizes a string to a key. Similar to the WordPress sanitize_key function
     * @param {node} target The target input field that needs a sanitized key
     */
    var sanitizeKey = function(value, target) {

        var key, context = '';

        if( jQuery(target).hasClass('wfr-property-option') ) {
            context = '_property';
        }

        if( jQuery(target).hasClass('wfr-criteria-option') ) {
            var criteria = target.classList[3].replace('wfr-criteria-', '_');
            context = criteria + '_attribute';
        }        

        key = value.toLowerCase();
        key = key.replace(/[^a-z0-9_-]/g,'');
       
        return key + context;

    };    

    /**
     * Loop through all our fields and set keys if not set already
     */
    jQuery('#waterfall_options .wfr-key-field').each( function(index, fieldName) {

        var fieldTarget = jQuery(fieldName).closest('.wp-custom-fields-repeatable-fields').find('.wfr-key-target')[0];

        if( fieldName.value && ! fieldTarget.value ) {

            fieldTarget.value = sanitizeKey(fieldName.value, fieldTarget);

        }

    } );

    /**
     * Listen to change events for new fields
     */
    jQuery(document).on('change', '.wfr-key-field', function(event) {

        var fieldName   = this;
        var fieldTarget = jQuery(fieldName).closest('.wp-custom-fields-repeatable-fields').find('.wfr-key-target')[0];

        if( fieldName.value && ! fieldTarget.value ) {
            fieldTarget.value = sanitizeKey(fieldName.value, fieldTarget);
        }
        
    });

} );