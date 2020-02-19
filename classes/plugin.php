<?php
/**
 * Boots our plugin and makes it's internal data accessible
 */
namespace Waterfall_Reviews;
use Waterfall as Waterfall;
use MakeitWorkPress\WP_Updater as Updater;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Plugin {
   
    /**
     * Determines whether a class has already been instanciated.
     * @access private
     */
    private static $instance = null;

    /**
     * The parent theme instance
     * @access private
     */
    private $parent = null;    

    /**
     * Initial constructor
     */
    private function __construct() {}

    /**
     * Retrieve and return the single instance
     */
    public static function instance() {
        
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->launch();
        }

        return self::$instance;
        
    } 
    
    /**
     * Launches our plugin by loading and applying the configurations
     */
    private function launch() {

        // Load utility functions
        require_once( WFR_PATH . '/functions/utilities.php' );

        // Hook configurations, just before the main theme does
        add_action('after_setup_theme', [$this, 'setup'], 5);

        /**
         * Some additional hooks
         */     

        // Remove the attachment review rules, killing our category archives
        add_filter( 'rewrite_rules_array', function($rules) {
            unset($rules['reviews/[^/]+/([^/]+)/?$']);
            return $rules;
        } );

        /**
         * Set-up the updater
         */
        new Updater\Boot(['source' => 'https://github.com/makeitworkpress/waterfall-reviews', 'type' => 'plugin']);

    }

    /**
     * Loads our configurations
     */
    public function setup() {

        // Loads our parent instance
        $this->parent = Waterfall::instance();  
     
        // Launch the various modules of our plugin
        $modules = [
            'Waterfall_Reviews\Ajax', 
            'Waterfall_Reviews\Score',
            'Waterfall_Reviews\Views\Shortcodes', 
            'Waterfall_Reviews\Views\Archive_Reviews', 
            'Waterfall_Reviews\Views\Single_Reviews'
        ];

        foreach( $modules as $module ) {

            if( class_exists($module) ) {
                new $module();
            }
            
        }        

        // Load our general configurations
        require_once( WFR_PATH . '/config/general.php' );

        // Some configurations only load in certain contexts
        if( is_admin() ) {
            require_once( WFR_PATH . '/config/meta.php' );
            require_once( WFR_PATH . '/config/options.php' );

            $configurations['options']['reviewMeta']    = $reviewMeta;
            $configurations['options']['options']       = $options;
        }

        if( is_customize_preview() ) {

            require_once( WFR_PATH . '/config/customizer.php' );
            $configurations['options']['colorsPanel']       = $colorsPanel;
            $configurations['options']['layoutPanel']       = $layoutPanel;
            $configurations['options']['typographyPanel']   = $typographyPanel;

        }

        $configurations = apply_filters('waterfall_reviews_configurations', $configurations);

        // Only a set of predefined configurations can be added
        foreach( $configurations as $name => $values ) {

            if( ! in_array($name, ['register', 'options', 'enqueue', 'elementor']) ) {
                continue;
            } 

            $this->parent->config->add( $name, $values );

        }

    }

}