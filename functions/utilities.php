<?php
/** 
 * Utility functions used by Waterfall Reviews
 */

/**
 * Retrieves the correct sorting arguments
 * 
 * @param   Array   $args The current query arguments
 * @param   String  $sort The current sorter preference
 * @return  Array   $args The sorting arguments
 */
function sorting_arguments( $args = ['post_type' => 'reviews'], $sort = 'date_asc' ) {
    
    $sorts = [
        'date_asc'		=> ['order' => 'ASC', 'orderby' => 'date'],
        'date_desc'		=> ['order' => 'DESC', 'orderby' => 'date'],
        'price_asc'     => ['order' => 'ASC', 'orderby' => 'meta_value_num', 'meta_key' => 'price'],
        'price_desc'    => ['order' => 'DESC', 'orderby' => 'meta_value_num', 'meta_key' => 'price'],
        'rating_asc'    => ['order' => 'ASC', 'orderby' => 'meta_value_num', 'meta_key' => 'rating'],
        'rating_desc'   => ['order' => 'DESC', 'orderby' => 'meta_value_num', 'meta_key' => 'rating'],			
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
        'visitor_rating_asc'    => ['order' => 'ASC', 'orderby' => 'meta_value_num', 'meta_key' => 'visitors_rating'],                                     
        'visitor_rating_desc'   => ['order' => 'DESC', 'orderby' => 'meta_value_num', 'meta_key' => 'visitors_rating']                          
    ];

    foreach( $sorts as $key => $value ) {
        if( $sort == $key ) {
            $args = wp_parse_args($value, $args);
        }
    }

    return $args;

}