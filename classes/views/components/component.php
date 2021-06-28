<?php
/**
 * Contains the class abstraction for our components
 */

namespace Waterfall_Reviews\Views\Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

abstract class Component {  
    
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
        
        $this->customizer   = wf_get_data('customizer');
        $this->layout       = wf_get_data('layout');
        $this->options      = wf_get_data();
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
    public function render( $render = true ) {

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
        
        if( ! $render ) {
            ob_start();
        }
        
        require( $file );

        if( ! $render ) {
            return ob_get_clean();
        }

    }   

}