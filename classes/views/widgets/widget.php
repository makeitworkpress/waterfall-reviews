<?php
/**
 * This trait contains common widget functions, so we don't repeat ourself
 */
namespace Waterfall_Reviews\Views\Widgets;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

trait Widget {

    /** 
     * Renders the title form
     * 
     * @param array     $instance   Previously saved values from database.
     * @param string    $fields     The fields data to look tino. At least has 'id', 'type', 'label'. Optionally, has 'options'
     */ 
    public function formFields( $instance, Array $fields ) {

        foreach( $fields as $field ) {

            // We should have a type and id
            if( ! isset($field['type']) || ! isset($field['id']) ) {
                continue;
            }

            $form  = '';
            $id    = esc_attr($this->get_field_id($field['id']));
            $name  = esc_attr($this->get_field_name($field['id']));
            $value = ! empty($instance[$field['id']]) ? esc_attr($instance[$field['id']]) : '';
            
            switch( $field['type'] ) {
                case 'checkbox':
                    $form   = '<input class="checkbox" id="' . $id . '" name="' . $name . '" type="checkbox"'. checked( $value, true, false ) . '>';
                    break;             
                case 'input':
                    $form   = '<input class="widefat" id="' . $id . '" name="' . $name . '" type="text" value="' . $value . '">';
                    break;
                case 'number': 
                    $form   = '<input class="tiny-text" id="' . $id . '" name="' . $name . '" type="number" value="' . $value . '" step="1" min="1">';
                    break;
                case 'select': 

                    if( ! isset($field['options']) ) {
                        $field['options'] = [];
                    }

                    $form   = '<select class="widefat" id="' . $id . '" name="' . $name . '">';
                    foreach( $field['options'] as $key => $label ) {
                        $form .= '<option value="' . $key . '"'. selected( $value, $key, false ) . '>' . $label . '</option>';
                    }
                    $form  .= '</select>';
                    break;           
            }

            // Form fields
            $label = '<label for="' . $id  . '">' . esc_attr($field['label']) . '</label>';
            $form  = in_array($field['type'], ['checkbox']) ? $form . $label : $label . $form;
            
            echo '<p>' . $form . '</p>';

        }

    }

    /** 
     * Sanitizes the saved data for a field
     * 
     * @param   array   $input      The input value for the given fields - equals to new
     * @param   array   $type       The array of fields to save
     * 
     * @return  array   $values     The sanitized values
     */ 
    public function sanitizeFields( $input, Array $fields ) {

        $values = [];

        foreach( $fields as $field ) {

			if( ! isset($field['id']) ) {
				continue;
            }

            $values[$field['id']] = '';

            if( empty( $input[$field['id']] ) ) {
                continue;
            }
            
            switch( $field['type'] ) {
                case 'checkbox': 
                    $values[$field['id']] = true;
                    break;             
                case 'input':
                    $values[$field['id']] = strip_tags($input[$field['id']]);
                    break;
                case 'number': 
                    $values[$field['id']] = intval($input[$field['id']]);
                    break;
                case 'select': 
                    $values[$field['id']] = sanitize_key($input[$field['id']]);
                    break; 
                default:
                    $values[$field['id']] = strip_tags($input[$field['id']]);          
            }            
                        
        }

        return $values;

    }    

    /** 
     * Renders the title on the front-end
     * 
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.   
     */ 
    public function titleDisplay( $args, $instance ) {

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        
    }    

}