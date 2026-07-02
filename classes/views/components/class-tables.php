<?php
/**
 * Displays the properties of a single review, including possible criteria properties
 */
namespace Waterfall_Reviews\Views\Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Tables extends Component {
    
    /**
     * Contains our groups and fields
     * @access private
     */
    private $fields = [];

    /**
     * Initializes our component
     */
    protected function initialize() {

        $this->params   = wp_parse_args( $this->params, [
            'attributes'    => [],      // Limits the display only to the given criteria attributes (use attribute meta keys)
            'categories'    => [],      // Only displays data from these review category ids
            'form'          => false,   // Displays a review selection form (or not)
            'groups'        => [],      // Only show the content from the given groups
            'label'         => __('Select at least 2 items to compare', 'wfr'), // Label that is used for selecting reviews
            'load'          => true,    // Loads the table directly if set to true. Warning! Loads all reviews if a query is not defined.
            'price'         => __('Get', 'wfr'), // Button to the affiliate, set false to hide
            'properties'    => [],      // Limits the display only to the given properties (use property meta keys)            
            'query'         => [],      // Arguments to query posts by
            'reviews'       => [],      // Default reviews to load
            'tags'          => [],      // Only displays data from these review tag ids 
            'title'         => true,    // Displaces the title of a review within the table
            'view'          => 'tabs',  // We either show 'tabs' or a complete 'table'
            'weight'        => false,   // If values need to be weighted
        ] );

        $this->template = 'tables'; 

        if( ! wp_script_is('wfr-tables') ) {
            wp_enqueue_script('wfr-tables');
        }

    }

    /**
     * Receives our properties and data from the database
     */
    protected function query() {

        $this->props['class']           = $this->params['view'];

        foreach( ['form', 'label', 'load', 'title', 'view'] as $param ) {
            $this->props[$param]        = $this->params[$param]; 
        }

        $this->props['data']            = [];
        
        foreach( ['attributes', 'categories', 'groups', 'properties', 'tags', 'view', 'weight'] as $key ) {

            // Clean up our arrays of whitespace that may have been adedd through the use of shortcodes or ajax
            if( is_array($this->params[$key]) ) {
                foreach( $this->params[$key] as $pk => $pv ) {
                    $this->params[$key][$pk] = trim($pv);
                }
            }            

            $this->props['data'][$key]  = is_array($this->params[$key]) ? esc_attr(implode(',', $this->params[$key])) : esc_attr($this->params[$key]);   

        }

        $this->props['fields']          = $this->fields;
        $this->props['reviews']         = [];

        /**
         * Get our object cache
         */
        if( $this->params['load'] ) {

            $unique = ''; 

            foreach( ['categories', 'reviews', 'tags'] as $metric ) {
                if( $this->params[$metric] && is_array($this->params[$metric]) ) {
                    $unique .= '_' . substr($metric, 0, 1) . implode('_', $this->params[$metric]);
                }           
            }

            foreach( ['attributes', 'properties', 'groups'] as $metric ) {
                if( $this->params[$metric] && is_array($this->params[$metric]) ) {
                    $unique .= '_' . substr($metric, 0, 1) . implode('_', $this->params[$metric]);
                }           
            }            
            
            if( $this->params['query'] ) {
                $unique .= '_q' . serialize($this->params['query']);
            }
    
            $cache_key  = 'wfr_tables_fields_' . md5( sanitize_key($unique) );
            $cache      = wp_cache_get($cache_key);
    
            // We have our data cached
            if( $cache ) {
                $this->props['reviews'] = (array) $cache['reviews'];
                $this->props['fields']  = (array) $cache['fields'];
                return;
            }

        }        

        /**
         * Load the reviews for which we want to load the fields
         */
        $this->set_reviews();
        $this->props['reviews'] = $this->params['reviews'];

        if( ! $this->params['load'] ) {
            return;
        }        

        /**
         * Set our fields, function inherited from the parent
         */
        foreach( $this->params['reviews'] as $review => $properties ) {
            $this->set_custom_fields( $review );  
        }

        $this->props['fields'] = $this->fields;

        /**
         * Set our cache
         */
        if( $this->params['load'] ) {
            wp_cache_set( $cache_key, ['reviews' => $this->props['reviews'], 'fields' => $this->props['fields']] );
        }    
    
    }
    
    /**
     * Gets our reviews based upon table parameters
     */
    private function set_reviews() {

        if( ! $this->params['query'] ) {
            $this->params['query'] = ['fields' => 'ids', 'post_type' => 'reviews', 'posts_per_page' => -1, 'status' => 'publish']; 
        }

        if( $this->params['categories'] && is_array($this->params['categories']) ) {
            $this->params['query']['tax_query'][] = ['taxonomy' => 'reviews_category', 'terms' => $this->params['categories']];
        }

        if( $this->params['tags'] && is_array($this->params['tags']) ) {
            $this->params['query']['tax_query'][] = ['taxonomy' => 'reviews_tag', 'terms' => $this->params['tags']];
        } 
        
        // Query or retrieve
        $reviews = $this->params['reviews'] ? $this->params['reviews'] : get_posts($this->params['query']);
        
        // Rebuilt our array
        $this->params['reviews'] = [];

        if( ! $reviews ) {
            return;
        }
            
        // Set our array of reviews
        foreach( $reviews as $review ) {
            $title  = get_the_title($review);
            
            if( $this->params['form'] ) {
                $logo   = rtrim( get_post_meta($review, 'logo', true), ',' );
                $image  = $logo ? $logo : get_post_thumbnail_id($review); 
            }

            $this->params['reviews'][$review] = [
                'image' => $this->params['form'] ? wp_get_attachment_image($image, 'thumbnail', false, ['alt' => esc_attr($title)]) : '',
                'link'  => esc_url( get_the_permalink($review) ),
                'title' => esc_html($title)
            ];

            // Adds a price button
            if( $this->params['price'] ) {

                $prices = new Prices([
                    'button'        => true, 
                    'button_label'   => $this->params['price'], 
                    'id'            => $review,
                    'names'         => false,
                    'schema'        => false, 
                    'single'        => true
                ]);
                   
                $this->params['reviews'][$review]['price'] = $prices->render(false);

            }
        }
        
    
    }

    
    /**
     * Gets the values for our custom fields and transforms them into an array with labels,
     * so they can be used by components
     * 
     * @param   Float   $post The id for the given post
     * 
     * @return void
     */
    protected function set_custom_fields( $post ) {

        // $post Should be set and an integer
        if( ! is_int($post) ) {
            return;
        } 

        // Default group labels
        $labels = ['general' => __('General', 'wfr'), 'properties' => __('Properties', 'wfr')];
        
        // Define our groups if they are not set by default
        if( ! $this->params['groups'] ) {
            $groups = ['general', 'properties']; 
        }

        if( isset($this->options['rating_criteria']) && $this->options['rating_criteria'] ) { 
            foreach( $this->options['rating_criteria'] as $criteria ) {
                $key                        = isset($criteria['key']) && $criteria['key'] ? sanitize_key($criteria['key']) : sanitize_key($criteria['name']);
                $labels[$key]               = sanitize_text_field($criteria['name']);
                if( ! $this->params['groups'] ) {
                    $groups[]   = $key;
                }
                
            }
        }

        if( $this->params['groups'] ) {
            $groups = $this->params['groups'];
        }

        /**
         * Get our default price, price unit and price currency
         */
        $priceCurrency  = get_post_meta($post, 'price_currency', true);
        $priceCurrency  = $priceCurrency ? $priceCurrency : $this->options['review_currency'];
        $priceUnit      = get_post_meta($post, 'price_unit', true);
        $price          = get_post_meta($post, 'price', true);   
        $plans          = get_post_meta($post, 'plans', true);

        foreach( $groups as $group ) {

            if( ! isset($this->fields[$group]) ) {
                $this->fields[$group] = [
                    'label'     => isset($labels[$group]) ? $labels[$group] : '',
                    'fields'    => []    
                ];
            }

            /**
             * General groups
             */
            if( $group == 'general' ) {

                $fields = ['price' => __('Price', 'wfr'), 'rating' => __('Overall Rating', 'wfr')];
                
                if( isset($this->options['rating_criteria']) && $this->options['rating_criteria'] ) { 
                    foreach( $this->options['rating_criteria'] as $criteria ) {
                        $key            = isset($criteria['key']) && $criteria['key'] ? sanitize_key($criteria['key']) : sanitize_key($criteria['name']);
                        $fields[$key]   = sanitize_text_field($criteria['name']);
                    }
                }

                // Loop through our general fields
                foreach( $fields as $fkey => $flabel ) {

                    if( ! isset($this->fields[$group]['fields'][$fkey]) ) {
                        $this->fields[$group]['fields'][$fkey] = [
                            'label'     => esc_html($flabel), 
                            'values'    => []
                        ];
                    }
                    
                    if( $fkey == 'price' ) {

                        $prices = new Prices([
                            'button'        => true, 
                            'button_label'   => $this->params['price'], 
                            'id'            => $post,
                            'names'         => false, 
                            'schema'        => false,
                            'single'        => true
                        ]);

                        $this->fields[$group]['fields'][$fkey]['values'][$post] = $prices->render(false); 

                    } else {

                        $rating = new Rating([
                            'criteria'      => $fkey == 'rating' ? [] : [$fkey],
                            'id'            => $post,
                            'names'         => false, 
                            'schema'        => false,
                            'source'        => $fkey == 'rating' ? ['overall'] : ['criteria']
                        ]);

                        $this->fields[$group]['fields'][$fkey]['values'][$post] = $rating->render(false);

                    }

                }


            /**
             * Dynamic groups (properties and criteria)
             */    
            } else {

                $gkey = $group == 'properties' ? $group : $group . '_attributes';

                // This group doesn't have any options yet
                if( ! isset($this->options[$gkey]) || ! array_filter($this->options[$gkey]) ) {
                    continue;
                }
                
                foreach( $this->options[$gkey] as $field ) {

                    $ftype  = $group == 'properties' ? '_property' : '_' . $group . '_attribute'; 
                    $fkey   = isset($field['key']) && $field['key'] ? sanitize_key($field['key']) : sanitize_key($field['name']) . $ftype;

                    // Skip this field if we have custom properties defined
                    if( $gkey == 'properties' && $this->params['properties'] && ! in_array($fkey, $this->params['properties']) ) {
                        continue;
                    }

                    // Skip this field if we have custom criteria attributes defined
                    if( strpos($gkey, '_attributes') && $this->params['attributes'] && ! in_array($fkey, $this->params['attributes']) ) {
                        continue;
                    }
                    
                    $meta   = get_post_meta($post, $fkey, true);

                    // This is a repeatable field with multiple items, linked to a plan
                    if( $field['repeat'] && is_array($meta) ) {
                        $value      = [];
                        foreach( $meta as $planValueSet ) {

                            $planPrice  = $planValueSet['price'];
                            $planName   = $planValueSet['name'];

                            // Retrieve the correct plan name and price from our selection of plans
                            if( $plans ) {
                                foreach( $plans as $plan ) {
                                    if( sanitize_key($plan['name']) == $planValueSet['plan'] ) { 
                                        $planPrice  = $planPrice ? $planPrice : $plan['price'];
                                        $planName   = $planName ? $planName : $plan['name'];
                                        break;
                                    }
                                }
                            }

                            $planValues = $this->get_field_values( $field, $planValueSet['value'], $planPrice );
    
                            // Only add plans if there is a value
                            if( $planValues ) {
                                $planPrice  = $planPrice ? ' <span class="wfr-fields-price">(' . esc_html($priceCurrency) . esc_html($planPrice) . ' ' . esc_html($priceUnit) . ')</span>' : '';
                                $planName   = $planName ? ' <span class="wfr-fields-plan"> - ' . esc_html($planName) . '</span>' : '';
                                $value[]    = '<span class="wfr-fields-repeatable">' . $planValues . $planName . $planPrice . '</span>';
                            }
    
                        }                        
                    } else {
                        $value = $this->get_field_values( $field, $meta, $price );
                    }

                    // Repreatable fields are an array, and are seperated by a line break
                    if( is_array($value) ) {
                        $value = implode( '', array_filter($value) );
                    }
                    
                    if( $value ) {

                        if( ! isset($this->fields[$group]['fields'][$fkey]) ) {
                            $this->fields[$group]['fields'][$fkey] = [
                                'label'     => esc_html($field['name']), 
                                'values'    => []
                            ];
                        }

                        $this->fields[$group]['fields'][$fkey]['values'][$post] = $value;

                    }

                }

            }

            /**
             * If we don't have any fields for all the groups, unset it
             */
            if( ! $this->fields[$group]['fields'] ) {
                unset($this->fields[$group]);
            }

        }

    } 
    
    /**
     * Retrieves the values of a given field
     *
     * @param array     $field      The field attributes of a saved field
     * @param mixed     $meta       The saved metavalues for a given field
     * @param mixed     $price      The price for the given field
     * 
     * 
     * @return string   $value      The formatted value
     */
    private function get_field_values( $field, $meta, $price = false ) {
        
        switch( $field['type'] ) {
            case 'input':
            case 'number':
            case 'textarea':
                $value = esc_html($meta);

                if( $this->params['weight'] && is_numeric($value) && is_numeric($price) && $field['weighted'] ) {
                    $value .= '<span class="wfr-fields-weighted"> / ' . round(floatval($value/$price), 2) . ' ' . __('(weighted)', 'wfr') . '</span>';
                }

                break;
            case 'checkbox':
            case 'select':
                $value  = '';
                $values = array_filter( explode(',', $field['values']) );
                
                foreach( $values as $choice ) {

                    $identifier     = sanitize_key($choice);

                    // If a key value is seperated through a colon, we have to find the label and the value
                    if( strpos($choice, ':') ) {
                        $divide     = explode(':', $choice);
                        $identifier = sanitize_text_field($divide[0]);
                        $choice     = $divide[1];
                    }
                    
                    // If multiple options are checked
                    if( is_array($meta) && $meta[$identifier] == true ) {

                        if( ! $value ) {
                            $value  = [];
                        }

                        $value[]    = esc_html($choice);

                    // If it is just a single option, being checked
                    } elseif( $identifier == $meta ) {
                        $value      = esc_html($choice);    
                    }
                } 

                // Array values are comma seperated
                if( is_array($value) ) {
                    $value = esc_html( implode( ', ', $value ) );
                }

                break;
            default:
                $value = '';
        }

        return $value;

    }    

}