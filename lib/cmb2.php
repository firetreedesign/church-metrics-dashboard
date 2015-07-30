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
        'title'         => __( 'Dashboard Widget Settings', 'cm-dash-widgets' ),
        'object_types'  => array( 'cm_dash_widgets', ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
    ) );

    $cmb->add_field( array(
	    'name'             => __( 'Campus', 'cm-dash-widgets' ),
	    'desc'             => __( 'Select a campus', 'cm-dash-widgets' ),
	    'id'               => $prefix . 'campus',
	    'type'             => 'pw_select',
	    'show_option_none' => false,
	    'options'          => 'cm_dash_widgets_campuses_field',
	) );
	
	$cmb->add_field( array(
	    'name'             => __( 'Category', 'cm-dash-widgets' ),
	    'desc'             => __( 'Select a category', 'cm-dash-widgets' ),
	    'id'               => $prefix . 'category',
	    'type'             => 'pw_select',
	    'show_option_none' => false,
	    'options'          => 'cm_dash_widgets_category_field',
	) );
	
	$cmb->add_field( array(
	    'name'             => __( 'Event', 'ft-church-metrics' ),
	    'desc'             => __( 'Select an event (optional)', 'cm-dash-widgets' ),
	    'id'               => $prefix . 'event',
	    'type'             => 'pw_select',
	    'show_option_none' => true,
	    'options'          => 'cm_dash_widgets_event_field',
	) );
	
	$cmb->add_field( array(
	    'name'		=> __( 'Display data as', 'cm-dash-widgets' ),
	    'desc'		=> __( 'Select a display type', 'cm-dash-widgets' ),
	    'id'		=> $prefix . 'display',
	    'type'		=> 'pw_select',
	    'default'	=> 'numerical',
	    'show_option_none' => false,
	    'options'	=> array(
	        'numerical'	=> __( 'Numerical', 'cm-dash-widgets' ),
	        //'chart'		=> __( 'Chart', 'cm-dash-widgets' ),
	    ),
	) );
	
	$cmb->add_field( array(
	    'name'				=> __( 'Display Period', 'cm-dash-widgets' ),
	    'desc'				=> __( 'Select a display period', 'cm-dash-widgets' ),
	    'id'				=> $prefix . 'display_period',
	    'type'				=> 'pw_select',
	    'default'			=> 'this_week',
	    'show_option_none'	=> false,
	    'options'			=> array(
	    	'this week'		=> __( 'This Week', 'cm-dash-widgets' ),
	    	'last week'		=> __( 'Last Week', 'cm-dash-widgets' ),
	    	'this month'	=> __( 'This Month', 'cm-dash-widgets' ),
	    	'last month'	=> __( 'Last Month', 'cm-dash-widgets' ),
	    	'this year'		=> __( 'This Year', 'cm-dash-widgets' ),
	    	'last year'		=> __( 'Last Year', 'cm-dash-widgets' ),
	    	'all_time'		=> __( 'All Time', 'cm-dash-widgets' ),
	    ),
	) );
	
	$cmb->add_field( array(
	    'name'				=> __( 'Compare To', 'cm-dash-widgets' ),
	    'desc'				=> __( 'Select something to compare to', 'cm-dash-widgets' ),
	    'id'				=> $prefix . 'compare_period',
	    'type'				=> 'pw_select',
	    'default'			=> 'none',
	    'show_option_none'	=> false,
	    'options'			=> array(),
	) );
	
	$cmb->add_field( array(
	    'name'             => __( 'Visible To User', 'cm-dash-widgets' ),
	    'desc'             => __( 'Select a user (optional)', 'cm-dash-widgets' ),
	    'id'               => $prefix . 'visibility_user',
	    'type'             => 'pw_select',
	    'default'			=> 'all',
	    'show_option_none' => false,
	    'options'          => 'cm_dash_widgets_visibility_user_field',
	) );

}

function cm_dash_widgets_campuses_field( $field ) {
	
	$args = array(
		'user'	=> cm_dash_widgets_get_option('user'),
		'key'	=> cm_dash_widgets_get_option('key'),
	);
	
	$cm = new WP_Church_Metrics( $args );
	
	$cm_request = $cm->campuses();
	$cm_body = wp_remote_retrieve_body( $cm_request );
	$cm_body = json_decode( $cm_body );
	
	$cm_array = array();
	
	foreach( $cm_body as $item ) {
		$cm_array[ $item->id ] = $item->slug;
	}
	
	return $cm_array;
	
}

function cm_dash_widgets_category_field( $field ) {
	
	$args = array(
		'user'	=> cm_dash_widgets_get_option('user'),
		'key'	=> cm_dash_widgets_get_option('key'),
	);
	
	$cm = new WP_Church_Metrics( $args );
	
	$cm_request = $cm->categories();
	$cm_body = wp_remote_retrieve_body( $cm_request );
	$cm_body = json_decode( $cm_body );
	
	$cm_array = array();
	
	foreach( $cm_body as $item ) {
		$cm_array[ $item->id ] = $item->name;
	}
	
	return $cm_array;
	
}

function cm_dash_widgets_event_field( $field ) {
	
	$args = array(
		'user'	=> cm_dash_widgets_get_option('user'),
		'key'	=> cm_dash_widgets_get_option('key'),
	);
	
	$cm = new WP_Church_Metrics( $args );
	
	$cm_request = $cm->events();
	$cm_body = wp_remote_retrieve_body( $cm_request );
	$cm_body = json_decode( $cm_body );
	
	$cm_array = array();
	
	foreach( $cm_body as $item ) {
		$cm_array[ $item->id ] = $item->name;
	}
	
	return $cm_array;
	
}

function cm_dash_widgets_visibility_user_field( $field ) {
	
	$options = array();
	$options['all'] = 'All Users';
	
	$users = get_users();
	foreach ( $users as $user ) {
		$options[ $user->ID ] = $user->display_name;
	}
	
	return $options;
	
}

function cm_dash_widgets_cmb_opt_groups( $args, $defaults, $field_object, $field_types_object ) {
	
	// Only do this for the field we want (vs all select fields)
	if ( '_cm_dash_widgets_compare_period' != $field_types_object->_id() ) {
		return $args;
	}
	
	$option_array = array(
		__( 'This Week', 'cm-dash-widgets' ) => array(
			'last week' => __( 'Last Week', 'cm-dash-widgets' ),
			'this week last year' => __( 'This Week Last Year', 'cm-dash-widgets' ),
		),
		__( 'Last Week', 'cm-dash-widgets' ) => array(
			'last week last year' => __( 'Last Week Last Year', 'cm-dash-widgets' ),
		),
		__( 'This Month', 'cm-dash-widgets' ) => array(
			'last month' => __( 'Last Month', 'cm-dash-widgets' ),
			'this month last year' => __( 'This Month Last Year', 'cm-dash-widgets' ),
		),
		__( 'Last Month', 'cm-dash-widgets' ) => array(
			'last month last year' => __( 'Last Month Last Year', 'cm-dash-widgets' ),
		),
		__( 'This Year', 'cm-dash-widgets' ) => array(
			'last year' => __( 'Last Year', 'cm-dash-widgets' ),
		),
	);
	
	$saved_value = $field_object->escaped_value();
	$value       = $saved_value ? $saved_value : $field_object->args( 'default' );
	$options_string = '';
	$options_string .= $field_types_object->select_option( array(
		'label'		=> __( 'Nothing', 'cm-dash-widgets' ),
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
			));
		}
		$options_string .= '</optgroup>';
	}
	// Ok, replace the options value
	$defaults['options'] = $options_string;
	return $defaults;
}
add_filter( 'cmb2_select_attributes', 'cm_dash_widgets_cmb_opt_groups', 10, 4 );