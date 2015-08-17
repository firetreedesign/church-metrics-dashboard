<?php
/**
 * Plugin Name: Church Metrics Dashboard
 * Description: Allows you to create Dashboard Widgets to display data from Church Metrics.
 * Author: FireTree Design, LLC <info@firetreedesign.com>
 * Author URI: https://firetreedesign.com/
 * Version: 1.2.0
 * Plugin URI: https://firetreedesign.com/
 */

// Include the Church Metrics Dashboard class
require_once( plugin_dir_path( __FILE__ ) . 'lib/Church_Metrics_Dashboard_Class.php' );

new Church_Metrics_Dashboard();