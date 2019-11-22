<?php
/*
Plugin Name:  Waterfall Reviews
Plugin URI:   https://www.makeitworkpress/wordpress-plugins/waterfall-directory/
Description:  The Waterfall Reviews plugin turns your Waterfall WordPress theme into a killer-review site. Works great with ElasticPress and Elementor too.
Version:      0.1
Author:       Make it WorkPress
Author URI:   https://www.makeitwork.press/
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
 * Registers the autoloading for classes and our vendors within the waterfall reviews plugin
 */
spl_autoload_register( function($classname) {

    $class      = str_replace( ['\\', 'waterfall-reviews/'], [DIRECTORY_SEPARATOR, ''], str_replace( '_', '-', strtolower($classname) ) );
    $classes    = dirname(__FILE__) .  DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $class . '.php';
    
    $theme      = str_replace( '\\', DIRECTORY_SEPARATOR, str_replace( '_', '-', strtolower($classname) ) );
    $theme      = get_template_directory() .  DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $theme . '.php';
    
    $vendor     = str_replace( 'makeitworkpress' . DIRECTORY_SEPARATOR, '', $class );
    $vendor     = 'makeitworkpress' . DIRECTORY_SEPARATOR . preg_replace( '/\//', '/src/', $vendor, 1 ); // Replace the first slash for the src folder
    $vendors    = get_template_directory() .  DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . $vendor . '.php';

    if( file_exists($classes) ) {
        require_once( $classes );
    } if( file_exists($theme) ) {
        require_once( $theme );
    } elseif( file_exists($vendors) ) {
        require_once( $vendors );    
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