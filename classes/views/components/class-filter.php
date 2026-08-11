<?php
/**
 * Displays the summary of a single review, including advantages and disadvantages
 */
namespace Waterfall_Reviews\Views\Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Filter extends Component {

    protected function initialize() {
        $this->params = wp_parse_args( $this->params, [
            'category'          => 0,                       // If a certain category is preloaded within the filter - thus, only results from this category are loaded
            'label'             => __('Filter', 'wfr'),     // The label for the filter button
            'instant'           => true,                    // If the filter is instant (without a button)
            'price'             => true,                    // If a price filter field should be added
            'price_label'       => __('Price', 'wfr'),                                   
            'rating'            => true,                    // If a rating filter field should be added
            'rating_label'      => __('Rating', 'wfr'), 
            'rating_min'        => 1, 
            'search'            => true,                    // If a sort field should be added
            'search_label'      => __('Search for', 'wfr'),                            
            'sort'              => true,                    // If a sort field should be added
            'sort_label'        => __('Sort by', 'wfr'),
            'target'            => 'wfr-reviews-component', // Default target data-id   
            'taxonomy'          => true,                    // If a taxonomy (reviews_category) filter field should be added
            'taxonomy_label'    => __('Category', 'wfr') 
        ] );

        $this->template = 'filter'; 
    }

    /**
     * Define the properties
     */
    protected function query() {

        $this->props                = $this->params;
        $this->props['terms']       = [];

        // Our price
        if( $this->params['price'] ) {
            $maxima = get_option( 'wfr_review_maxima' );

            if( isset($maxima['price']) ) {
                $this->props['priceMax']    = $maxima['price'];    
            }

        }         

        if( $this->params['rating'] ) {
            $this->props['ratingMax']       = $this->options['rating_maximum'];
            $this->props['ratingStyle']     = $this->options['rating_style'];
        }
        
        if( $this->params['sort'] ) {
            $this->props['sortOptions']     = [
                'price_desc'    => __('Price (High-Low)', 'wfr'),
                'price_asc'     => __('Price (Low-High)', 'wfr'),
                'rating_desc'   => __('Rating (High-Low)', 'wfr'),
                'rating_asc'    => __('Rating (Low-High)', 'wfr'),
                'title_asc'     => __('Title (A-Z)', 'wfr'),
                'title_desc'    => __('Title (Z-A)', 'wfr'),
                'value_desc'    => __('Value (High-Low)', 'wfr'),
                'value_asc'     => __('Value (Low-High)', 'wfr')
            ];
        }   
        
        // Visitor Rating sorts
        if( isset($this->options['rating_visitors']) && $this->options['rating_visitors'] ) {
            $this->props['sortOptions']['visitor_rating_desc']   = __('Visitors Rating (High-Low)', 'wfr');   
            $this->props['sortOptions']['visitor_rating_asc']    = __('Visitors Rating (Low-High)', 'wfr');
        }        
        
        if( $this->params['taxonomy'] ) {
            $this->props['terms']           = get_terms( ['fields' => 'id=>name', 'parent' => $this->params['category'], 'taxonomy' => 'reviews_category'] );
        }        

    }

}
