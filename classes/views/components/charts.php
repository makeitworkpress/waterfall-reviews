<?php
/**
 * Displays the summary of a single review, including advantages and disadvantages
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
     * Initialize our component parameters
     */
    protected function initialize() {

        $this->params = wp_parse_args( $this->params, [
            'categories' => [],                                              // Only displays data from these review category ids
            'default'    => __('Select data', 'wfr'),                        // Label that is used for the default option 
            'id'         => 'wfrChartData',                                  // The default id for the chart
            'include'    => [],                                              // If valid, only includes these posts ids in the query 
            'label'      => __('Select data to display in a chart', 'wfr'),  // Label that is used for selecting charts - if set to false, hides the form
            'load'       => false,                                           // Loads the selected chart by default if set to true
            'meta'       => 'rating',                                        // Indicates the metafield that is used as a source of loading and ordering the chart data 
            'normal'     => __('Normal Values', 'wfr'),                      // The button for normal chart loading
            'tags'       => [],                                              // Only displays data from these review tag ids 
            'groups'     => [],                                              // Limits the display only to certain groups of criteria (for criteria, use sanitized criteria keys)
            'weighted'   => __('Price Weighted Values'),                     // The button for weighted chart loading
            'weight'     => false                                            // Allows to load weighted charts for the given attribute by showing the weighted button
        ] );

        $this->template = 'charts'; 

        if( ! wp_script_is('wfr-chart') ) {
            wp_enqueue_script('wfr-chart');
        }
    }

    /**
     * Setups our initial data - displaying the dropdown selector. This formats the data
     */
    protected function query() {

        global $post;

        $this->props['category']        = is_array($this->params['categories']) ? implode(',', $this->params['categories']) : ''; 
        $this->props['default']         = $this->params['default'];
        $this->props['id']              = $this->params['id'];
        $this->props['selector']        = $this->params['label'];
        $this->props['weight']          = $this->params['weight'];

        if( $this->params['weight'] ) {
            $this->props['normal']      = $this->params['normal'];
            $this->props['weighted']    = $this->params['weighted'];
        }

        $this->props['tag']             = is_array($this->params['tags']) ? implode(',', $this->params['tags']) : '';

        // General information
        $this->props['selectorGroups']['general'] = [
            'label'     => __('General', 'wfr'),
            'options'   => [
                'price'         => __('Price', 'wfr'),
                // 'price_value'   => __('Price/value')
            ]
        ];

        // Save our labels
        $this->meta['price']        = __('Price', 'wfr');
        // $this->meta['price_value']  = __('Price/value');

        // Rating
        $this->props['selectorGroups']['rating'] = [
            'label'     => __('Rating', 'wfr'),
            'options'   => [
                'rating' => __('Overall Rating', 'wfr')
            ]
        ];

        // Save our labels
        $this->meta['rating']       = __('Overall Rating', 'wfr');      

        // Properties
        if( $this->options['properties'] && isset($this->options['properties'][0]['name']) && $this->options['properties'][0]['name'] ) {

            $this->props['selectorGroups']['properties'] = [
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

                $key  = sanitize_key($property['name']);
                $this->props['selectorGroups']['properties']['options'][$key . '_property'] = esc_html($property['name']);
                $this->meta[$key . '_property']                                             = $property['name'];

            }

            // Remove if we don't have any numerical properties
            if( ! $this->props['selectorGroups']['properties']['options'] ) {
                unset($this->props['selectorGroups']['properties']);    
            }

        }

        // Additional rating criteria
        if( $this->options['rating_criteria'] ) {

            foreach( $this->options['rating_criteria'] as $criteria ) {

                if( ! $criteria['name'] ) {
                    continue;
                }                
                
                $key = sanitize_key($criteria['name']);
                
                // Criteria Rating
                $this->props['selectorGroups']['rating']['options'][$key . '_rating'] = $criteria['name'];
                $this->meta[$key . '_rating']                                         = $criteria['name'];

                // Rating attributes
                if( $this->options[$key . '_attributes']  && isset($this->options[$key . '_attributes'][0]['name']) && $this->options[$key . '_attributes'][0]['name']) {
                    
                    $this->props['selectorGroups'][$key . '_attributes'] = [
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

                        $unique = sanitize_key($attribute['name']);
                        $this->props['selectorGroups'][$key . '_attributes']['options'][$unique . '_' . $key . '_attribute']    = esc_html($attribute['name']);
                        $this->meta[$unique . '_' . $key . '_attribute']                                                        = $attribute['name'];

                    }

                }

            }

        }

        /**
         * Only allow certain groups from the selector
         */
        if( $this->params['groups'] ) {
            foreach( $this->props['selectorGroups'] as $key => $group ) {
                $group = str_replace('_attributes', '', $key);
                if( ! in_array($group, $this->params['groups']) ) {
                    unset($this->props['selectorGroups'][$key]);
                }
            }
        }
    
    }

    /**
     * Loads chartdata given the set of parameters
     * 
     * @return array $data The data for displaying charts
     */
    public function getChartData() {

        /**
         * There is no meta key defined.
         * Will result in JS errors.
         */
        if( ! $this->params['meta'] ) {
            return;
        }

        /**
         * Look if we have a value stored to the object cache
         */
        $cache_key  = 'wfr_chart_data_' . sanitize_key($this->params['meta']);
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
        $args = ['fields' => 'ids', 'meta_key' => sanitize_key($this->params['meta']), 'orderby' => 'meta_value_num', 'posts_per_page' => -1, 'post_type' => 'reviews'];

        if( $this->params['categories'] ) {
            $args['tax_query'][] = ['taxonomy' => 'reviews_category', 'terms' => $this->params['categories']];
        }

        if( $this->params['tags'] ) {
            $args['tax_query'][] = ['taxonomy' => 'reviews_tag', 'terms' => $this->params['tags']];
        }        

        if( $this->params['include'] && is_array($this->params['include']) ) {
            $args['post__in'] = $this->params['include'];
        }

        $reviews = get_posts($args);

        $data = [
            'normal'    => [
                'dataSet'   => [                
                    'data'          => [],
                    'label'         => $this->meta[sanitize_key($this->params['meta'])]
                ],
                'labels'   => [],
            ],
            'weighted'  => [
                'dataSet'   => [                
                    'data'          => [],
                    'label'         => $this->meta[sanitize_key($this->params['meta'])]
                ],
                'labels'   => [],
            ]
        ];

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

            // If a number field is repeatable, thus has multiple values. Often used for various plans in one item
            if( is_array($meta) && isset($meta[0]['name']) && isset($meta[0]['value']) ) {

                foreach( $meta as $plan ) {
                    $values = $this->formatData( $plan['value'], $plan['name'], $plan['price'], $priceCurrency, $priceUnit ); 
                   
                    if( $values['normal'] ) {
                        $metrics['normal'][] = $values['normal'];
                    }

                    if( $this->params['weight'] && $values['weighted'] ) {
                        $metrics['weighted'][] = $values['weighted'];
                    }

                }

            } else {

                $price          = $this->params['meta'] === 'price' ? $meta : get_post_meta($review, 'price', true); 
                $values         = $this->formatData( $meta, get_the_title($review), $price, $priceCurrency, $priceUnit ); 

                if( $values['normal'] ) {
                    $metrics['normal'][] = $values['normal'];
                }

                if( $this->params['weight'] && $values['weighted'] && $this->params['meta'] !== 'price' ) {
                    $metrics['weighted'][] = $values['weighted'];
                }

            }
        }

        // Sort our metrics on value
        usort( $metrics['normal'], function($a, $b) {
            return strcmp($b['value'], $a['value']);
        });

        if( $this->params['weight'] ) {
            usort( $metrics['weighted'], function($a, $b) {
                return strcmp($b['value'], $a['value']);
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
    private function formatData( $value, $label, $price, $currency, $unit = '' ) {

        $currency   = $currency ? $currency : $this->options['review_currency'];
        $unit       = $unit ? ' ' . $unit : '';

        $details    = $price ? ' (' . $currency . $price . $unit . ')' : '';
        $data       = ['normal' => false, 'weighted' => false];
       
        $weighted    = is_numeric($price) && is_numeric($value) ? $value/$price : '';

        if( is_numeric($value) ) {
            $data['normal']   = ['label' => $label . $details, 'value' => floatval($value)];
        }

        if( is_numeric($weighted) ) {
            $data['weighted'] = ['label' => $label . $details, 'value' => floatval($weighted)];
        }

        return $data;

    }

}
