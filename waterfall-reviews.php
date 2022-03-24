<?php
/*
Plugin Name:  Waterfall Reviews
Plugin URI:   https://www.makeitwork.press/wordpress-plugins/waterfall-reviews/
Description:  The Waterfall Reviews plugin turns your Waterfall WordPress theme into a killer-review site. Works great with ElasticPress and Elementor too.
Version:      0.2.1
Author:       Make it WorkPress
Author URI:   https://makeitwork.press/
License:      GPL3
License URI:  https://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * First, we have to check if the waterfall template is being used
 * If it is not used, we return an error in the admin area, indicating that the theme should be present.
 */
$theme = wp_get_theme();

if( $theme->template != 'waterfall' ) {
    add_action( 'admin_notices', function() {
        echo '<div class="error"><p>' . __('The Waterfall theme is not present or activated. The Waterfall Reviews plugin requires the Waterfall theme to function.', 'wfr') . '</p></div>';      
    });     
    return;
}

/**
 * Registers the autoloading for plugin classes
 */
spl_autoload_register( function($class_name) {
    
    $called_class   = str_replace( '\\', '/', str_replace('_', '-', strtolower($class_name)) );
    
    // Plugin Classes
    $plugin_spaces  = explode( '/', str_replace( 'waterfall-reviews/', '', $called_class) );
    $final_class    = array_pop($plugin_spaces);
    $class_rel_path = $plugin_spaces ? implode('/', $plugin_spaces) . '/class-' . $final_class : 'class-' . $final_class;
    $class_file     = dirname(__FILE__) .  '/classes/' . $class_rel_path . '.php';
    
    if( file_exists($class_file) ) {
        require_once( $class_file );
        return;
    }

    // Require Vendor (composer) classes
    array_splice($plugin_spaces, 2, 0, 'src');
    $vendor_class_file  = dirname(__FILE__) . '/vendor/' . implode(DIRECTORY_SEPARATOR, $plugin_spaces) . '/' . $final_class . '.php';

    if( file_exists($vendor_class_file) ) {
        require_once( $vendor_class_file );    
    }    
   
} );

/**
 * Boots our plugin
 */
add_action( 'plugins_loaded', function() {
    defined( 'WFR_PATH' ) or define( 'WFR_PATH', plugin_dir_path( __FILE__ ) );
    defined( 'WFR_URI' ) or define( 'WFR_URI', plugin_dir_url( __FILE__ ) );

    $plugin = Waterfall_Reviews\Plugin::instance();
} );