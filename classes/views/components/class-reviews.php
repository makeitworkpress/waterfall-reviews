<?php
/**
 * The reviews components initializes our list display of reviews and 
 * formats all data to be displayed in the template.
 */
namespace Waterfall_Reviews\Views\Components;
use MakeitWorkPress\WP_Components as WP_Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Reviews extends Component {

    /**
     * Contains our reviews
     * @access public
     */
    public $reviews = [];

    /**
     * Contains our arguments used for rendering the posts
     * @access public
     */
    public $arguments;    

    /**
     * Set up our parameters
     */
    protected function initialize() {

        // Schema indicators
        $noSchema = isset($this->options['scheme_post_types_disable']) && $this->options['scheme_post_types_disable'] ? $this->options['scheme_post_types_disable'] : [];
        
        // Our default parameters
        $this->params   = WP_Components\Build::multi_parse_args( $this->params, [
            'ajax'              => true,
            'attributes'        => [
                'class'         => 'wfr-reviews-component',
                'data'          => ['id' => 'wfr-reviews-component'],
                'itemscope'     => '',
                'itemtype'      => ''
            ],
            'none'              => isset($this->layout['reviews_archive_content_none']) ? $this->layout['reviews_archive_content_none'] : __('No reviews found.', 'wfr'),
            'post_properties'   => [
                'attributes'    => [
                    'itemprop'  => $this->options['review_scheme'] == 'BlogPosting' ? 'blogPost' : '',
                    'itemtype'  => 'http://schema.org/' . $this->options['review_scheme'],
                    'style'     => ['min-height' => isset($this->layout['reviews_archive_content_height']) && $this->layout['reviews_archive_content_height'] ? $this->layout['reviews_archive_content_height'] . 'px;' : '']

                ], 
                'blog_schema'   => $this->options['review_scheme'] == 'BlogPosting' ? true : false,
                'content_atoms' => isset($this->layout['reviews_archive_content_content']) && $this->layout['reviews_archive_content_content'] == 'excerpt' ? ['content' => ['atom' => 'content', 'properties' => ['type' => 'excerpt']]] : [],
                'footer_atoms'  => [],
                'grid'          => isset($this->layout['reviews_archive_content_columns']) && $this->layout['reviews_archive_content_columns'] ? $this->layout['reviews_archive_content_columns'] : 'third',
                'header_atoms'  => [
                    'title'     => ['atom' => 'title', 'properties' => ['attributes' => ['itemprop' => 'name headline', 'class' => 'entry-title'], 'tag' => 'h2', 'link' => 'post']]     
                ],
                'image'         => [ 
                    'attributes' => [
                        'class' => 'entry-image'
                    ],
                    'enlarge'   => isset($this->layout['reviews_archive_content_image_enlarge']) && $this->layout['reviews_archive_content_image_enlarge'] ? true : false, 
                    'float'     => isset($this->layout['reviews_archive_content_image_float']) && $this->layout['reviews_archive_content_image_float'] ? $this->layout['reviews_archive_content_image_float'] : 'none', 
                    'lazyload'  => isset($this->options['optimize']['lazyLoad']) && $this->options['optimize']['lazyLoad'] ? true : false, 
                    'link'      => 'post', 
                    'size'      => isset($this->layout['reviews_archive_content_image']) && $this->layout['reviews_archive_content_image'] ? $this->layout['reviews_archive_content_image'] : 'square-ld'
                ],
                'logo'      => isset($this->customizer['logo']) && is_numeric( $this->customizer['logo'] ) ? wp_get_attachment_image_url( $this->customizer['logo'], 'full' ) : ''               
            ],
            'button'        => isset($this->layout['reviews_archive_content_button']) && $this->layout['reviews_archive_content_button'] ? true : false,  // Displays a button to the post
            'button_label'  => isset($this->layout['reviews_archive_content_button']) && $this->layout['reviews_archive_content_button'] ? $this->layout['reviews_archive_content_button'] : '',                 // Text for this button
            'featured'      => isset($this->layout['reviews_archive_content_featured']) && $this->layout['reviews_archive_content_featured'] ? $this->layout['reviews_archive_content_featured'] : 'standard',   // Determines were we get our featured image from, either the custom meta field for the logo or featured image. Supports 'logo' or 'standard'.
            'grid_gap'      => isset($this->layout['reviews_archive_content_gap']) && $this->layout['reviews_archive_content_gap'] ? $this->layout['reviews_archive_content_gap'] : 'default', 
            'pagination'    => ['type' => 'numbers'],                                           // Displays pagination
            'price'         => isset($this->layout['reviews_archive_content_price']) && $this->layout['reviews_archive_content_price'] ? true : false,   // Displays the price
            'price_button'  => isset($this->layout['reviews_archive_content_price_button']) && $this->layout['reviews_archive_content_price_button'] ? $this->layout['reviews_archive_content_price_button'] : '',           // Displays the button to one supplier
            'rating'        => isset($this->layout['reviews_archive_content_rating']) && $this->layout['reviews_archive_content_rating'] ? true : false,                                                  // Displays the rating within a review
            'schema'        => in_array( 'reviews', $noSchema ) ? false : true,
            'summary'       => isset($this->layout['reviews_archive_content_summary']) && $this->layout['reviews_archive_content_summary'] ? true : false,                                                 // Adds the summary of our review
            'query'         => '',
            'query_args'     => [
                'post_status'   => 'publish',
                'post_type'     => 'reviews', 
                'fields'        => 'ids'
            ],
            'view'          => isset($this->layout['reviews_archive_content_style']) && $this->layout['reviews_archive_content_style'] ? $this->layout['reviews_archive_content_style'] : 'grid',        // Accepts our review style, either list, magazine or grid
        ] );

        // We are using WP_Components to display our posts
        $this->template = false;

    }

    /**
     * Queries and formats our data
     */
    protected function query() {

        // Custom image
        if( $this->params['featured'] == 'logo' ) {
            $this->params['post_properties']['image']['image'] = 'logo'; // Looks for the image under the media meta key
        }

        // Adds the price
        if( $this->params['price'] ) {
            $this->params['post_properties']['footer_atoms']['price'] = [
                'atom'          => 'callback', 
                'properties'    => ['callback' => [$this, 'price']]                
            ];  
        } 

        // Adds our button, linking to the review
        if( $this->params['button'] ) {
          
            $this->params['post_properties']['footer_atoms']['button'] = [
                'atom'          => 'button', 
                'properties'    => [
                    'background'    => 'light',
                    'icon_after'    => isset($this->layout['reviews_archive_content_button_icon']) && $this->layout['reviews_archive_content_button_icon'] ? 'angle-right' : '',
                    'icon_visible'  => 'hover',
                    'label'         => $this->params['button_label'],
                    'size'          => 'small'                   
                ]
            ];

        }        
            
        // Adds the rating component
        if( $this->params['rating'] ) {
            $this->params['post_properties']['header_atoms']['rating'] = [
                'atom'          => 'callback', 
                'properties'    => ['callback' => [$this, 'rating']]                
            ];
        }

        // Adds our summary
        if( $this->params['summary'] ) {
            $this->params['post_properties']['content_atoms']['summary'] = [
                'atom'          => 'meta', 
                'properties'    => [
                    'attributes'  => ['itemprop' => 'description', 'class' => 'wfr-item-summary'],
                    'key'         => 'summary',
                ]                
            ];
        }
        
    } 

    /**
     * Instead of using a custom template, we use the WP_Components library for generating posts
     * 
     * @param boolean $render If we echo the value instead of returning
     */
    public function render( $render = true ) {

        if( $render ) {
            WP_Components\Build::molecule('posts', $this->params, $render );
        } elseif( ! $render ) {
            return WP_Components\Build::molecule('posts', $this->params, $render );
        }

    }

    /**
     * Renders our prices
     */
    public function price() {
        $prices = new Prices([
            'button'        => $this->params['price_button'] ? true : false, 
            'button_label'  => $this->params['price_button'], 
            'names'         => false, 
            'single'        => true
        ]);
        $prices->render();       
    }    

    /**
     * Renders our rating
     */
    public function rating() {
        $rating = new Rating(['names' => false, 'schema' => $this->params['schema'] ? 'aggregate' : false]);
        $rating->render();
    }    

}