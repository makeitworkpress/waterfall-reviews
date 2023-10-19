<?php
namespace Waterfall_Reviews\Views\Widgets;
use WP_Widget as WP_Widget;
use Waterfall_Reviews\Views\Components as Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Filter extends WP_Widget {

    use Widget;

	/**
	 * Register widget with WordPress.
	 */    
    function __construct() {
		parent::__construct(
			'wfr_filter', // Base ID
			esc_html__( 'Waterfall Reviews Filter', 'wfr' ),
			[ 'classname' => 'wfr-filter-widget', 'description' => esc_html__( 'Displays a filter widget for use in review archives.', 'wfr' )]
		);
		
		$this->fields = [
			[
				'label' => __('Title', 'wfr'),
				'id' 		=> 'title',
				'type' 	=> 'input'
			],
			[
				'label' => __('Show Search Filter', 'wfr'),
				'id' 		=> 'search',
				'type' 	=> 'checkbox'
			],
			[
				'label' => __('Label for Search Filter', 'wfr'),
				'id' 		=> 'search_label',
				'type' 	=> 'input',
			],
			[
				'label' => __('Placeholder for Search Filter', 'wfr'),
				'id' 		=> 'searchPlaceholder',
				'type' 	=> 'input',
			],						
			[
				'label' => __('Show Sorting Filter', 'wfr'),
				'id' 		=> 'sort',
				'type' 	=> 'checkbox'
			],
			[
				'label' => __('Label for Sorting Filter', 'wfr'),
				'id' 		=> 'sort_label',
				'type' 	=> 'input',
			],						
			[
				'label' => __('Show Price Filter', 'wfr'),
				'id' 		=> 'price',
				'type' 	=> 'checkbox'
			],
			[
				'label' => __('Label for Price Filter', 'wfr'),
				'id' 		=> 'price_label',
				'type' 	=> 'input',
			],			
			[
				'label' => __('Show Rating Filter', 'wfr'),
				'id' 		=> 'rating',
				'type' 	=> 'checkbox'
			],
			[
				'label' => __('Label for Rating Filter', 'wfr'),
				'id' 		=> 'rating_label',
				'type' 	=> 'input',
			],						
			[
				'label' => __('Show Category Filter', 'wfr'),
				'id' 		=> 'taxonomy',
				'type' 	=> 'checkbox',
			],
			[
				'label' => __('Label for Category Filter', 'wfr'),
				'id' 		=> 'taxonomy_label',
				'type' 	=> 'input',
			],			
			[
				'label' => __('Instant Filter', 'wfr'),
				'id' 		=> 'instant',
				'type' 	=> 'checkbox'
			],			
			[
				'label' => __('Label for Filter Button', 'wfr'),
				'id' 		=> 'label',
				'type' 	=> 'input'
			]														
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

		// Bail out if we only show at certain taxonomies
		if( isset(get_queried_object()->term_id) && $instance['display'] && get_queried_object()->term_id != $instance['display'] ) {
			return;
		}		
        
		echo $args['before_widget'];
        
		$this->title_display( $args, $instance);

		if( isset(get_queried_object()->term_id) ) {
			$instance['category'] = get_queried_object()->term_id;
		}

		$filter = new Components\Filter( $instance );
		$filter->render();
        
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


		$terms = get_terms( ['fields' => 'id=>name', 'hide_empty' => false, 'parent' => 0, 'taxonomy' => 'reviews_category'] );
		$options = [
			0 => __('Select a term', 'wfr')
		];

		if( $terms ) {
			foreach( $terms as $id => $name ) {
				$options[$id] = $name;
			}
		}

		$this->fields['display'] = [
			'label' 	=> __('Only Display in Following Category', 'wfr'),
			'id' 		=> 'display',
			'options' 	=> $options,
			'type' 		=> 'select'
		];

		$this->form_fields( $instance, $this->fields );
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

		$this->fields['display'] = [
			'id' 		=> 'display',
			'type' 		=> 'select'
		];
		
		return $this->sanitize_fields( $new, $this->fields );
	} 	

}