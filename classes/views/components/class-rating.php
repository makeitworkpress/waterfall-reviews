<?php
/**
 * Displays our rating in a distinct style,
 * such as stars, bars, circles, numbers, smileys, percentages
 * 
 * // @todo support for user reviews within comments
 */
namespace Waterfall_Reviews\Views\Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Rating extends Component {

    /**
     * Set up our component
     */
    protected function initialize() {
        
        // Our default parameters
        $this->params   = wp_parse_args( $this->params, [
            'author'        => '',
            'class'         => '',
            'criteria'      => [],                                  // Only loads the rating from the given criteria. Use sanitized criteria key for this
            'id'            => 0,
            'max'           => isset($this->options['rating_maximum']) && $this->options['rating_maximum'] ? $this->options['rating_maximum'] : 5,
            'min'           => 1,
            'names'         => true,
            'nothing'       => __('No ratings have been found', 'wfr'),
            'schema'        => 'aggregate',                         // Accepts aggregate or review
            'source'        => ['overall'],                         // Determines our source and the use microschemes. Accepts [overall, criteria, users] to load our rating for these aspects
            'style'         => isset($this->options['rating_style']) ? $this->options['rating_style'] : '',      // If defined, overwrites our style from the options. Accepts stars, bars, circles, numbers, smileys, percentages
            'values'        => []                                   // Accepts custom rating values, matching their keys (overall, users, sanitized criteria name)
        ] );

        // Define our post id
        if( ! $this->params['id'] ) {
            global $post;
            $this->params['id'] = is_numeric($post) ? $post : $post->ID;
        }        

        $this->template = 'rating';      

    }

    /**
     * Queries and formats our data
     */
    protected function query() {

        // Our parameters equal our props in this case
        $this->props            = $this->params;
        $this->props['names']   = $this->params['names'];
        $this->props['nothing'] = $this->params['nothing'];
        $this->props['ratings'] = [];
        $this->props['schema']  = $this->params['schema'] ? 'itemprop="' . $this->params['schema'] . 'Rating" itemscope="itemscope" itemtype="http://schema.org/' . str_replace('review', '', $this->params['schema']) . 'Rating"' : '';

        // Set-up our overall rating values
        if( in_array('overall', $this->params['source']) ) {

            // Determine our rating count
            if( isset($this->options['rating_visitors']) && $this->options['rating_visitors'] == true ) {
                $count = get_comments_number($this->params['id']) + 1 - count( get_comments(['post_id' => $this->params['id'], 'meta_key' => 'rating_not_counted', 'meta_value' => true]) ); 
            } else {
                $count = 1;
            }

            $this->props['ratings'][] = [
                'count'     => $count,
                'name'      => __('Overall', 'wfr'),
                'source'    => 'overall',  
                'value'     => isset($this->params['values']['overall']) ? $this->params['values']['overall'] : get_post_meta( $this->params['id'], 'rating', true)
            ];

        }

        // Set-up our users rating criteria
        if( in_array('users', $this->params['source']) ) {
            $this->props['ratings'][] = [
                'name'      => __('User Rating', 'wfr'),
                'source'    => 'users',               
                'value'     => isset($this->params['values']['users']) ? $this->params['values']['users'] : get_post_meta( $this->params['id'], 'visitors_rating', true)
            ];
        }        

        // Set-up our criteria ratings
        if( in_array('criteria', $this->params['source']) ) {

            foreach( $this->options['rating_criteria'] as $criteria ) {
                        
                // Bail out if there is no name
                if( ! isset($criteria['name']) || ! $criteria['name'] ) {
                    continue;
                }

                // Our criteria key
                $key = isset($criteria['key']) && $criteria['key'] ? sanitize_key($criteria['key']) : sanitize_key($criteria['name']);

                // Only show the rating for a certain criteria
                if( $this->params['criteria'] && ! in_array($key, $this->params['criteria']) ) {
                    continue;
                }

                $this->props['ratings'][] = [
                    'name'      => $criteria['name'],
                    'source'    => 'criteria',  
                    'value'     => isset($this->params['values'][$key]) ? $this->params['values'][$key] : get_post_meta( $this->params['id'], $key . '_rating', true)
                ];
            
            }

        }


        /**
         * Define the display of our styles
         * Defines stars, bars, circles, numbers, smileys, percentages
         */
        foreach( $this->props['ratings'] as $key => $rating ) {

            // Only display rating if we have values
            if( ! $rating['value'] ) {
                unset($this->props['ratings'][$key]);
                continue;
            }

            $rating['author']   = $this->params['author'];
            $rating['display']  = '';
            $rating['item']     = get_post_meta( $this->params['id'], 'reviewed_item', true );
            $rating['value']    = $rating['value'] ? $rating['value'] : 0;
   
            $setting            = wf_get_data('colors', 'rating_color');
            $color              = $setting ? $setting : '#FD4A5B';            

            switch( $this->params['style'] ) {
                
                // Star rating
                case 'stars':

                    $floor      = floor( $rating['value'] );
                    $fraction   = $rating['value'] - $floor;

                    $fullStars  = $fraction >= 0.75 ? round( $rating['value'] ) : $floor;
                    $halfStars  = $fraction < 0.75 && $fraction > 0.25 ? 1 : 0;
                    $emptyStars = $this->params['max'] - $fullStars - $halfStars;

                    for($i = 1; $i <= $fullStars; $i++) {
                        $rating['display'] .= '<i class="fa fa-star"></i>';
                    }
                
                    if( $halfStars ) {
                        $rating['display'] .= '<i class="fa fa-star-half-o"></i>';
                    }
                
                    for( $i = 1; $i <= $emptyStars; $i++ ) {
                        $rating['display'] .= '<i class="fa fa-star-o"></i>';
                    }                    

                    break;

                // Colored bar rating
                case 'bars':
                    $rating['display']  = '<strong>' . $rating['value'] . '/' . $this->params['max'] . '</strong><span class="wfr-rating-bars"><span style="background-color: ' . $color . '; width:' . ($rating['value']/$this->params['max']) * 100 . '%"></span></span>';
                    break;  
                    
                // Circles with lines rating
                case 'circles':

                    $degrees            = intval( ($rating['value']/$this->params['max']) * 360);

                    $background         = $degrees >= 180 ? 'linear-gradient(90deg, ' . $color . ' 50%, transparent 50% ),' : 'linear-gradient(90deg, transparent 50%, white 50% ),';
                    $background        .= 'linear-gradient(' . (270 - $degrees) . 'deg, ' . $color . ' 50%, transparent 50% )';
                    $rating['display']  = '<span class="wfr-rating-circle" style="background-image: ' . $background . '"><strong>' . $rating['value'] . '</strong></span>';
                    break;    
                    
                // Plain numbers
                case 'numbers':
                    $rating['display'] = '<strong>' . round( $rating['value'], 1 ) . '/' . $this->params['max'] . '</strong>';
                    break;  
                    
                // Smileys
                case 'smileys':
                    $fraction = $rating['value']/$this->params['max'];
                    if( $fraction <= 0.33 ) {
                        $rating['display'] = '<i class="fa fa-frown-o"></i>';
                    } elseif( $fraction <= 0.67 ) {
                        $rating['display'] = '<i class="fa fa-meh-o"></i>';
                    } elseif( $fraction <= 1) {
                        $rating['display'] = '<i class="fa fa-smile-o"></i>';
                    }
                    break;    
                    
                // Numbers
                case 'percentages':
                    $rating['display'] = '<strong>' . intval( ($rating['value']/$this->params['max']) * 100 ) . '%' . '</strong>';
                    break;                    
            }

            $this->props['ratings'][$key] = $rating;

        }
   
    }  

}