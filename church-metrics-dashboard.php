<?php
/**
 * Plugin Name: Church Metrics Dashboard
 * Description: Allows you to create Dashboard Widgets to display data from Church Metrics.
 * Author: FireTree Design, LLC <info@firetreedesign.com>
 * Author URI: https://firetreedesign.com/
 * Version: 1.0.0
 * Plugin URI: https://firetreedesign.com/
 */

// Setup the Church Metrics class
require_once( plugin_dir_path( __FILE__ ) . 'lib/WP_Church_Metrics_Class.php' );

// Setup the Church Metrics custom fields
require_once( plugin_dir_path( __FILE__ ) . 'lib/cmb2.php' );
 
// Include the Extended Post Type helper
require_once( plugin_dir_path( __FILE__ ) . 'lib/extended-cpts/extended-cpts.php' ); 

// Setup the Church Metrics custom post type
require_once( plugin_dir_path( __FILE__ ) . 'inc/cpt-cm_dash_widgets.php' );

// Setup the Church Metrics Dashboard Widgets
require_once( plugin_dir_path( __FILE__ ) . 'inc/dashboard.php' );

// Include the helper functions
require_once( plugin_dir_path( __FILE__ ) . 'inc/helper-functions.php' );

// Setup the settings page
require_once( plugin_dir_path( __FILE__ ) . 'inc/cpt-cm_dash_widgets-settings.php' );

// Disabled the [Add New] button if the API is not set up
add_action( 'load-post-new.php', 'cm_dash_widgets_disable_new_post' );
function cm_dash_widgets_disable_new_post() {
    
    if ( get_current_screen()->post_type == 'cm_dash_widgets' ) {
	    
	    $user = cm_dash_widgets_get_option('user');
	    $key = cm_dash_widgets_get_option('key');
	    
	    if ( $user == '' || $key == '' ) {
		    wp_die( "You must first setup the Church Metrics API." );
	    }
	    
    }
        
}

// Enqueue the Admin stylesheet only on the Dashboard page
function cm_dash_widgets_admin_styles($hook) {
    if ( 'index.php' != $hook ) {
        return;
    }

    wp_register_style( 'cm_dash_widgets_admin_css', plugin_dir_url( __FILE__ ) . 'css/admin.css' );
    wp_enqueue_style( 'cm_dash_widgets_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'cm_dash_widgets_admin_styles' );