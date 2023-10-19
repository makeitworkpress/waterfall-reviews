<?php
/*
Plugin Name:  Waterfall Reviews
Plugin URI:   https://www.makeitwork.press/wordpress-plugins/waterfall-reviews/
Description:  The Waterfall Reviews plugin turns your Waterfall WordPress theme into a killer-review site. Works great with ElasticPress and Elementor too.
Version:      0.2.7
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
    
    $called_class       = str_replace( '\\', '/', str_replace( '_', '-', $class_name ) );
    
    $class_names        = explode( '/', str_replace( 'Waterfall-Reviews/', '', $called_class) );
    $final_class        = array_pop($class_names);
    $class_rel_path     = $class_names ? implode('/', $class_names) . '/class-' . $final_class : 'class-' . $final_class;
    $class_file         = dirname(__FILE__) .  '/classes/' . strtolower( $class_rel_path ) . '.php';

    if( file_exists($class_file) ) {
        require_once( $class_file );
        return;
    }
        
    // Require Vendor (composer) classes
    if( ! isset($class_names[0]) || $class_names[0] !== 'MakeitWorkPress' || ! isset($class_names[1]) ) {
        return;
    }

    array_splice($class_names, 2, 0, 'src');
    $class_names[0] = strtolower($class_names[0]);
    $class_names[1] = strtolower($class_names[1]);
    $vendor_class_file  = dirname(__FILE__) . '/vendor/' . implode('/', $class_names) . '/' . $final_class . '.php';

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