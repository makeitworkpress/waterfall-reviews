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
            'categories' => [],                                              // Only displays data from these review categories
            'default'    => __('Select data', 'wfr'),                        // Label that is used for the default option 
            'id'         => 'wfrChartData',                                  // The default id for the chart
            'include'    => [],                                              // If valid, only includes these posts ids in the query 
            'label'      => __('Select data to display in a chart', 'wfr'),  // Label that is used for selecting charts - if set to false, hides the form
            'load'       => false,                                           // Loads the selected chart by default if set to true
            'meta'       => 'rating',                                        // Indicates the metafield that is used as a source of loading and ordering the chart data 
            'tags'       => [],                                              // Only displays data from these review tags 
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

        $this->props['category']    = implode(',', $this->params['categories']); 
        $this->props['default']     = $this->params['default'];
        $this->props['id']          = $this->params['id'];
        $this->props['selector']    = $this->params['label'];
        $this->props['tag']         = implode(',', $this->params['tags']);

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
                $this->props['selectorGroups']['properties']['options'][$key . '_property'] = $property['name'];
                $this->meta[$key . '_property']                                             = $property['name'];

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
                        'label'     => sprintf( __('%s Attributes', 'wfr'), $criteria['name']),
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
                        $this->props['selectorGroups'][$key . '_attributes']['options'][$unique . '_' . $key . '_attribute']    = $attribute['name'];
                        $this->meta[$unique . '_' . $key . '_attribute']                                                        = $attribute['name'];

                    }

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

        $args = ['fields' => 'ids', 'meta_key' => $this->params['meta'], 'orderby' => 'meta_value_num', 'posts_per_page' => -1, 'post_type' => 'reviews'];

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
            'dataSet'   => [
                'data'  => [],
                'label' => $this->meta[$this->params['meta']]
            ],
            'labels'    => [],
        ];

        // Return an empty dataset
        if( ! $reviews ) {
            return $data;
        }

        // Retrieve the specific data per review
        foreach( $reviews as $key => $review ) {
            $meta                           = get_post_meta( $review, $this->params['meta'], true );

            // If a number field is repeatable, thus has multiple values. Often used for various plans in one item
            if( is_array($meta) && isset($meta[0]['name']) && isset($meta[0]['value']) ) {

                foreach( $meta as $plan ) {
                    $data['labels'][]               = $plan['name'];
                    $data['dataSet']['data'][]      = $plan['value']; 
                }

            } else {
                $data['labels'][]               = get_the_title( $review );
                $data['dataSet']['data'][]      = $meta; 
            }
        }

        // Loads the chart by an instant, thus requiring the values directly.
        if( $this->params['load'] == true ) {
            wp_localize_script( 'wfr-scripts', 'chart' . $this->params['id'], $data );    
        } else {
            return $data;
        }

    }

}