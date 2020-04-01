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

        $this->props['class'] = $this->params['view'];

        // Load our properties data into our tabs
        if( isset($this->options['properties']) && $this->options['properties'] ) {

            $this->props['tabs']['properties']['title']     = __('Properties', 'wfr');
            $this->props['tabs']['properties']['content']   = [];

            $this->setTabContent( 'property' );

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
                if( ! isset($this->options[$key . '_attributes'][0]['name']) || ! $this->options[$key . '_attributes'][0]['name'] ) {
                    continue;
                }

                $this->props['tabs'][$key]['title']     = $criteria['name'];
                $this->props['tabs'][$key]['content']   = [];

                $this->setTabContent( 'attribute', $key );

            }

        }
        
    }

    /**
     * Sets the field values for either properties or criteria attributes
     * @param string    $type           The type of property to get data for, either criteria attributes (attribute) or properties
     * @param string    $key            The optional criteria key to look for in the meta fields and optiel fields
     * 
     * @return void
     */
    private function setTabContent( $type = 'attribute', $key = '') {

        // Get our global post object
        global $post;

        // $post Should be set
        if( ! is_object($post) ) {
            return;
        }

        // The correct property should be declared
        if( ! in_array($type, ['attribute', 'property']) ) {
            return;
        }

        // For attributes, the key is also suffixed (indicating the criteria)
        $key   = $key ? $key . '_' : ''; 

        // The option key where we need to get our fields from
        $option = $type == 'attribute' ? 'attributes' : 'properties';

        // We should have fields
        if( ! isset($this->options[$key . $option]) || ! $this->options[$key . $option] ) {
            return;
        }      
  
        // Get our default price and price currency
        $priceCurrency  = get_post_meta($post->ID, 'price_currency', true);
        $priceCurrency  = $priceCurrency ? $priceCurrency : $this->options['review_currency'];
        $priceUnit      = get_post_meta($post->ID, 'price_unit', true);

        /**
         * For each field, get the meta values
         * and subsequently format them correctly.
         */
        foreach( $this->options[$key . $option] as $attribute ) {
                
            $id     = sanitize_key($attribute['name']) . '_';
            $meta   = get_post_meta($post->ID, $id . $key . $type, true); // Key is optional for criteria attributes here
            $value  = '';

            if( $attribute['repeat'] ) {
                if( is_array($meta) ) {
                    $value      = [];
                    foreach( $meta as $plan ) {
                        $planValues = $this->getFieldValues($attribute, $plan['value']);

                        // Only add plans if there is a value
                        if( $planValues ) {
                            $planPrice  = $plan['price'] ? ' <span class="wfr-properties-price">(' . $priceCurrency . $plan['price'] . ' ' . $priceUnit . ')</span>' : '';
                            $planName   = $plan['name'] ? ' - <span class="wfr-properties-plan">' . $plan['name'] . '</span>' : '';
                            $value[]    = $planValues . $planName . $planPrice;
                        }

                    }
                }
            } else {
                $value = $this->getFieldValues($attribute, $meta);
            }

            // Fields that allow for repeat return an array, we split it by line here
            if( is_array($value) ) {
                $value = implode('<br/>', $value);
            }                    

            // In the end, only add the values and thus the tabs if they have a value
            if( $value ) {
                $target = $type == 'attribute' && $key ? str_replace('_', '', $key) : 'properties';
                $this->props['tabs'][$target]['content'][] = ['label' => $attribute['name'], 'value' => $value];
            }
        
        }

    }

    /**
     * Retrieves the values of a given field
     *
     * @param array     $attributes     The field attributes of a saved field
     * @param mixed     $meta           The saved metavalues for a given field
     * 
     * @return string   $value          The formatted value
     */
    private function getFieldValues( $attribute, $meta ) {
        
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

                    // If a key value is seperated through a colon
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