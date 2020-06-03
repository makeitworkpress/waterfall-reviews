<?php
/**
 * Displays an in between section with a document, for use in the articles itself
 */
namespace Waterfall_Reviews\Views\Elementor;
use Waterfall_Reviews\Views\Components as Components;
use Elementor as Elementor;
use Elementor\Controls_Manager as Controls_Manager;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Reviews extends Elementor\Widget_Base {

	/**
	 * Retrieves the name for the widget
	 *
	 * @return string Widget name
	 */
	public function get_name() {
		return 'wfr-reviews-elementor';
	}

	/**
	 * Set the custom title for the widget
	 *
	 * @return string Widget title
	 */
	public function get_title() {
		return __( 'Reviews', 'wfr' );
    }
    
    /**
	 * Name for the icon used
	 *
	 * @return string Widget icon
     */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

    
    /**
	 * Name for the category used
	 *
	 * @return string Category name
     */	
	public function get_categories() {
		return ['waterfall-widgets'];
	}	
    
	/**
	 * Registers the custom widget controls. 
	 */
	protected function _register_controls() {

        /**
         * Elements
         */
        $this->start_controls_section( 
            'section_elements',
            [
                'label' => esc_html__( 'Elements', 'wfr' ),
            ]
		);
		
		$this->add_control(
			'button',
			[
				'label'     	=> __( 'Button Text', 'wfr' ),
				'description'   => __( 'Button text for the button linking to the review. Leave empty to disable the button.', 'wfr' ),
				'type'      	=> Controls_Manager::TEXT,
				'default'   	=> '',
			]
		); 
		
		$this->add_control(
			'excerpt',
			[
				'label'     	=> __( 'Show Excerpt', 'wfr' ),
				'description'   => __( 'Displays the excerpt in each item.', 'wfr' ),
				'type'      	=> Controls_Manager::SWITCHER,
				'default'   	=> 'no',
				'label_on'  	=> __( 'Yes', 'wfr' ),
				'label_off' 	=> __( 'No', 'wfr' ),
			]
		);	
		
		$this->add_control(
			'price',
			[
				'label'     	=> __( 'Show Price', 'wfr' ),
				'type'      	=> Controls_Manager::SWITCHER,
				'default'   	=> 'yes',
				'label_on'  	=> __( 'Yes', 'wfr' ),
				'label_off' 	=> __( 'No', 'wfr' ),
			]
		);

		$this->add_control(
			'price_button',
			[
				'label'     	=> __( 'Price Button Text', 'wfr' ),
				'description'   => __( 'Button text for the button linking to first vendor of the reviewed item. Leave empty to disable this button.', 'wfr' ),
				'type'      	=> Controls_Manager::TEXT,
				'default'   	=> '',
			]
		); 		
		
		$this->add_control(
			'rating',
			[
				'label'     	=> __( 'Show Ratings', 'wfr' ),
				'description'   => __( 'Displays the overall rating of the review within each item.', 'wfr' ),
				'type'      	=> Controls_Manager::SWITCHER,
				'default'   	=> 'yes',
				'label_on'  	=> __( 'Yes', 'wfr' ),
				'label_off' 	=> __( 'No', 'wfr' ),
			]
		);
		
		$this->add_control(
			'summary',
			[
				'label'     	=> __( 'Show Summary', 'wfr' ),
				'description'   => __( 'Displays the review summary within each item.', 'wfr' ),
				'type'      	=> Controls_Manager::SWITCHER,
				'default'   	=> 'no',
				'label_on'  	=> __( 'Yes', 'wfr' ),
				'label_off' 	=> __( 'No', 'wfr' ),
			]
        );		
        
        $this->end_controls_section();

        /**
         * Our general style
         */
        $this->start_controls_section( 
            'section_general',
            [
                'label' => esc_html__( 'General', 'wfr' ),
            ]
		);
		
		$this->add_control(
			'title_tag',
			[
				'label'     => __( 'Headings Tag', 'wfr' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h4',
				'options'   => [
					'h2' => __('H2', 'wfr'),
					'h3' => __('H3', 'wfr'),
					'h4' => __('H4', 'wfr'),
					'h5' => __('H5', 'wfr')
				]
			]
		); 		

		$this->add_control(
			'view',
			[
				'label'     => __( 'Display Type', 'wfr' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'list',
				'options'   => wf_get_grid_options()
			]
		); 
		
		$this->add_control(
			'grid',
			[
				'label'     => __( 'Grid Columns', 'wfr' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'third',
				'options'   => wf_get_column_options()
			]
		);  		
		
		

		$this->add_control(
			'height',
			[
				'label'         => __( 'Minimum Height in Pixel', 'wfr' ),
				'description'   => __( 'Optional minimum height for a single review in pixels.', 'wfr' ),
                'type'          => Controls_Manager::SLIDER,
				'range'         => [
					'px'        => [
						'max'   => 1000,
					],
                ], 
				'selectors' => [
					'{{WRAPPER}} .molecule-post' => 'min-height: {{SIZE}}{{UNIT}};'
				]                             
			]
        );

        $this->end_controls_section();

        /**
         * Image Controls
         */
        $this->start_controls_section( 
            'section_image',
            [
                'label' => esc_html__( 'Image', 'wfr' ),
            ]
        );

		$this->add_control(
			'image',
			[
				'label'     => __( 'Show Image', 'wfr' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => __( 'Show', 'wfr' ),
				'label_off' => __( 'Hide', 'wfr' ),
			]
        ); 
        
		$this->add_control(
			'image_featured',
			[
				'label'     => __( 'Image Source', 'wfr' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'standard',
				'options'   => [
                    'standard'  => __( 'Standard Featured Image', 'wfr' ),
                    'logo'      => __( 'Image from Logo in Media', 'wfr' )
                ]
			]
        );
   
		$this->add_control(
			'image_enlarge',
			[
				'label'     => __( 'Enlarge on Hover', 'wfr' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => __( 'Yes', 'wfr' ),
				'label_off' => __( 'No', 'wfr' ),
			]
        );          
        
		$this->add_control(
			'image_float',
			[
				'label'     => __( 'Image Float', 'wfr' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => wf_get_float_options()
			]
        ); 
        
		$this->add_group_control(
			Elementor\Group_Control_Image_Size::get_type(),
			[
				'name'    => 'image_size', // Actually its `image_size`.
				'default' => 'square-ld',
			]
		);        
        
        $this->end_controls_section();        
        
        /**
         * Our query
         */
        $this->start_controls_section( 
            'section_query',
            [
                'label' => esc_html__( 'Query', 'wfr' ),
            ]
		);
		
		$this->add_control(
			'number',
			[
				'label'     	=> __('Number of Reviews to Display', 'wfr'),
				'type'      	=> Controls_Manager::NUMBER,
				'default'   	=> 3,
				'min'   		=> 0
			]
		);

		$this->add_control(
			'offset',
			[
				'label'     	=> __('Reviews Offset', 'wfr'),
				'description'   => __('The offset is the number of reviews skipped at the beginning of the list.', 'wfr'),
				'type'      	=> Controls_Manager::NUMBER,
				'default'   	=> 0,
				'min'   		=> 0
			]
		);

		$posts = get_posts( ['fields' => 'ids', 'post_type' => 'reviews'] );

		if( $posts ) {
			foreach( $posts as $id ) {
				$reviews[$id] = get_the_title($id);
			}
		} 
		
		if( isset($reviews) ) {
			foreach( ['exclude' => __('Exclude', 'wfr'), 'include' => __('Include', 'wfr')] as $key => $label ) {
				$this->add_control(
					$key,
					[
						'label'     	=> sprintf( __('Reviews to %s', 'wfr'), $label ),
						'type'      	=> Controls_Manager::SELECT2,
						'default'   	=> '',
						'multiple'		=> true,
						'options'		=> $reviews
					]
				);
			}
		}

		// Sorting
		$sorts = [
			'date_desc'		=> __('Date (New-Old)', 'wfr'),
			'date_asc'		=> __('Date (Old-New)', 'wfr'),
			'price_desc'    => __('Price (High-Low)', 'wfr'),
			'price_asc'     => __('Price (Low-High)', 'wfr'),
			'rating_desc'   => __('Rating (High-Low)', 'wfr'),
			'rating_asc'    => __('Rating (Low-High)', 'wfr'),
			'title_asc'     => __('Title (A-Z)', 'wfr'),
			'title_desc'    => __('Title (Z-A)', 'wfr'),
			'value_desc'    => __('Value (High-Low)', 'wfr'),
			'value_asc'     => __('Value (Low-High)', 'wfr')
		];		

		$options = get_option('waterfall_options'); 

		if( isset($options['rating_visitors']) && $options['rating_visitors'] ) {
			$sorts['visitor_rating_asc']    = __('Visitors Rating (Low-High)', 'wfr');
			$sorts['visitor_rating_desc']   = __('Visitors Rating (High-Low)', 'wfr');   
		}		

		$this->add_control(
			'sort',
			[
				'label'     	=> __('Ordering of Reviews', 'wfr'),
				'type'      	=> Controls_Manager::SELECT,
				'default'   	=> 'date_desc',
				'options'   	=> $sorts	
			]
		);

        $taxonomies = [
			'categories' => [
				'description'	=> __('Displays reviews from the selected categories', 'wfr'),
				'label' 		=> 'Reviews Category', 
				'terms' 		=> get_terms( ['fields' => 'id=>name', 'hide_empty' => false, 'parent' => 0, 'taxonomy' => 'reviews_category'] ) 
			],
			'tags'		 => [
				'description'	=> __('Displays reviews from the selected tags', 'wfr'),
				'label' 		=> 'Reviews tag', 
				'terms' 		=> get_terms( ['fields' => 'id=>name', 'hide_empty' => false, 'parent' => 0, 'taxonomy' => 'reviews_tag'] ) 				
			]
		];

		foreach( $taxonomies as $name => $taxonomy ) {
			
			// We should have terms
			if( ! $taxonomy['terms'] ) {
				continue;
			}

			$this->add_control(
				$name,
				[
					'description'   => $taxonomy['description'],
					'label'     	=> $taxonomy['label'],
					'type'      	=> Controls_Manager::SELECT2,
					'default'   	=> '',
					'multiple'   	=> true,
					'options'   	=> $taxonomy['terms'] 
				]
			); 				

		}

		$this->add_control(
			'pagination',
			[
				'label'     => __( 'Show Pagination', 'wfr' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'label_on'  => __( 'Yes', 'elementor' ),
				'label_off' => __( 'No', 'elementor' ),
			]
        ); 		       

		$this->end_controls_section();
    }
	
	/**
	 * Renders the output of the widget
	 */
	protected function render() {
		
		$settings 	= $this->get_settings();

		/**
		 * Direct Arguments
		 */
		$args		= [
			'button'			=> $settings['button'] ? true : false,
			'buttonLabel'		=> $settings['button'],
			'postProperties' 	=> [
				'grid' 			=> $settings['grid'],
                'headerAtoms'   => [
                    'title'     => ['atom' => 'title', 'properties' => ['attributes' => ['itemprop' => 'name headline', 'class' => 'entry-title'], 'tag' => $settings['title_tag'] ? $settings['title_tag'] : 'h2', 'link' => 'post']]     
                ],				
				'image'			=> [
					'attributes' 	=> [
						'class' 	=> 'entry-image',
					],
					'enlarge' 		=> $settings['image_enlarge'], 
					'float' 		=> $settings['image_float'], 
					'link'      	=> 'post',
					'size' 			=> $settings['image_size'] ? $settings['image_size'] : 'ld-square'
				]
			],
			'featured'			=> $settings['image_featured'],
			'price'				=> $settings['price'],
			'priceButton'		=> $settings['price_button'],
			'rating'			=> $settings['rating'],
			'summary'			=> $settings['summary'],
			'queryArgs'			=> [
				'offset' 			=> $settings['offset'],	
				'posts_per_page' 	=> $settings['number']	
			],
			'view'				=> $settings['view']	
		];

		/**
		 * Element Settings
		 */
		if( $settings['excerpt'] ) {
			$args['postProperties']['contentAtoms'] = ['content' => ['atom' => 'content', 'properties' => ['type' => 'excerpt']]];
		} else {
			$args['postProperties']['contentAtoms'] = [];	
		}

		/**
		 * General Settings
		 */
		if( $settings['height']['size'] ) {
			$args['postProperties']['attributes'] = ['style' => ['min-height' =>  $settings['height']['size'] .  $settings['height']['unit']]];
		}	
		
		/**
		 * Image Settings
		 */
		if( ! $settings['image'] ) {
			$args['postProperties']['image'] 	= [];
		}

		/**
		 * Query Settings
		 */
		if( isset($settings['categories']) && $settings['categories'] ) {
			$args['queryArgs']['tax_query'][] 	= ['taxonomy' => 'reviews_category', 'terms' => $settings['categories']];
		}

		if( $settings['exclude'] ) {
			$args['queryArgs']['post__not_in']	= $settings['exclude'];
		}
		
		if( $settings['include'] ) {
			$args['queryArgs']['post__in']		= $settings['include'];
		}
		
		if( $settings['sort'] ) {
			$args['queryArgs']					= sortingArguments($args['queryArgs'], $settings['sort']);
		}		

		if( isset($settings['tags']) && $settings['tags'] ) {
			$args['queryArgs']['tax_query'][] 	= ['taxonomy' => 'reviews_tag', 'terms' => $settings['tags']];
		}
		
		if( $settings['pagination'] == 'yes' ) {
			$args['pagination'] = ['type' => 'numbers'];
		} else {
			$args['pagination'] = false;	
		}
		
		$reviews 	= new Components\Reviews( $args );
		$reviews->render();
    
    }

	/**
	 * Render output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @access protected
	 */
	 protected function _content_template() {}
		
	/**
	 * Render  as plain content.
	 *
	 * Override the default render behavior, don't render sidebar content.
	 *
	 * @access public
	 */
	public function render_plain_content() {}    

}        