<?php
/**
 * Contains the widget class for a prices widget
 */
namespace Waterfall_Reviews\Views\Widgets;
use WP_Widget as WP_Widget;
use Waterfall_Reviews\Views\Components as Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Prices extends WP_Widget {

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
			'wfr_prices', // Base ID
			esc_html__( 'Waterfall Reviews Prices', 'wfr' ),
			[ 'classname' => 'wfr-prices-widget', 'description' => esc_html__( 'Displays the prices within a single review.', 'wfr' )]
		); 
		
		$this->fields = [
			[
				'label' => __('Title', 'wfr'),
				'id' 	=> 'title',
				'type' 	=> 'input'
			],
			[
				'label' => __('Show Rating', 'wfr'),
				'id' 	=> 'rating',
				'type' 	=> 'checkbox'
			],				
			[
				'label' => __('Show summary', 'wfr'),
				'id' 	=> 'summary',
				'type' 	=> 'checkbox'
			],			
			[
				'label' => __('Show Best Price on Top', 'wfr'),
				'id' 	=> 'best',
				'type' 	=> 'checkbox'
			],
			[
				'label' => __('Show Buttons to Suppliers', 'wfr'),
				'id' 	=> 'button',
				'type' 	=> 'checkbox'
			],
			[
				'label' => __('Label for Button', 'wfr'),
				'id' 	=> 'buttonLabel',
				'type' 	=> 'input'
			],
			[
				'label' => __('Show Supplier Names', 'wfr'),
				'id' 	=> 'names',
				'type' 	=> 'checkbox'
			],
			[
				'label' => __('Fix Widget Position', 'wfr'),
				'id' 	=> 'fixed',
				'type' 	=> 'checkbox'
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

		if( isset($instance['fixed']) && $instance['fixed'] ) {
			$args['before_widget'] = str_replace('wfr-prices-widget', 'wfr-prices-widget wfr-prices-widget-fixed', $args['before_widget']);
		}				
        
		echo $args['before_widget'];
        
		$this->titleDisplay( $args, $instance);

		if( isset($instance['rating']) && $instance['rating'] ) {
			$rating = new Components\Rating(['schema' => false]);
			$rating->render();
		}

		// Adds the summary
		if( isset($instance['summary']) && $instance['summary'] ) {
			$summary = new Components\Summary( ['details' => false, 'schema' => false] );
			$summary->render();
		}

        $prices = new Components\Prices( $instance );
		$prices->render();			
        
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