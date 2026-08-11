<?php
/**
 * Boots our plugin and makes it's internal data accessible
 */
namespace Waterfall_Reviews;
use Waterfall as Waterfall;

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
        $this->modify_rewrites();
        $this->register_rest_meta();

        /**
         * Set-up the updater
         */
        $updater = \MakeitWorkPress\WP_Updater\Boot::instance();
        $updater->add(['source' => 'https://github.com/makeitworkpress/waterfall-reviews', 'type' => 'plugin']);

    }

    /**
     * Modifies to rewrite rules
     */
    private function modify_rewrites() {

        // Remove the attachment review rules, killing our category archives
        add_filter( 'rewrite_rules_array', function($rules) {
            unset($rules['reviews/[^/]+/([^/]+)/?$']);
            return $rules;
        } );

    }

    /**
     * Registers the rating meta field to be accessible over the REST API
     */
    public function register_rest_meta() {

        /** 
         * @param WP_REST_Server $wp_rest_server The WP Rest Server object
         */       
        add_action('rest_api_init', function( $wp_rest_server ) {

            register_post_meta('reviews', 'rating', [
                'show_in_rest'  => true,
                'single'        => true,
                'type'          => 'number'            
            ]);        

        });

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

            $configurations['options']['review_meta']   = $review_meta;
            $configurations['options']['options']       = $options;
        }

        if( is_customize_preview() ) {

            require_once( WFR_PATH . '/config/customizer.php' );
            $configurations['options']['colors_panel']      = $colors_panel;
            $configurations['options']['layout_panel']      = $layout_panel;
            $configurations['options']['typography_panel']  = $typography_panel;

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