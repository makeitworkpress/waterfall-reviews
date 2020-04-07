<?php
/**
 * Contains the class abstraction for our components
 */

namespace Waterfall_Reviews\Views\Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

abstract class Component {

    /**
     * Contains our custom meta fields for the given post
     * @access protected
     */
    protected $fields = [];    
    
    /**
     * Contains our customizer layout options
     * @access protected
     */
    protected $layout;

    /**
     * Contains our general options
     * @access protected
     */
    protected $options = [];     

    /**
     * Contains the parameters for a component
     * @access protected
     */
    public $params = []; 

    /**
     * Contains the public properties, used in the template
     * @access public
     */
    public $props = [];

    /**
     * Contains the template
     * @access protected
     */
    protected $template = '';    
    
    /**
     * Set up our parameters and component
     * 
     * @param array     $params     The parameters for our material grid
     * @param boolean   $format     If we want to query and format by default
     * @param boolean   $render     If we want to render by default
     */
    public function __construct( $params = [], $format = true, $render = false ) {  
        
        $this->customizer   = wf_get_theme_option('customizer');
        $this->layout       = wf_get_theme_option('layout');
        $this->options      = wf_get_theme_option();
        $this->params       = $params;

        $this->initialize();     

        // If format is true, we query and format
        if( $format ) {
            $this->query();
        }

        // If render is true, we render by default
        if( $render && $this->props ) {
            $this->render();
        }

    } 
    
    /**
     * This function initializes our components, sets it paramenters
     */
    abstract protected function initialize();      

    /**
     * This function should be present at children - it queries components data from the database and formates its for use
     */
    abstract protected function query();  
    

    /**
     * Renders a component
     * 
     * @param boolean   $return     If we return the given template instead of rendering it
     */
    public function render( $return = false ) {

        // We should have a template defined in our child class. Otherwise, we do not know what to render.
        if( ! $this->template ) {
            return;
        }

        // Developers can hook into the template and use their own
        $file = apply_filters( 'wfr_components_template_' . $this->template, WFR_PATH . '/templates/components/' . $this->template . '.php' );

        if( ! file_exists($file) ) {
            return;
        }

        // Cast our object properties into the template variable, so they are accessible by the template file. Developers can hook into our properties
        ${$this->template} = apply_filters( 'wfr_components_props_' . $this->template, $this->props );

        // Return if we have empty props
        if( ! ${$this->template} ) {
            return;
        }
        
        if( $return ) {
            ob_start();
        }
        
        require( $file );

        if( $return ) {
            return ob_get_clean();
        }

    }
    
    /**
     * Gets the values for our custom fields and transforms them into an array with labels,
     * so they can be used by components
     * 
     * @param   Float   $post The id for the given post
     * @param   Array   $groups Only return values from these groups (use sanitized criteria names, 'properties' or 'general')
     * @param   Array   $properties Only return values from these properties
     * @param   Array   $attributes Only return values from these attributes
     * @param   Boolean $weighted If weighted values should be returned as well
     * 
     * @return void
     */
    protected function setCustomFields( $post, $weighted = false, $groups = [], $attributes = [], $properties = []) {

        // $post Should be set and an integer
        if( ! is_int($post) ) {
            return;
        } 

        $labels = ['general' => __('General', 'wfr'), 'properties' => __('Properties', 'wfr')];
        
        // Define our groups if they are not set by default
        if( ! $groups ) {

            $groups = ['general', 'properties'];

            if( isset($this->options['rating_criteria']) && $this->options['rating_criteria'] ) { 
                foreach( $this->options['rating_criteria'] as $criteria ) {
                    $key            = isset($criteria['key']) && $criteria['key'] ? sanitize_key($criteria['key']) : sanitize_key($criteria['name']);
                    $groups[]       = $key;
                    $labels[$key]   = sanitize_text_field($criteria['name']);
                }
            }

        }

        foreach( $groups as $group ) {

            if( ! isset($this->fields[$group]) ) {
                $this->fields[$group] = [
                    'label'     => $labels[$group],
                    'fields'    => []    
                ];
            }

            /**
             * General groups
             */
            if( $group == 'general' ) {


            /**
             * Dynamic groups (properties and criteria)
             */    
            } else {

                $gkey = $group == 'properties' ? $group : $group . '_attributes';

                // This group doesn't have any options yet
                if( ! isset($this->options[$gkey]) || ! array_filter($this->options[$gkey]) ) {
                    continue;
                }

                // Get our default price, price unit and price currency
                $priceCurrency  = get_post_meta($post, 'price_currency', true);
                $priceCurrency  = $priceCurrency ? $priceCurrency : $this->options['review_currency'];
                $priceUnit      = get_post_meta($post, 'price_unit', true);
                $price          = get_post_meta($post, 'price', true);  
                
                foreach( $this->options[$gkey] as $field ) {
                    $ftype  = $group == 'properties' ? '_property' : '_' . $group . '_attribute'; 
                    $fkey   = isset($field['key']) && $field['key'] ? sanitize_key($field['key']) : sanitize_key($field['name']) . $ftype;

                    $meta   = get_post_meta($post, $fkey, true);

                    if( $attribute['repeat'] && is_array($meta) ) {
                        $value      = [];
                        foreach( $meta as $plan ) {
                            $planValues = $this->getFieldValues( $attribute, $plan['value'], $plan['price'], $weighted );
    
                            // Only add plans if there is a value
                            if( $planValues ) {
                                $planPrice  = $plan['price'] ? ' <span class="wfr-fields-price">(' . esc_html($priceCurrency) . $plan['price'] . ' ' . esc_html($priceUnit) . ')</span>' : '';
                                $planName   = $plan['name'] ? ' <span class="wfr-fields-plan"> - ' . $plan['name'] . '</span>' : '';
                                $value[]    = esc_html($planValues) . $planName . $planPrice;
                            }
    
                        }                        
                    } else {
                        $value = $this->getFieldValues( $attribute, $meta, $price, $weighted );
                    }

                    if( is_array($value) ) {
                        $value = implode( '<br/>', array_filter($value) );
                    }
                    
                    if( $value ) {

                        if( ! isset($this->fields[$group]['fields'][$fkey]) ) {
                            $this->fields[$group]['fields'][$fkey] = [
                                'label'     => esc_html($attribute['name']), 
                                'values'    => []
                            ];
                        }

                        $this->fields[$group]['fields'][$fkey]['values'][$post] = esc_html($value);

                    }

                }

            }

            /**
             * If we don't have any fields for all the groups, unset it
             */
            if( ! $this->fields[$group]['fields'] ) {
                unset($this->props[$group]);
            }

        }

    } 
    
    /**
     * Retrieves the values of a given field
     *
     * @param array     $attributes     The field attributes of a saved field
     * @param mixed     $meta           The saved metavalues for a given field
     * @param mixed     $price          The price for the given field
     * @param boolean   $weighted       If you want to return weighted values
     * 
     * 
     * @return string   $value          The formatted value
     */
    private function getFieldValues( $attribute, $meta, $price = false, $weighted = false ) {
        
        switch( $attribute['type'] ) {
            case 'input':
            case 'number':
            case 'textarea':
                $value = $meta;

                if( $weighted && is_numeric($value) && is_numeric($price) ) {
                    $value .= '<span class="wfr-fields-weighted">' . floatval($value/$price) . ' ' . __('(Price Weighted)', 'wfr') . '</span>';
                }

                break;
            case 'checkbox':
            case 'select':
                $value  = '';
                $values = array_filter( explode(',', $attribute['values']) );
                
                foreach( $values as $choice ) {

                    $identifier     = sanitize_key($choice);

                    // If a key value is seperated through a colon, we have to find the label and the value
                    if( strpos($choice, ':') ) {
                        $divide     = explode(':', $choice);
                        $identifier = sanitize_text_field($divide[0]);
                        $choice     = $divide[1];
                    }
                    
                    // If multiple options are checked
                    if( is_array($meta) && $meta[$identifier] == true ) {

                        if( ! $value ) {
                            $value  = [];
                        }

                        $value[]    = $choice;

                    // If it is just a single option, being checked
                    } elseif( $identifier == $meta ) {
                        $value      = $choice;    
                    }
                } 

                // Array values are comma seperated
                if( is_array($value) ) {
                    $value = implode( ', ', $value );
                }

                break;
            default:
                $value = '';
        }

        return $value;

    }    

}