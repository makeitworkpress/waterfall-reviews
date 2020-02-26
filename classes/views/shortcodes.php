<?php
/**
 * Contains the class abstraction for our components
 */

namespace Waterfall_Reviews\Views;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Shortcodes {
 
    /**
     * Set up the shortcodes
     */
    public function __construct( ) { 
        
        // Removes parent methods from the actions array
        foreach( get_class_methods($this) as $key => $action ) {
            
            if( strpos($action, "__") === 0 ) {
                continue;
			}
                
            add_shortcode( 'wfr_' . $action, [$this, $action] );
            
        }  
        
    }
    
    /**
     * Reviews shortcode
     */
    public function reviews( $atts = [] ) {

        // Default attributes
        $atts = shortcode_atts( [
            'button'            => '',
            'categories'        => '',
            'grid'              => 'full',
            'rating'            => true,
            'excerpt'           => false,
            'exclude'           => '',
            'featured'          => 'logo',
            'include'           => '',
            'image'             => true,
            'image_enlarge'     => true,
            'image_float'       => 'left',
            'image_size'        => 'thumbnail',
            'offset'            => 0,
            'number'            => 10,
            'pagination'        => false,
            'price'             => true,
            'price_button'      => '',
            'sort'              => 'rating_desc',
            'summary'           => false,
            'tags'              => '',
            'title'             => 'h4',
            'view'              => 'list'
        ], $atts, 'reviews' );  
        
		/**
		 * Direct Arguments
         * @todo Dry this with the \elementor\reviews.php class, arguments are essentially the same.
		 */
		$args		= [
			'attributes'		=> ['class' => 'wfr-reviews-component wfr-shortcode'],
			'button'			=> $atts['button'] ? true : false,
			'buttonLabel'		=> $atts['button'],
			'postProperties' 	=> [
				'grid' 			=> $atts['grid'],
                'headerAtoms'   => [
                    'title'     => [
                        'atom' => 'title', 
                        'properties' => ['attributes' => ['itemprop' => 'name headline', 'class' => 'entry-title'], 'tag' => $atts['title'], 'link' => 'post']
                    ]     
                ],				
				'image'			=> [
					'attributes' 	=> ['class' => 'entry-image'],
					'enlarge' 		=> $atts['image_enlarge'], 
					'float' 		=> $atts['image_float'], 
					'size' 			=> $atts['image_size'] ? $atts['image_size'] : 'ld-square'
				]
			],
			'featured'			=> $atts['featured'],
			'price'				=> $atts['price'],
			'priceButton'		=> $atts['price_button'],
			'rating'			=> $atts['rating'],
			'summary'			=> $atts['summary'],
			'queryArgs'			=> [
				'offset' 			=> $atts['offset'],	
				'posts_per_page' 	=> $atts['number']	
			],
			'view'				=> $atts['view']	
		];

		/**
		 * Element Settings
		 */
		if( $atts['excerpt'] ) {
			$args['postProperties']['contentAtoms'] = ['content' => ['atom' => 'content', 'properties' => ['type' => 'excerpt']]];
		} else {
			$args['postProperties']['contentAtoms'] = [];	
		}	
		
		/**
		 * Image Settings
		 */
		if( ! $atts['image'] ) {
			$args['postProperties']['image'] 	= [];
		}

		/**
		 * Query Settings
		 */
		if( $atts['categories'] ) {
            $atts['categories'] = explode(',', $atts['categories']);
			$args['queryArgs']['tax_query'][] 	= ['taxonomy' => 'reviews_category', 'terms' => $atts['categories']];
		}

		if( $atts['exclude'] ) {
			$args['queryArgs']['post__not_in']	= [$atts['exclude']];
		}
		
		if( $atts['include'] ) {
			$args['queryArgs']['post__in']		= [$atts['include']];
		}
		
		if( $atts['sort'] ) {
			$args['queryArgs']					= sortingArguments($args['queryArgs'], $atts['sort']);
		}		

		if( $atts['tags'] ) {
            $atts['tags'] = explode(',', $atts['tags']);
			$args['queryArgs']['tax_query'][] 	= ['taxonomy' => 'reviews_tag', 'terms' => $atts['tags']];
		}
		
		if( $atts['pagination'] ) {
			$args['pagination'] = ['type' => 'numbers'];
		} else {
			$args['pagination'] = false;	
		}

		$reviews 	= new Components\Reviews( $args );
		return $reviews->render(false);        

    }
      

}