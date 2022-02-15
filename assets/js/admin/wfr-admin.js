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

        if( target ) {
            if( jQuery(target).hasClass('wfr-property-option') ) {
                context = '_property';
            }

            if( jQuery(target).hasClass('wfr-criteria-option') ) {
                var criteria = target.classList[3].replace('wfr-criteria-', '_');
                context = criteria + '_attribute';
            }     
        } 

        key = value.toLowerCase();
        key = key.replace(/[^a-z0-9_-]/g,'');
       
        return key + context;

    };    

    /**
     * Loop through all our fields and set keys if not set already
     */
    jQuery('#waterfall_options .wfr-key-field').each( function(index, fieldName) {

        var fieldTarget = jQuery(fieldName).closest('.wpcf-repeatable-fields').find('.wfr-key-target')[0];

        if( fieldName.value && ! fieldTarget.value ) {

            fieldTarget.value = sanitizeKey(fieldName.value, fieldTarget);

        }

    } );

    /**
     * Listen to change events for new fields
     */
    jQuery(document).on('change', '.wfr-key-field', function(event) {

        var fieldName   = this;
        var fieldTarget = jQuery(fieldName).closest('.wpcf-repeatable-fields').find('.wfr-key-target')[0];

        if( fieldName.value && ! fieldTarget.value ) {
            fieldTarget.value = sanitizeKey(fieldName.value, fieldTarget);
        }
        
    });

    /**
     * Listen to changes of our plans, and add them to any related fields accordingly
     */
    var addPlansDynamically = function() {
        var plansElementGroups = document.querySelector('.wfr-plans-meta .wpcf-repeatable-groups');

        if( ! plansElementGroups || plansElementGroups.length < 1 ) {
            return;
        } 
            
        var triggerReplaceFields = function() {
            var fields = jQuery(document).find('.wfr-plans-meta .field-id-name input');

            if( ! fields || fields.length < 1 ) {
                return;
            }
                
            var plans = {};
            for( var field of fields ) {
                
                if( ! field.value || field.value === '' ) {
                    continue;
                }

                var key = sanitizeKey(field.value, '');
                plans[key] = { text: field.value, selected: false };
            }

            if( plans.length < 1 ) {
                return;
            }

            // Retrieve all select fields
            var selectFields = document.querySelectorAll('.wfr-meta-linked-plans .field-id-plan select');
            if( ! selectFields || selectFields.length < 1 ) {
                return;
            }

            for( var selectField of selectFields ) {
                var selectFieldOptions = selectField.querySelectorAll('option');

                for( var option of selectFieldOptions ) {

                    if( option.value === '' ) {
                        continue;
                    }
                    
                    if( plans.hasOwnProperty(option.value) ) {
                        plans[option.value] = { text: option.text, selected: option.selected };
                    }

                    option.remove();
                }

                for( var planKey in plans ) {
                    if ( ! plans.hasOwnProperty(planKey)) {
                        continue;
                    }
                    var newOption = document.createElement('option');
                    newOption.value = planKey;
                    newOption.innerHTML = plans[planKey].text;
                    newOption.selected = plans[planKey].selected;
                    selectField.append(newOption);
                }
            }
        };

        // Watch changes in our plans
        jQuery(document).on('change', '.wfr-plans-meta .field-id-name input', function() {
            triggerReplaceFields();
        });

        jQuery(document).on('click', '.wfr-plans-meta .wpcf-repeatable-remove-group', function() {
            setTimeout( function() {
                triggerReplaceFields();
            }, 600);             
        });            
    };

    addPlansDynamically();

    /**
     * Insert the calculated rating into the general rating
     */
    var calculateAutomaticRating = function() {
        if( jQuery('.wfr-review-meta').length > 0 && jQuery('.wfr-review-meta').hasClass('wfr-rating-calculation-automatic') ) {
            if( typeof wp.data !== 'undefined' ) {

                var updatedMeta = {};
                var wasSaving = false;
                var wpEditor = wp.data.select('core/editor');

                // After saving, insert our updated rating
                wp.data.subscribe( () => {

                    // Only applied to reviews post type
                    if( wpEditor.getCurrentPostType() !== 'reviews' ) {
                        return;
                    }           

                    if( wpEditor.isSavingPost() ) {
                        wasSaving = true;
                        return;
                    }

                    if( wasSaving ) {
                        var postId    = wpEditor.getCurrentPostId();
            
                        // We have to fetch our data somewhat later, because the updated meta is not immediately available.
                        setTimeout( function() {
            
                            wp.apiFetch({ path: 'wp/v2/reviews/' + postId })
                                .then( post => {
            
                                    // Nothing to update
                                    if( Object.keys(post.meta).length === 0 || updatedMeta === post.meta ) {
                                        return;
                                    }
            
                                    updatedMeta = post.meta;

                                    if( updatedMeta.hasOwnProperty('rating') ) {
                                        jQuery('#rating').val(updatedMeta.rating);
                                    }

                                })
                                .catch( error => console.log(error) );
                        }, 1000);

                    }

                    wasSaving = false;
                });
            }  
        }
    };

    calculateAutomaticRating();

} );