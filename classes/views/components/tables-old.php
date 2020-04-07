<?php
/**
 * Displays the properties of multiple reviews within a table.
 */
namespace Waterfall_Reviews\Views\Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Tables extends Component {

    /**
     * Initialize our component parameters
     */
    protected function initialize() {

        $this->params = wp_parse_args( $this->params, [
            'attributes'    => [],                                              // Limits the display only to the given criteria attributes (use attribute meta keys) @todo
            'categories'    => [],                                              // Only displays data from these review category ids
            'groups'        => [],                                              // Limits the display only to certain groups of criteria (for criteria, use sanitized criteria keys)
            'include'       => [],                                              // If valid, only includes these posts ids in the query 
            'form'          => true,                                            // Whether to display a selection form or not
            'label'         => __('Select items to compare', 'wfr'),            // Label that is used for selecting reviews
            'load'          => false,                                           // Loads the table directly if set to true. If categories, include or tags are not defined, loads all reviews.
            'properties'    => [],                                              // Limits the display only to the given properties (use property meta keys)
            'tags'          => [],                                              // Only displays data from these review tag ids 
            'weight'        => false                                            // Adds weighted data
        ] );

        $this->template = 'tables'; 

        if( ! wp_script_is('wfr-tables') ) {
            wp_enqueue_script('wfr-tables');
        }

    }

    /**
     * Setups our initial data - displaying the dropdown selector. This formats the data
     * 
     * @return void
     */
    protected function query() {

        global $post;

        $this->props['category']        = is_array($this->params['categories']) ? implode(',', $this->params['categories']) : '';
        $this->props['include']         = is_array($this->params['include']) ? implode(',', $this->params['include']) : '';
        $this->props['form']            = $this->params['form'];
        $this->props['label']           = $this->params['label'];
        $this->props['reviews']         = [];
        $this->props['tag']             = is_array($this->params['tags']) ? implode(',', $this->params['tags']) : '';
        $this->props['values']          = [];
        $this->props['weight']          = $this->params['weight'];
        $this->props['weighted']        = $this->params['weighted'];

        /**
         * We'll be loading reviews if we show our review selection form
         */
        if( $this->params['form'] ) {
            $this->getReviews();
        }

        /**
         * General fields
         */
        $this->props['groups']['general'] = [
            'label'     => __('General', 'wfr'),
            'fields'    => [
                'price'         => ['label' => esc_html( __('Price', 'wfr') )],
                'rating'        => ['label' => esc_html( __('Overall Rating', 'wfr') ), 'type' => 'number']
            ]
        ];

        /**
         * Properties
         */
        if( $this->options['properties'] && isset($this->options['properties'][0]['name']) && $this->options['properties'][0]['name'] ) {

            $this->props['groups']['properties'] = [
                'label'    => __('Properties', 'wfr'),
                'fields'   => []
            ];            

            foreach( $this->options['properties'] as $property ) {
        
                // Name and type should be defined
                if( ! $property['name'] && ! $property['type'] ) {
                    continue;
                }

                $key = $property['key'] ? sanitize_key($property['key']) : sanitize_key($property['name']) . '_property';
                $this->props['groups']['properties']['fields'][$key] = ['label' => esc_html($property['name']), 'type' => $property['type']];

            }

            // Remove if we don't have any properties
            if( ! $this->props['groups']['properties']['fields'] ) {
                unset($this->props['groups']['properties']);    
            }

        }

        // Additional rating criteria
        if( $this->options['rating_criteria'] ) {

            foreach( $this->options['rating_criteria'] as $criteria ) {

                if( ! $criteria['name'] ) {
                    continue;
                }                
                
                $key = isset($criteria['key']) && $criteria['key'] ? sanitize_key($criteria['key']) : sanitize_key($criteria['name']);
                
                // Criteria Rating
                $this->props['groups']['general']['fields'][$key . '_rating'] = ['label' => esc_html($criteria['name']), 'type' => 'number'];

                // Rating attributes
                if( $this->options[$key . '_attributes']  && isset($this->options[$key . '_attributes'][0]['name']) && $this->options[$key . '_attributes'][0]['name']) {
                    
                    $this->props['groups'][$key . '_attributes'] = [
                        'label'    => esc_html($criteria['name']),
                        'fields'   => []
                    ]; 

                    foreach( $this->options[$key . '_attributes'] as $attribute ) {

                        // Name and type should be defined
                        if( ! $attribute['name'] && ! $attribute['type'] ) {
                            continue;
                        }

                        $unique = $attribute['key'] ? sanitize_key($attribute['key']) : sanitize_key($attribute['name']) . '_' . $key . '_attribute';
                        $this->props['groups'][$key . '_attributes']['fields'][$unique] = ['label' => esc_html($attribute['name']), 'type' => $attribute['type']];

                    }

                }

            }

        }

        /**
         * Only allow certain groups from the selector
         */
        if( $this->params['groups'] ) {
            foreach( $this->props['groups'] as $key => $group ) {
                $group = str_replace('_attributes', '', $key);
                if( ! in_array($group, $this->params['groups']) ) {
                    unset($this->props['groups'][$key]);
                }
            }
        }

        /**
         * Directly loads all table data
         */
        if( $this->params['load'] == true ) {
            $this->getTableData();
        } 
    
    }

    /**
     * Loads chartdata given the set of parameters
     * 
     * @return void 
     */
    public function getTableData() {

        /**
         * Look if we have a value stored to the object cache
         */
        $unique = 'reviews';

        foreach( ['categories', 'include', 'tags'] as $metric ) {
            if( $this->params[$metric] && is_array($this->params[$metric]) ) {
                $unique .= '_' . substr($metric, 0, 1) . implode(',', $this->params[$metric]);
            }           
        }       

        $cache_key  = 'wfr_tables_data_' . sanitize_key($unique);
        $cache      = wp_cache_get($cache_key);

        if( $cache ) {
            $this->props['reviews'] = (array) $cache['reviews'];
            $this->props['values']  = (array) $cache['values'];
            return;
        }

        /**
         * Retrieve our reviews
         */
        if( ! $this->props['reviews'] ) {
            $this->getReviews();
        }

        // There is nothing added
        if( ! $this->props['reviews'] ) {
            return;
        }

        // Retrieve the specific data per review
        foreach( $this->props['reviews'] as $review => $properties ) {

            /**
             * Get price data, so we can add this to the plan description
             * and eventually add weighted data. A bit ugly now, but it works.
             */
            $price     = get_post_meta($review, 'price', true);
            $currency  = get_post_meta($review, 'price_currency', true);
            $unit      = get_post_meta($review, 'price_unit', true);  
            
            // This will be a huge query, hence we cache the data
            foreach( $this->props['groups'] as $group ) {

                foreach( $group['fields'] as $key => $field ) {
                    $meta = get_post_meta( $review, $key, true );

                    if( is_array($meta) && isset($meta[0]['name']) && isset($meta[0]['value']) ) {

                        foreach( $meta as $plan ) {
                            $planCurrency   = $currency ? $currency : $this->options['review_currency'];
                            $planUnit       = $unit ? ' ' . $unit : '';
                            $planDetails    = isset($plan['price']) && $plan['price'] ? ' (' . $planCurrency . $plan['price'] . $planUnit  . ')' : '';

                            $this->props['values'][$review][$key][] = [
                                'plan'  => $plan['name'] . $planDetails, 
                                'value' => $this->formatData($plan['value'], $plan['price'])
                            ];
                        }
        
                    } else {
                        $this->props['values'][$review][$key] = $this->formatData($meta, $price); 
                    }                    

                }

            }

        }

        wp_cache_set( $cache_key, ['reviews' => $this->props['reviews'], 'values' => $this->props['values']] );

    }

    /**
     * Formats our values and labels for the given chart
     * 
     * @param   Float       $value The numerical input value
     * @param   Float       $price The price associated with the value
     * 
     * @return  Array       $data The formatted values
     */
    private function formatData( $value, $price ) {

        $data       = ['normal' => false, 'weighted' => false];
       
        $weighted    = is_numeric($price) && is_numeric($value) ? $value/$price : '';

        if( is_array($value) && $value ) {
            $data['normal']   = esc_html( implode(', ', $value) );
        } elseif( $value ) {
            $data['normal']   = esc_html($value);
        }

        if( is_numeric($weighted) && $this->params['weight'] ) {
            $data['weighted'] = floatval($weighted);
        }

        return $data;

    }

    /**
     * Gets our reviews based upon table parameters
     */
    private function getReviews() {

        $args = ['fields' => 'ids', 'post_type' => 'reviews', 'posts_per_page' => -1, 'status' => 'publish'];

        if( $this->params['include'] && is_array($this->params['include']) ) {
            $args['post__in'] = $this->params['include'];
        }  

        if( $this->params['categories'] && is_array($this->params['categories']) ) {
            $args['tax_query'][] = ['taxonomy' => 'reviews_category', 'terms' => $this->params['categories']];
        }

        if( $this->params['tags'] && is_array($this->params['tags']) ) {
            $args['tax_query'][] = ['taxonomy' => 'reviews_tag', 'terms' => $this->params['tags']];
        } 
        
        $reviews = get_posts($args);

        if( ! $reviews ) {
            return;
        }
            
        foreach( $reviews as $review ) {
            $title = get_the_title($review);
            $this->props['reviews'][$review] = [
                'image' => $this->params['form'] ? get_the_post_thumbnail($review, 'thumbnail', ['alt' => esc_attr($title)]) : '',
                'title' => esc_html($title)
            ];
        }
        
    
    }

}
