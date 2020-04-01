/**
 * Custom Javascript for automatically adding keys to fields
 */
jQuery(document).ready( function() {

    /**
     * 
     * @param {string} value Sanitizes a string to a key. Similar to the WordPress sanitize_key function
     */
    var sanitizeKey = function(value) {
        value = value.toLowerCase().replace('/[^a-z0-9_\-]/', '').replace(/ /g, '_');
        return value;
    };    

    /**
     * Loop through all our fields and set keys if not set already
     */
    jQuery('#waterfall_options .wfr-key-field').each( function(index, fieldName) {

        var fieldTarget = jQuery(fieldName).closest('.wp-custom-fields-repeatable-fields').find('.wfr-key-target')[0];

        if( fieldName.value && ! fieldTarget.value ) {
            console.log(sanitizeKey(fieldName.value));
            fieldTarget.value = sanitizeKey(fieldName.value);
        }

    } );

    /**
     * Listen to change events for new fields
     */
    jQuery('#waterfall_options .wfr-key-field').on('')

    if( ! fieldName.value && ! fieldTarget.value ) {
        jQuery(fieldName).change( function(event) {
            fieldTarget.value = event.target.value;
        });
    }

} );