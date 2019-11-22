<?php
/**
 * Displays the properties of a single review, including possible criteria properties
 */
namespace Waterfall_Reviews\Views\Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Properties extends Component {

    protected function initialize() {

        $this->params   = wp_parse_args( $this->params, [
            'criteria'      => true,    // Shows the content of each criteria in additional tabs
            'properties'    => true,    // Shows the content properties
            'view'          => 'tabs'   // We either show tabs or a list
        ] );

        $this->template = 'properties'; 

    }

    /**
     * Receives our properties and data from the database
     */
    protected function query() {

        global $post;

        $this->props['class'] = $this->params['view'];

        // Load our properties data into our tabs
        if( $this->params['properties'] && isset($this->options['properties']) && $this->options['properties'] ) {

            $this->props['tabs']['properties']['title']     = __('Properties', 'wfr');

            $this->props['tabs']['properties']['content']   = [];
            
            foreach( $this->options['properties'] as $property ) {

                $key    = sanitize_key($property['name']);
                $meta   = get_post_meta($post->ID, $key . '_property', true);

                // We should have meta
                if( ! $meta ) {
                    continue;
                }

                switch( $property['type'] ) {
                    case 'input':
                    case 'number':
                    case 'textarea':
                        $value = $meta;
                        break;
                    case 'checkbox':
                    case 'select':
                        $value  = '';
                        $values = array_filter( explode(',', $property['values']) );
                        
                        foreach( $values as $choice ) {
                            $identifier     = sanitize_key($choice);
                            
                            if( is_array($meta) && $meta[$identifier] == true ) {
                                
                                if( ! $value ) {
                                    $value  = [];
                                }

                                $value[]    = $choice;
                                
                            } elseif( $identifier == $meta ) {
                                $value      = $choice;    
                            }
                        } 

                        break;
                }

                if( is_array($value) ) {
                    $value = implode(', ', $value);
                }

                $this->props['tabs']['properties']['content'][] = ['label' => $property['name'], 'value' => $value];

            }

            // If we don't have properties, we unset this tab
            if( ! $this->props['tabs']['properties']['content'] ) {
                unset($this->props['tabs']['properties']);
            }

        }        


        // Load our criteria data into our tabs
        if( $this->params['criteria'] && isset($this->options['rating_criteria']) && $this->options['rating_criteria'] ) {

            foreach( $this->options['rating_criteria'] as $criteria ) {

                $key = sanitize_key($criteria['name']);

                // We should have attributes for a criteria
                if( ! isset($this->options[$key . '_attributes']) || ( isset($this->options[$key . '_attributes'][0]['name']) && ! $this->options[$key . '_attributes'][0]['name']) ) {
                    continue;
                }

                $this->props['tabs'][$key]['title']     = $criteria['name'];
                $this->props['tabs'][$key]['content']   = [];

                foreach( $this->options[$key . '_attributes'] as $attribute ) {
                
                    $id     = sanitize_key($attribute['name']);
                    $meta   = get_post_meta($post->ID, $id . '_' . $key . '_attribute', true);

                    switch( $attribute['type'] ) {
                        case 'input':
                        case 'number':
                        case 'textarea':
                            $value = $meta;
                            break;
                        case 'checkbox':
                        case 'select':
                            $value  = '';
                            $values = array_filter( explode(',', $attribute['values']) );
                            
                            foreach( $values as $choice ) {

                                $identifier     = sanitize_key($choice);

                                if( strpos($choice, ':') ) {
                                    $divide     = explode(':', $choice);
                                    $identifier = sanitize_text_field($divide[0]);
                                    $choice     = $divide[1];
                                }
                                
                                if( is_array($meta) && $meta[$identifier] == true ) {

                                    if( ! $value ) {
                                        $value  = [];
                                    }
                                    $value[]    = $choice;
                                } elseif( $identifier == $meta ) {
                                    $value      = $choice;    
                                }
                            } 
    
                            break;
                    }
    
                    if( is_array($value) ) {
                        $value = implode(', ', $value);
                    }                    

                    if( $value ) {
                        $this->props['tabs'][$key]['content'][] = ['label' => $attribute['name'], 'value' => $value];
                    }
                
                }

            }

        }
        
    }

}