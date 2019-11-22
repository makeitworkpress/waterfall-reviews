<?php
namespace Waterfall_Reviews\Views\Widgets;
use WP_Widget as WP_Widget;
use Waterfall_Reviews\Views\Components as Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Similar extends WP_Widget {

	use Widget;
	
	/**
	 * Contains our form fields
	 * @access public
	 */
	public $fields = [];	

	/**
	 * Register widget with WordPress.
	 */    
    function __construct() {
		parent::__construct(
			'wfr_similar', // Base ID
			esc_html__( 'Waterfall Reviews Similar', 'wfr' ),
			[ 'classname' => 'wfr-similar-widget', 'description' => esc_html__( 'Displays similar or related reviews within a single review.', 'wfr' )]
		);    
		
		$this->fields = [
			[
				'label' => __('Title', 'wfr'),
				'id' 	=> 'title',
				'type' 	=> 'input'
			],
			[
				'label' => __('Number of Reviews to show', 'wfr'),
				'id' 	=> 'number',
				'type' 	=> 'number'
			],
			[
				'label' => __('Instead of Similar, load Related Reviews', 'wfr'),
				'id' 	=> 'related',
				'type' 	=> 'checkbox'
			],									
			[
				'label' => __('Show Button to Review', 'wfr'),
				'id' 	=> 'button',
				'type' 	=> 'checkbox'
			],
			[
				'label' => __('Label for this Button', 'wfr'),
				'id' 	=> 'buttonLabel',
				'type' 	=> 'input'
			],
			[
				'label' => __('Show excerpt', 'wfr'),
				'id' 	=> 'excerpt',
				'type' 	=> 'checkbox'
			],			
			[
				'label' => __('Enlarge Featured Image on Hover', 'wfr'),
				'id' 	=> 'enlarge',
				'type' 	=> 'checkbox'
			],			
			[
				'label' => __('Featured Image to Show', 'wfr'),
				'id' 	=> 'featured',
				'type' 	=> 'select',
				'options' => [
					'standard' 	=> __('Standard Featured Image', 'wfr'),
					'logo' 		=> __('Logo from Review Media', 'wfr'),
				]
			],
			[
				'label' => __('Show Prices', 'wfr'),
				'id' 	=> 'price',
				'type' 	=> 'checkbox'
			],
			[
				'label' => __('Text for Price Button', 'wfr'),
				'id' 	=> 'priceButton',
				'type' 	=> 'input'
			],
			[
				'label' => __('Show Rating', 'wfr'),
				'id' 	=> 'rating',
				'type' 	=> 'checkbox'
			],
			[
				'label' => __('Show Summary', 'wfr'),
				'id' 	=> 'summary',
				'type' 	=> 'checkbox'
			],
			[
				'label' => __('Style of Reviews', 'wfr'),
				'id' 	=> 'view',
				'type' 	=> 'select',
				'options' => wf_get_grid_options()
			],												
		];
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
	
		global $post;

		// The global post object should be set
		if( ! isset($post) ) {
			return;
		}

		// Default properties
		$instance['postProperties']['contentAtoms'] = $instance['excerpt'] ? ['content' => ['atom' => 'content', 'properties' => ['type' => 'excerpt']]] : [];
		$instance['postProperties']['headerAtoms']  = ['title' => ['atom' => 'title', 'properties' => ['attributes' => ['itemprop' => 'name headline', 'class' => 'entry-title'], 'tag' => 'h4', 'link' => 'post']]];
		$instance['postProperties']['image'] 	= [
			'enlarge' 	=> $instance['enlarge'],
			'float'		=> 'none',
			'lazyload'  => isset($this->options['optimize']['lazyLoad']) && $this->options['optimize']['lazyLoad'] ? true : false,
			'link'		=> 'post',
			'size'		=> 'medium'
		];
		$instance['postProperties']['grid'] 			= 'full';
		$instance['postProperties']['grid'] 			= 'full';
		
		// Query Properties
		$instance['queryArgs']['post__not_in']  		= [$post->ID];
		$instance['queryArgs']['posts_per_page'] 		= $instance['number'];	

		// Widgets do not use schema
		$instance['schema']								= false;

		// Set-ups related reviews.
		if( $instance['related'] ) {

			if( function_exists('ep_find_related') ) {
				$instance['queryArgs']['post__in']      = ep_find_related( $post->ID, $instance['number'] );
				$instance['queryArgs']['ep_integrate']  = true;
			} else {
				$taxonomies 							= get_post_taxonomies( $post );
				$instance['queryArgs']['post__in'] 		= [];

				if( $taxonomies ) {
					foreach( $taxonomies as $taxonomy ) {
						$terms 							= get_the_terms( $post->ID, $taxonomy );
						if( $terms ) {
							$termIDs 					= [];
							foreach( $terms as $term ) {
								$termIDs[] 				= $term->term_id;   
							}
							$query['tax_query'][] 		= [
								'taxonomy'  => $taxonomy,
								'terms'     => $termIDs
							];
						}
					}
				}
			} 

		// Displays similar reviews. Returns nothing if there are no similar widgets to show.
		} else {
			$similar = get_post_meta($post->ID, 'similar', true);
			if( $similar  ) {
				$instance['queryArgs']['post__in']		= $similar;
			} else {
				return;
			}
		}
		
        echo $args['before_widget'];
        
		$this->titleDisplay( $args, $instance );

        $reviews = new Components\Reviews( $instance );
        $reviews->render();
        
        echo $args['after_widget'];
        
    } 	
	
    /**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$this->formFields( $instance, $this->fields );
    } 
    
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new Values just sent to be saved.
	 * @param array $old Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new, $old ) {
		return $this->sanitizeFields( $new, $this->fields );
	} 	

}