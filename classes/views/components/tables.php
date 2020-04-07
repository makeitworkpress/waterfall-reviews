<?php
/**
 * Displays the properties of a single review, including possible criteria properties
 */
namespace Waterfall_Reviews\Views\Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Tables extends Component {

    /**
     * Initializes our component
     */
    protected function initialize() {

        $this->params   = wp_parse_args( $this->params, [
            'attributes'    => [],      // Limits the display only to the given criteria attributes (use attribute meta keys)
            'form'          => false,   // Displays a review selection form (or not)
            'groups'        => [],      // Only show the content from the given groups
            'label'         => __('Select items to compare', 'wfr'), // Label that is used for selecting reviews
            'load'          => false,   // Loads the table directly if set to true. Loads all reviews if a query is not defined.
            'properties'    => [],      // Limits the display only to the given properties (use property meta keys)            
            'query'         => [],      // Arguments to query posts by
            'reviews'       => [],      // Default reviews to load
            'view'          => 'tabs',  // We either show 'tabs' or a complete 'table'
            'weighted'      => false,
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

        $this->props['class'] = $this->params['view'];

        // if( $this->params['criteria'] == true ) {

        //     if( isset($this->options['rating_criteria']) && $this->options['rating_criteria'] ) { 
        //         foreach( $this->options['rating_criteria'] as $criteria ) {
        //             $key                        = isset($criteria['key']) && $criteria['key'] ? sanitize_key($criteria['key']) : sanitize_key($criteria['name']);
        //             $this->params['groups'][]    = $key;            
        //         }
        //     }

        // }        

        // if( $this->params['properties'] == true ) {
        //     $this->params['groups'][] = 'properties';
        // }

        // Load the reviews for which we want to load the fields
        if( ! $this->params['reviews'] ) {
            $this->setReviews();
            $this->props['reviews'] = $this->params['reviews'];
        }

        // Set our fields, function inherited from the parent
        foreach( $this->params['reviews'] as $review ) {
            $this->setCustomFields( $review, $this->params['weighted'], $this->params['groups']);  
        }
        
        
    
    }
    
    /**
     * Gets our reviews based upon table parameters
     */
    private function setReviews() {

        if( ! $this->params['query'] ) {
            $this->params['query'] = ['fields' => 'ids', 'post_type' => 'reviews', 'posts_per_page' => -1, 'status' => 'publish']; 
        }

        // if( $this->params['include'] && is_array($this->params['include']) ) {
        //     $args['post__in'] = $this->params['include'];
        // }  

        // if( $this->params['categories'] && is_array($this->params['categories']) ) {
        //     $args['tax_query'][] = ['taxonomy' => 'reviews_category', 'terms' => $this->params['categories']];
        // }

        // if( $this->params['tags'] && is_array($this->params['tags']) ) {
        //     $args['tax_query'][] = ['taxonomy' => 'reviews_tag', 'terms' => $this->params['tags']];
        // } 
        
        $reviews = get_posts($this->params['query']);

        if( ! $reviews ) {
            return;
        }
            
        foreach( $reviews as $review ) {
            $title = get_the_title($review);
            $this->params['reviews'][$review] = [
                'image' => $this->params['form'] ? get_the_post_thumbnail($review, 'thumbnail', ['alt' => esc_attr($title)]) : '',
                'title' => esc_html($title)
            ];
        }
        
    
    }    


}