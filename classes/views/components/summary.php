<?php
/**
 * Displays the summary of a single review, including advantages and disadvantages
 */
namespace Waterfall_Reviews\Views\Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Summary extends Component {

    protected function initialize() {

        // Parameters
        $this->params = wp_parse_args( $this->params, [
            'advantages_title'      => isset($this->layout['reviews_ad_title']) ? $this->layout['reviews_ad_title'] : '',
            'author'                => '',
            'details'               => true,
            'disadvantages_title'   => isset($this->layout['reviews_dis_title']) ? $this->layout['reviews_dis_title'] : '',
            'schema'                => true,
            'summary'               => true
        ] );

        // Displayed properties
        $this->props    = [
            'advantages_title'      => '',
            'advantages'            => [],
            'author'                => $this->params['author'],
            'disadvantages'         => [],
            'disadvantages_title'   => '',
            'schema'                => $this->params['schema'],
            'summary'               => ''
        ]; 

        $this->template = 'summary';

    }

    /**
     * Queries the details
     */
    protected function query() {

        global $post;

        if( $this->params['advantages_title'] ) {
            $this->props['advantages_title'] = $this->params['advantages_title'];  
        }

        if( $this->params['disadvantages_title'] ) {
            $this->props['disadvantages_title'] = $this->params['disadvantages_title'];
        }        

        if( $this->params['summary'] ) {
            $this->props['description'] = get_post_meta( $post->ID, 'summary', true );
        }
    
        if( $this->params['details'] ) {
    
            foreach( ['advantages', 'disadvantages'] as $aspect ) {
                $meta                   = get_post_meta( $post->ID, $aspect, true );

                if( $meta && is_array($meta) ) {
                    
                    foreach( $meta as $listed ) {
                        
                        if( ! $listed['name'] ) {
                            continue;
                        }

                        $this->props[$aspect][] = $listed['name'];

                    }

                }

            }

        }
    
    }

}
