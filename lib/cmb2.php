<?php
/**
 * Get the bootstrap!
 */
if ( file_exists(  __DIR__ . '/cmb2/init.php' ) ) {
	require_once  __DIR__ . '/cmb2/init.php';
} elseif ( file_exists(  __DIR__ . '/CMB2/init.php' ) ) {
	require_once  __DIR__ . '/CMB2/init.php';
}

require_once __DIR__ . '/cmb-field-select2/cmb-field-select2.php';

add_action( 'cmb2_init', 'cm_dash_widgets_metaboxes' );

/**
 * Define the metabox and field configurations.
 */
function cm_dash_widgets_metaboxes() {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_cm_dash_widgets_';

    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id'            => 'cm_dash_widgets_metabox',
        'title'         => __( 'Dashboard Widget Settings', 'church-metrics-dashboard' ),
        'object_types'  => array( 'cm_dash_widgets', ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
    ) );

    $cmb->add_field( array(
	    'name'             => __( 'Campus', 'church-metrics-dashboard' ),
	    'desc'             => __( 'Select a campus', 'church-metrics-dashboard' ),
	    'id'               => $prefix . 'campus',
	    'type'             => 'pw_select',
	    'show_option_none' => false,
	    'options'          => 'cm_dash_widgets_campus_field',
	) );
	
	$cmb->add_field( array(
	    'name'             => __( 'Category', 'church-metrics-dashboard' ),
	    'desc'             => __( 'Select a category', 'church-metrics-dashboard' ),
	    'id'               => $prefix . 'category',
	    'type'             => 'pw_select',
	    'show_option_none' => false,
	    'options'          => 'cm_dash_widgets_category_field',
	) );
	
	$cmb->add_field( array(
	    'name'             => __( 'Event', 'church-metrics-dashboard' ),
	    'desc'             => __( 'Select an event (optional)', 'church-metrics-dashboard' ),
	    'id'               => $prefix . 'event',
	    'type'             => 'pw_select',
	    'show_option_none' => true,
	    'options'          => 'cm_dash_widgets_event_field',
	) );
	
	$cmb->add_field( array(
	    'name'		=> __( 'Display data as', 'church-metrics-dashboard' ),
	    'desc'		=> __( 'Select a display type', 'church-metrics-dashboard' ),
	    'id'		=> $prefix . 'display',
	    'type'		=> 'pw_select',
	    'default'	=> 'numerical',
	    'show_option_none' => false,
	    'options'	=> array(
	        'numerical'	=> __( 'Numerical', 'church-metrics-dashboard' ),
	        //'chart'		=> __( 'Chart', 'church-metrics-dashboard' ),
	    ),
	) );
	
	$cmb->add_field( array(
	    'name'				=> __( 'Display Period', 'church-metrics-dashboard' ),
	    'desc'				=> __( 'Select a display period', 'church-metrics-dashboard' ),
	    'id'				=> $prefix . 'display_period',
	    'type'				=> 'pw_select',
	    'default'			=> 'this_week',
	    'show_option_none'	=> false,
	    'options'			=> array(
	    	'this week'		=> __( 'This Week', 'church-metrics-dashboard' ),
	    	'last week'		=> __( 'Last Week', 'church-metrics-dashboard' ),
	    	'this month'	=> __( 'This Month', 'church-metrics-dashboard' ),
	    	'last month'	=> __( 'Last Month', 'church-metrics-dashboard' ),
	    	'this year'		=> __( 'This Year', 'church-metrics-dashboard' ),
	    	'last year'		=> __( 'Last Year', 'church-metrics-dashboard' ),
	    	'all_time'		=> __( 'All Time', 'church-metrics-dashboard' ),
	    ),
	) );
	
	$cmb->add_field( array(
	    'name'				=> __( 'Compare To', 'church-metrics-dashboard' ),
	    'desc'				=> __( 'Select something to compare to', 'church-metrics-dashboard' ),
	    'id'				=> $prefix . 'compare_period',
	    'type'				=> 'pw_select',
	    'default'			=> 'none',
	    'show_option_none'	=> false,
	    'options'			=> array(),
	) );
	
	$cmb->add_field( array(
	    'name'             => __( 'Visible To User', 'church-metrics-dashboard' ),
	    'desc'             => __( 'Select a user (optional)', 'church-metrics-dashboard' ),
	    'id'               => $prefix . 'visibility_user',
	    'type'             => 'pw_multiselect',
	    'default'			=> 'all',
	    'show_option_none' => false,
	    'options'          => 'cm_dash_widgets_visibility_user_field',
	) );

}

/**
 * Callback for populating the Campus field
 */

function cm_dash_widgets_campus_field( $field ) {
	
	// Define our Church Metrics arguments
	$args = array(
		'user'	=> cm_dash_widgets_get_option('user'),
		'key'	=> cm_dash_widgets_get_option('key'),
	);
	
	// Initialize the Church Metrics API class
	$cm = new WP_Church_Metrics( $args );
	
	// Request the campuses from the API
	$cm_request = $cm->campuses();
	
	// Get the body from the response
	$cm_body = json_decode( wp_remote_retrieve_body( $cm_request ) );
	
	// Define our array
	$cm_array = array();
	
	// Loop through the records and populate our array
	foreach( $cm_body as $item ) {
		$cm_array[ $item->id ] = $item->slug;
	}
	
	// Return the array
	return $cm_array;
	
}

/**
 * Callback for populating the Category field
 */

function cm_dash_widgets_category_field( $field ) {
	
	// Define our Church Metrics arguments
	$args = array(
		'user'	=> cm_dash_widgets_get_option('user'),
		'key'	=> cm_dash_widgets_get_option('key'),
	);
	
	// Initialize the Church Metrics API class
	$cm = new WP_Church_Metrics( $args );
	
	// Request the categories from the API
	$cm_request = $cm->categories();
	
	// Get the body from the response
	$cm_body = json_decode( wp_remote_retrieve_body( $cm_request ) );
	
	// Define our array
	$cm_array = array();
	
	// Loop through the records and populate our array
	foreach( $cm_body as $item ) {
		$cm_array[ $item->id ] = $item->name;
	}
	
	// Return the array
	return $cm_array;
	
}

/**
 * Callback for populating the Event field
 */

function cm_dash_widgets_event_field( $field ) {
	
	// Define our Church Metrics arguments
	$args = array(
		'user'	=> cm_dash_widgets_get_option('user'),
		'key'	=> cm_dash_widgets_get_option('key'),
	);
	
	// Initialize the Church Metrics API class
	$cm = new WP_Church_Metrics( $args );
	
	// Request the categories from the API
	$cm_request = $cm->events();
	
	// Get the body from the response
	$cm_body = json_decode( wp_remote_retrieve_body( $cm_request ) );
	
	// Define our array
	$cm_array = array();
	
	// Loop through the records and populate our array
	foreach( $cm_body as $item ) {
		$cm_array[ $item->id ] = $item->name;
	}
	
	// Return the array
	return $cm_array;
	
}

/**
 * Callback for populating the Visibility field
 */

function cm_dash_widgets_visibility_user_field( $field ) {
	
	// Define our array
	$options = array();
	$options['all'] = __( 'All Users', 'church-metrics-dashboard' );
	
	// Retrieve all of the users
	$users = get_users();
	
	// Loop through the users and populate our array
	foreach ( $users as $user ) {
		$options[ $user->ID ] = $user->display_name;
	}
	
	// Return the array
	return $options;
	
}

/**
 * Filter to populate the compare_period field with grouped options
 */

function cm_dash_widgets_cmb_opt_groups( $args, $defaults, $field_object, $field_types_object ) {
	
	// Only do this for the field we want (vs all select fields)
	if ( '_cm_dash_widgets_compare_period' != $field_types_object->_id() ) {
		return $args;
	}
	
	// Define our options
	$option_array = array(
		__( 'This Week', 'church-metrics-dashboard' ) => array(
			'last week' => __( 'Last Week', 'church-metrics-dashboard' ),
			'this week last year' => __( 'This Week Last Year', 'church-metrics-dashboard' ),
		),
		__( 'Last Week', 'church-metrics-dashboard' ) => array(
			'last week last year' => __( 'Last Week Last Year', 'church-metrics-dashboard' ),
		),
		__( 'This Month', 'church-metrics-dashboard' ) => array(
			'last month' => __( 'Last Month', 'church-metrics-dashboard' ),
			'this month last year' => __( 'This Month Last Year', 'church-metrics-dashboard' ),
		),
		__( 'Last Month', 'church-metrics-dashboard' ) => array(
			'last month last year' => __( 'Last Month Last Year', 'church-metrics-dashboard' ),
		),
		__( 'This Year', 'church-metrics-dashboard' ) => array(
			'last year' => __( 'Last Year', 'church-metrics-dashboard' ),
		),
	);
	
	$saved_value	= $field_object->escaped_value();
	$value			= $saved_value ? $saved_value : $field_object->args( 'default' );
	$options_string	= '';
	$options_string	.= $field_types_object->select_option( array(
		'label'		=> __( 'Nothing', 'church-metrics-dashboard' ),
		'value'		=> 'nothing',
		'checked'	=> ! $value
	));
	
	foreach ( $option_array as $group_label => $group ) {
		
		$options_string .= '<optgroup label="'. $group_label .'">';
		
		foreach ( $group as $key => $label ) {
			
			$options_string .= $field_types_object->select_option( array(
				'label'		=> $label,
				'value'		=> $key,
				'checked'	=> $value == $key
			) );
			
		}
		
		$options_string .= '</optgroup>';
	}
	
	// Ok, replace the options value
	$defaults['options'] = $options_string;
	
	return $defaults;
}
add_filter( 'cmb2_select_attributes', 'cm_dash_widgets_cmb_opt_groups', 10, 4 );