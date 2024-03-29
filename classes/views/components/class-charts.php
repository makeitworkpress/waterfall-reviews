<?php
/**
 * Displays numerical data from reviews within a chart
 */
namespace Waterfall_Reviews\Views\Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Charts extends Component {

    /**
     * Holds a list of metra attributes and their names 
     * 
     * @access private
     */
    private $meta = [];

    /**
     * Holds a list of attribute ids that are weighted
     * 
     * @access private
     */
    private $weighted = [];    

    /**
     * Initialize our component parameters
     */
    protected function initialize() {

        $this->params = wp_parse_args( $this->params, [
            'categories' => [],                                              // Only displays data from these review category ids
            'form'       => true,                                            // Whether to display a selection form or not
            'groups'     => [],                                              // Limits the display only to certain groups of criteria (for criteria, use sanitized criteria keys)
            'id'         => 'wfrChartData',                                  // The default id for the chart
            'include'    => [],                                              // If valid, only includes these posts ids in the query 
            'label'      => __('Select data to compare in a chart', 'wfr'),  // Label that is used for selecting charts
            'load'       => false,                                           // Loads the selected chart by default if set to true
            'meta'       => '',                                              // Indicates the metafield that is used as a source of loading and ordering the chart data 
            'normal'     => __('Normal Values', 'wfr'),                      // The button for normal chart loading
            'select'     => __('Select data', 'wfr'),                        // Label that is used for the default option             
            'tags'       => [],                                              // Only displays data from these review tag ids 
            'weighted'   => __('Price Weighted Values'),                     // The button for weighted chart loading
            'weight'     => false                                            // Allows to load weighted charts for the given attribute by showing the weighted button
        ] );

        if( ! wp_script_is('wfr-chart') ) {
            wp_enqueue_script('wfr-chart');
        }

    }

    /**
     * Setups our initial data - displaying the dropdown selector. This formats the data
     */
    protected function query() {

        global $post;

        foreach( ['categories', 'form', 'id', 'include', 'meta', 'normal', 'label', 'select', 'tags', 'weight', 'weighted'] as $param ) {
            if( in_array($param, ['categories', 'include', 'tags']) ) {
                $this->props[$param] = is_array($this->params[$param]) ? implode(',', $this->params[$param]) : '';
            } else {
                $this->props[$param] = $this->params[$param];
            }
        }

        $this->props['class']           = $this->params['load'] ? ' wfr-charts-loaded' : '';

        /**
         * Start building our field selectors for the dropdown field
         */

        // General information
        $this->props['selector_groups']['general'] = [
            'label'     => __('General', 'wfr'),
            'options'   => [
                'price'         => __('Price', 'wfr'),
            ]
        ];

        // Save our labels
        $this->meta['price']        = __('Price', 'wfr');

        // Rating
        $this->props['selector_groups']['rating'] = [
            'label'     => __('Rating', 'wfr'),
            'options'   => [
                'rating' => __('Overall Rating', 'wfr')
            ]
        ];

        // Save our labels
        $this->meta['rating']       = __('Overall Rating', 'wfr');      

        // Properties
        if( isset($this->options['properties']) && $this->options['properties'] && isset($this->options['properties'][0]['name']) && $this->options['properties'][0]['name'] ) {

            $this->props['selector_groups']['properties'] = [
                'label'     => __('Properties', 'wfr'),
                'options'   => []
            ];            

            foreach( $this->options['properties'] as $property ) {
        
                // Name and type should be defined
                if( ! $property['name'] && ! $property['type'] ) {
                    continue;
                }

                // Only numerical properties are supported
                if(  $property['type'] != 'number' ) {
                    continue;
                }

                $key = isset($property['key']) && $property['key'] ? sanitize_key($property['key']) : sanitize_key($property['name']) . '_property';
                $this->props['selector_groups']['properties']['options'][$key]   = esc_html($property['name']);
                $this->meta[$key]                                               = $property['name'];

                if( $property['weighted'] ) {
                    $this->weighted[] = $key;    
                }

            }

            // Remove if we don't have any numerical properties
            if( ! $this->props['selector_groups']['properties']['options'] ) {
                unset($this->props['selector_groups']['properties']);    
            }

        }

        // Additional rating criteria
        if( isset($this->options['rating_criteria']) && $this->options['rating_criteria'] ) {

            foreach( $this->options['rating_criteria'] as $criteria ) {

                if( ! $criteria['name'] ) {
                    continue;
                }                
                
                $key = isset($criteria['key'] ) && $criteria['key'] ? sanitize_key($criteria['key']) : sanitize_key($criteria['name']);
                
                // Criteria Rating
                $this->props['selector_groups']['rating']['options'][$key . '_rating'] = esc_html($criteria['name']);
                $this->meta[$key . '_rating']                                         = $criteria['name'];

                // Rating attributes
                if( $this->options[$key . '_attributes']  && isset($this->options[$key . '_attributes'][0]['name']) && $this->options[$key . '_attributes'][0]['name']) {
                    
                    $this->props['selector_groups'][$key . '_attributes'] = [
                        'label'     => esc_html($criteria['name']),
                        'options'   => []
                    ]; 

                    foreach( $this->options[$key . '_attributes'] as $attribute ) {

                        // Name and type should be defined
                        if( ! $attribute['name'] && ! $attribute['type'] ) {
                            continue;
                        }

                        // Only numerical properties are supported
                        if(  $attribute['type'] != 'number' ) {
                            continue;
                        }

                        $unique = $attribute['key'] ? sanitize_key($attribute['key']) : sanitize_key($attribute['name']) . '_' . $key . '_attribute';
                        $this->props['selector_groups'][$key . '_attributes']['options'][$unique]    = esc_html($attribute['name']);
                        $this->meta[$unique]                                                        = $attribute['name'];

                        if( $attribute['weighted'] ) {
                            $this->weighted[] = $unique;    
                        }                       

                    }

                    // Remove if we don't have any numerical properties
                    if( ! $this->props['selector_groups'][$key . '_attributes']['options'] ) {
                        unset($this->props['selector_groups'][$key . '_attributes']);    
                    }                    

                }

            }

        }

        /**
         * Only allow certain groups from the selector
         */
        if( $this->params['groups'] ) {
            foreach( $this->props['selector_groups'] as $key => $group ) {
                $group = str_replace('_attributes', '', $key);
                if( ! in_array($group, $this->params['groups']) ) {
                    unset($this->props['selector_groups'][$key]);
                }
            }
        }

        /**
         * Directly loads all chart data
         */
        if( $this->params['load'] == true ) {
            $this->get_chart_data();
        }
    
    }

    /**
     * Loads chartdata given the set of parameters
     * 
     * @return array $data The data for displaying charts
     */
    public function get_chart_data() {

        $metakey = sanitize_key($this->params['meta']);

        // Default data structure
        $data = [
            'normal'    => [
                'dataSet'   => [                
                    'data'          => [],
                    'label'         => isset($this->meta[$metakey]) ? $this->meta[$metakey] : ''
                ],
                'labels'   => [],
            ],
            'weighted'  => [
                'dataSet'   => [                
                    'data'          => [],
                    'label'         => isset($this->meta[$metakey]) ? $this->meta[$metakey] : ''
                ],
                'labels'   => [],
            ]
        ];


        /**
         * There is no meta key defined.
         */
        if( ! $this->params['meta'] ) {
            return $data;
        }

        /**
         * Let's draft up a unique cache key based on some variables
         */
        $unique = $metakey; 

        foreach( ['categories', 'include', 'tags'] as $metric ) {
            if( $this->params[$metric] && is_array($this->params[$metric]) ) {
                $unique .= '_' . substr($metric, 0, 1) . implode('_', $this->params[$metric]);
            }           
        }

        if( $this->params['weight'] ) {
            $unique .= '_weighted';
        }  

        /**
         * Look if we have a value stored to the object cache
         */
        $cache_key  = 'wfr_chart_data_' . md5( sanitize_key($unique) );
        $cache      = wp_cache_get($cache_key);

        if( $cache ) {
            return $cache;
        }

        /**
         * Get price data
         */


        /**
         * Retrieve our reviews
         */
        $args = [
            'fields'            => 'ids', 
            'meta_key'          => sanitize_key($this->params['meta']), 
            'orderby'           => 'meta_value_num', 
            'posts_per_page'    => -1, 
            'post_type'         => 'reviews',
            'status'            => 'publish'
        ];

        if( $this->params['categories'] && is_array($this->params['categories']) ) {
            $args['tax_query'][] = ['taxonomy' => 'reviews_category', 'terms' => $this->params['categories']];
        }

        if( $this->params['include'] && is_array($this->params['include']) ) {
            $args['post__in'] = $this->params['include'];
        }        

        if( $this->params['tags'] && is_array($this->params['tags']) ) {
            $args['tax_query'][] = ['taxonomy' => 'reviews_tag', 'terms' => $this->params['tags']];
        }        

        $reviews = get_posts($args);

        $metrics = [
            'normal'    => [],
            'weighted'  => []
        ];

        // Return an empty dataset
        if( ! $reviews ) {
            return $data;
        }

        // Retrieve the specific data per review
        foreach( $reviews as $key => $review ) {
            $meta                           = get_post_meta( $review, sanitize_key($this->params['meta']), true );

            /**
             * Get price data, so we can add this to the plan description
             * and eventually add weighted charts. A bit ugly now, but it works.
             */
            $priceCurrency  = get_post_meta($review, 'price_currency', true);
            $priceUnit      = get_post_meta($review, 'price_unit', true);   
            $plans          = get_post_meta($review, 'plans', true);      

            // If a number field is repeatable, thus has multiple values. Often used for various plans in one item
            if( is_array($meta) && isset($meta[0]['name']) && isset($meta[0]['value']) ) {

                foreach( $meta as $planValueSet ) {

                    $planPrice  = $planValueSet['price'];
                    $planName   = $planValueSet['name'];

                    // Retrieve the correct plan name and price from our selection of plans
                    if( is_array($plans) ) {
                        foreach( $plans as $plan ) {
                            if( sanitize_key($plan['name']) == $planValueSet['plan'] ) {
                                $planPrice  = $planPrice ? $planPrice : $plan['price'];
                                $planName   = $planName ? $planName : $plan['name'];
                                break;
                            }
                        }    
                    }               

                    $values = $this->format_data( $planValueSet['value'], $planName, $planPrice, $priceCurrency, $priceUnit ); 
                   
                    if( $values['normal'] ) {
                        $metrics['normal'][] = $values['normal'];
                    }

                    if( $this->params['weight'] && $values['weighted'] && in_array($this->params['meta'], $this->weighted) ) {
                        $metrics['weighted'][] = $values['weighted'];
                    }

                }

            } else {

                $price          = $this->params['meta'] === 'price' ? $meta : get_post_meta($review, 'price', true); 
                $values         = $this->format_data( $meta, get_the_title($review), $price, $priceCurrency, $priceUnit ); 

                if( $values['normal'] ) {
                    $metrics['normal'][] = $values['normal'];
                }

                if( $this->params['weight'] && $values['weighted'] && in_array($this->params['meta'], $this->weighted) ) {
                    $metrics['weighted'][] = $values['weighted'];
                }

            }
        }

        // Sort our metrics on value
        usort( $metrics['normal'], function($a, $b) {
            return $b['value'] <=> $a['value'];
        });

        // Sort weighted values if we have them
        if( $this->params['weight'] ) {
            usort( $metrics['weighted'], function($a, $b) {
                return $b['value'] <=> $a['value'];
            });       
        }

        // Set our chart data
        foreach( $metrics as $context => $values ) {
            foreach( $values as $key => $value ) {
                $data[$context]['labels'][]               = $value['label'];
                $data[$context]['dataSet']['data'][]      = $value['value']; 
            }      
        }

        wp_cache_set( $cache_key, $data );

        // Loads the chart by an instant, thus requiring the values directly.
        if( $this->params['load'] == true ) {
            wp_localize_script( 'wfr-scripts', 'chart' . $this->params['id'], $data );    
        } else {
            return $data;
        }

    }

    /**
     * Formats our values and labels for the given chart
     * 
     * @param   Float       $value The numerical input value
     * @param   String      $label The label associated with the data
     * @param   Float       $price The price associated with the value
     * @param   String      $currency The price currency for the data
     * @param   String      $unit The united for the price
     * 
     * @return  Array       $data The formatted values
     */
    private function format_data( $value, $label, $price, $currency, $unit = '' ) {

        $currency   = $currency ? $currency : $this->options['review_currency'];
        $unit       = $unit ? ' ' . $unit : '';

        $details    = $price ? ' (' . $currency . $price . $unit . ')' : '';
        $data       = ['normal' => false, 'weighted' => false];
       
        $weighted    = is_numeric($price) && is_numeric($value) ? $value/$price : '';

        if( is_numeric($value) ) {
            $data['normal']   = ['label' => $label . $details, 'value' => floatval($value)];
        }

        if( is_numeric($weighted) ) {
            $data['weighted'] = ['label' => $label . $details, 'value' => round(floatval($weighted), 2) ];
        }

        return $data;

    }

}
