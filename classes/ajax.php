<?php
/**
 * This class is a container class helding several ajax powered functions,
 * such as functions for displaying charts and filtering reviews
 */
namespace Waterfall_Reviews;
use Waterfall_Reviews\Base as Base;
use WP_Query as WP_Query;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Ajax extends Base {

    /**
     * Registers our hooks. Is automatically performed upon initialization, as we extend the WFR_Base class
     */
    protected function register() {

        $this->actions = [
            ['wp_ajax_getChartData', 'getChartData'],
            ['wp_ajax_nopriv_getChartData', 'getChartData'],
            ['wp_ajax_loadTables', 'loadTables'],
            ['wp_ajax_nopriv_loadTables', 'loadTables'],            
            ['wp_ajax_filterReviews', 'filterReviews'],
            ['wp_ajax_nopriv_filterReviews', 'filterReviews']
        ];

    }

    /**
     * Retrieves the chart data for a certain meta key
     */
    public function getChartData() {

        wp_verify_nonce( 'we-love-good-reviews', $_POST['action'] );
        
        $args = [
            'categories'    => array_filter( explode(',', sanitize_text_field($_POST['categories'])), function($v) { return is_numeric($v); } ),
            'include'       => array_filter( explode(',', sanitize_text_field($_POST['include'])), function($v) { return is_numeric($v); } ),
            'meta'          => sanitize_key( $_POST['key'] ),
            'tags'          => array_filter( explode(',', sanitize_text_field($_POST['tags'])), function($v) { return is_numeric($v); } ),
            'weight'        => true
        ];

        $chart      = new Views\Components\Charts( $args );
        $data       = $chart->getChartData();

        wp_send_json_success( $data );

    }

    /**
     * Loads comparison tables for the selected reviews
     */
    public function loadTables() {

        wp_verify_nonce( 'we-love-good-reviews', $_POST['action'] );
        
        $args = [
            'attributes'    => isset($_POST['attributes']) ? array_filter( explode(',', sanitize_text_field($_POST['attributes'])) ) : [],
            'categories'    => isset($_POST['categories']) ? array_filter( explode(',', sanitize_text_field($_POST['categories'])), function($v) { return is_numeric($v); } ) : [],
            'form'          => false,
            'groups'        => isset($_POST['groups']) ? array_filter( explode(',', sanitize_text_field($_POST['groups'])) ) : [],
            'load'          => true,
            'price'         => false,
            'properties'    => isset($_POST['properties']) ? array_filter( explode(',', sanitize_text_field($_POST['properties'])) ) : [],
            'reviews'       => array_filter( $_POST['reviews'], function($v) { return is_numeric($v); } ),
            'tags'          => isset($_POST['tags']) ? array_filter( explode(',', sanitize_text_field($_POST['tags'])), function($v) { return is_numeric($v); } ) : [],
            'view'          => sanitize_text_field($_POST['view']),
            'weight'        => sanitize_text_field($_POST['weight'])
        ];

        $tables      = new Views\Components\Tables( $args );

        wp_send_json_success( $tables->render(false) );

    }    

    /**
     * Executes a filter action for use in filters
     */
    public function filterReviews() {

        wp_verify_nonce( 'we-love-good-reviews', $_POST['action'] );

        $args = [
            'ep_integrate'      => true,     // We support elastic search if the plugin is installed
            'fields'            => 'ids',
            'post_status'       => 'publish',
            'posts_per_page'    => isset( $_POST['number'] ) ? intval( $_POST['number'] ) : get_option( 'posts_per_page' ),
            'post_type'         => 'reviews'
        ];

        // Sorting fields
        if( isset($_POST['sort']) && $_POST['sort'] ) {
            $sorts = [
                'price_asc'     => ['order' => 'ASC', 'orderby' => 'meta_value_num', 'meta_key' => 'price', 'meta_type' => 'NUMERIC'],
                'price_desc'    => ['order' => 'DESC', 'orderby' => 'meta_value_num', 'meta_key' => 'price', 'meta_type' => 'NUMERIC'],
                'rating_asc'    => ['order' => 'ASC', 'orderby' => 'meta_value_num', 'meta_key' => 'rating', 'meta_type' => 'NUMERIC'],
                'rating_desc'   => ['order' => 'DESC', 'orderby' => 'meta_value_num', 'meta_key' => 'rating', 'meta_type' => 'NUMERIC'],               
                'title_asc'     => ['order' => 'ASC', 'orderby' => 'title'],
                'title_desc'    => ['order' => 'DESC', 'orderby' => 'title'],  
                'value_asc'     => [
                    'meta_query'    => [
                        'relation'  => 'AND', 
                        'price'     => ['compare' => 'EXISTS', 'key' => 'price', 'type' => 'NUMERIC'],
                        'rating'    => ['compare' => 'EXISTS', 'key' => 'rating', 'type' => 'NUMERIC']
                    ],
                    'orderby'       => ['price' => 'DESC', 'rating' => 'ASC']
                ],
                'value_desc'    => [
                    'meta_query'    => [
                        'relation'  => 'AND', 
                        'price'     => ['compare' => 'EXISTS', 'key' => 'price', 'type' => 'NUMERIC'],
                        'rating'    => ['compare' => 'EXISTS', 'key' => 'rating', 'type' => 'NUMERIC']
                    ],                    
                    'orderby' => ['price' => 'ASC', 'rating' => 'DESC']
                ], 
                'visitor_rating_asc'    => ['order' => 'ASC', 'orderby' => 'meta_value_num', 'meta_key' => 'visitors_rating', 'meta_type' => 'NUMERIC'],                                     
                'visitor_rating_desc'   => ['order' => 'DESC', 'orderby' => 'meta_value_num', 'meta_key' => 'visitors_rating', 'meta_type' => 'NUMERIC']                                     
            ];

            foreach( $sorts as $sort => $properties ) {
                if( $_POST['sort'] == $sort ) {
                    $args = wp_parse_args( $properties, $args );
                }
            }
            
        } 
        
        if( isset($_POST['page']) ) {
            $args['paged'] = intval($_POST['page']);
        }         

        // Our minimum and maximum prices
        if( isset($_POST['price_min']) && $_POST['price_min'] ) {
            $args['meta_query']['prices']['compare']  = '>=';
            $args['meta_query']['prices']['key']      = 'price';
            $args['meta_query']['prices']['type']     = 'NUMERIC';
            $args['meta_query']['prices']['value']    = intval($_POST['price_min']);
        }

        if( isset($_POST['price_max']) && $_POST['price_max'] ) {
            $args['meta_query']['prices']['compare']  = '<=';
            $args['meta_query']['prices']['key']      = 'price';
            $args['meta_query']['prices']['type']     = 'NUMERIC';
            $args['meta_query']['prices']['value']    = intval($_POST['price_max']);
        }        

        if( isset($_POST['price_min']) && $_POST['price_min'] && isset($_POST['price_max']) && $_POST['price_max'] ) {
            $args['meta_query']['prices']['compare']  = 'BETWEEN';
            $args['meta_query']['prices']['key']      = 'price';
            $args['meta_query']['prices']['type']     = 'NUMERIC';
            $args['meta_query']['prices']['value']    = [intval($_POST['price_min']), intval($_POST['price_max'])];
        }

        // Manual Rating
        if( isset($_POST['rating']) && $_POST['rating'] ) {

            $options = wf_get_theme_option();
            $compare = $options['rating_maximum'] == $_POST['rating'] ? '=' : '>=';

            $args['meta_query'][] = [
                'compare' => $compare,
                'key'     => 'rating',
                'type'    => 'NUMERIC',
                'value'   => intval($_POST['rating'])
            ];
        }

        // SManual earch fields
        if( isset($_POST['search']) && $_POST['search'] ) {
            $args['s'] = sanitize_text_field($_POST['search']);
            $args['search_fields'] = [ 
                'meta'          => ['advantages', 'disadvantages', 'reviewed_item', 'summary'], 
                'post_title', 
                'post_content', 
                'post_excerpt',                
                'taxonomies'    => ['reviews_category']
            ];            
        } 
        
        // Taxonomy term
        if( isset($_POST['term']) && $_POST['term'] ) {
            $args['tax_query'][] = [
                'taxonomy'  => 'reviews_category',
                'terms'     => [intval($_POST['term'])]
            ];            
        }

        // Do our query
        $posts   = get_posts( wp_parse_args(['posts_per_page' => -1], $args) );
        $reviews = new Views\Components\Reviews(['attributes' => ['class' => 'archive-posts wfr-reviews-component'], 'queryArgs' => $args]);

        // Send our filtered results
        wp_send_json_success( ['html' => $reviews->render(false), 'posts' => $posts] );
        
    }

}