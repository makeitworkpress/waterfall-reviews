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
	 * Charts shortcode
	 */
	public function charts( $atts = [] ) {

        // Default attributes
        $atts = shortcode_atts( [
			'categories' => [],                           		// Only displays data from these review category ids, separate by comma
			'form'		 => false,
			'groups'     => [],                                 // Limits the display only to certain groups of criteria (for criteria, use sanitized criteria keys)
            'id'         => 'wfr_chart_data',             		// The default id for the chart
            'include'    => [],                           		// If valid, only includes these posts ids in the query 
            'label'      => '',  								// Label that is used for selecting charts - if set to false, hides the form
			'meta'       => '',                       			// Indicates the metafield that is used as a source of loading and ordering the default chart data 
			'normal'     => __('Normal Values', 'wfr'),			// The button for normal chart loading
			'tags'       => [],  								// Only displays data from these review tag ids, separate by tag
			'select'     => __('Select data', 'wfr'),     		// Label that is used for the default option 
			'weighted'   => __('Price Weighted Values'), 		// The button for weighted chart loading
			'weight'	 => false								// Allows the display of price weighted data if supported
		], $atts, 'charts' );

		// Some sanitization of shortcode inputs
		foreach( $atts as $key => $value ) {

			if( in_array($key, ['categories', 'include', 'tags']) ) {
				$atts[$key] = array_filter( explode(',', sanitize_text_field($value)), function($v) { return is_numeric($v); } );
			} else if( in_array($key, ['groups']) ) {
				$atts[$key] = array_filter( explode(',', sanitize_text_field($value)) );
			} else if( in_array($key, ['id', 'meta']) ) {
				$atts[$key] = sanitize_key($value);
			} else {
				$atts[$key] = sanitize_text_field($value);
			}
		}

		$atts['load'] = $atts['meta'] ? true : false; 	// Loads the selected chart by default if a meta key is set
		$atts['form'] = $atts['label'] ? true : false; 	// Shows the form if we have a label

		// Render our charts
		$charts 	= new Components\Charts( $atts );

		return $charts->render(false); 
		 	
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
            'title'             => 'h3',
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
	

	/**
	 * Charts shortcode
	 */
	public function tables( $atts = [] ) {

        // Default attributes
        $atts = shortcode_atts( [
            'attributes'    => [],      // Limits the display only to the given criteria attributes (use attribute meta keys)
            'categories'    => [],      // Only displays data from these review category ids
            'form'          => false,   // Displays a review selection form (or not)
            'groups'        => [],      // Only show the content from the given groups
            'label'         => '', 		// Label that is used for selecting reviews
            'load'          => true,    // Loads the table directly if set to true. Warning! Loads all reviews if a query is not defined.
            'price'         => __('Get', 'wfr'), // Button to the offer
            'properties'    => [],      // Limits the display only to the given properties (use property meta keys)            
            'reviews'       => [],      // Default reviews to load
			'tags'          => [],      // Only displays data from these review tag ids 
			'title'         => true,    // Displaces the title of a review within the table
            'view'          => 'table',  // We either show 'tabs' or a complete 'table'
            'weight'        => false,   // If values need to be weighted
		], $atts, 'charts' );

		// Some sanitization of shortcode inputs
		foreach( $atts as $key => $value ) {
			
			if( in_array($key, ['categories', 'reviews', 'tags']) ) {
				$atts[$key] = array_filter( explode(',', sanitize_text_field($value)), function($v) { return is_numeric($v); } );
			} else if( in_array($key, ['attributes', 'groups', 'properties']) ) {
				$atts[$key] = array_filter( explode(',', sanitize_text_field($value)) );
			} else {
				$atts[$key] = sanitize_text_field($value);
			}

		}

		// A form is set to true if we specify a label
		$atts['form'] = $atts['label'] ? true : false;

		// Render our charts
		$tables 	= new Components\Tables( $atts );

		return $tables->render(false); 
		 	
	}	
      

}