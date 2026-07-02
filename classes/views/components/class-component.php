<?php
/**
 * Contains the class abstraction for our components
 */
namespace Waterfall_Reviews\Views\Components;
use ReflectionClass as ReflectionClass;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

abstract class Component {

    /**
     * Contains the called class
     * @access private
     */
    private $class;
    
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
    private $template = '';
    
    /**
     * Set up our parameters and component
     * 
     * @param array     $params     The parameters for our material grid
     * @param boolean   $format     If we want to query and format by default
     * @param boolean   $render     If we want to render by default
     */
    final public function __construct( $params = [], $format = true, $render = false ) {  
        
        $this->customizer   = wf_get_data('customizer');
        $this->layout       = wf_get_data('layout');
        $this->options      = wf_get_data();
        $this->params       = $params;

        $this->initialize();     

        // If format is true, we query and format
        if( $format ) {
            $this->query();
        }

        $this->class    = strtolower( (new ReflectionClass($this))->getShortName() );
        $this->template = apply_filters( 'wfr_components_template_' . $this->class, WFR_PATH . '/templates/components/' . $this->class  . '.php');
        $this->props    = apply_filters( 'wfr_components_props_' . $this->class, $this->props);        

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

        if( ! $this->props ) {
            return;
        }        

        if( ! file_exists($this->template) ) {
            return;
        }

        // Cast our object properties into the class variable, so they are accessible by the template file under the class name 
        ${$this->class} = $this->props;
        
        if( ! $render ) {
            ob_start();
        }
        
        require( $this->template );

        if( ! $render ) {
            return ob_get_clean();
        }

    }   

}