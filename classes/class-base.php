<?php
/**
 * Defines the base for plugin classes that executes hooks
 */
namespace Waterfall_Reviews;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

abstract class Base {

    /**
     * Contains our actions
     * @access protected
     */
    protected $actions = [];  

    /**
     * Contains the array of default properties that should be checked for by child view classes. Should be filled within the register method.
     * @access protected
     */
    protected $defaults = [];       

    /**
     * Contains our filters
     * @access protected
     */
    protected $filters = [];

    /**
     * Contains our customizer layout data
     * @access protected
     */
    protected $layout = [];

    /**
     * Contains our option data
     * @access protected
     */
    protected $options = [];    

    public function __construct() {

        $this->layout       = get_theme_mod('waterfall_layout');
        $this->options      = get_option('waterfall_options');

        $this->register();

        // Fill our default lay-out properties if none are saved yet
        foreach( $this->defaults as $property => $value ) {
            if( ! isset($this->layout[$property]) ) {
                $this->layout[$property] = $value;
            }
        }
        
        $this->apply_hooks( $this->actions );
        $this->apply_hooks( $this->filters, 'filter' );

    }

    /**
     * Applies filters and actions
     */
    private function apply_hooks( Array $array, $type = 'action' ) {

        foreach( $array as $action ) {

            if( ! is_array($action) ) {
                continue;
            }

            // We also support numerical keys
            foreach( ['hook' => 0, 'method' => 1, 'priority' => 2, 'arguments' => 3] as $argument => $key ) {
                if( isset($action[$key]) ) {
                    $action[$argument] = $action[$key];
                    unset($action[$key]);
                }
            }

            // Obviously, our action hook and method should be defined.
            if( ! isset($action['hook']) || ! isset($action['method']) ) {
                continue;
            }

            // Our method should exist.
            if( ! method_exists($this, $action['method']) ) {
                continue;
            }

            // Set our additional arguments
            $action['priority']  = isset($action['priority']) ? $action['priority'] : 10;
            $action['arguments'] = isset($action['arguments']) ? $action['arguments'] : 1;

            if( $type == 'action' ) {
                add_action( $action['hook'], [$this, $action['method']], $action['priority'], $action['arguments'] );
            } elseif( $type == 'filter' ) {
                add_filter( $action['hook'], [$this, $action['method']], $action['priority'], $action['arguments'] );   
            }

        }

    }

    /**
     *  Child classes can use this function to register their hooks and subsequent actions
     */
    abstract protected function register();

}