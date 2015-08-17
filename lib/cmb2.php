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
	    'type'             => 'select',
	    'show_option_none' => false,
	    'options'          => 'cm_dash_widgets_campus_field',
	) );
	
	$cmb->add_field( array(
	    'name'             => __( 'Category', 'church-metrics-dashboard' ),
	    'desc'             => __( 'Select a category', 'church-metrics-dashboard' ),
	    'id'               => $prefix . 'category',
	    'type'             => 'pw_multiselect',
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
	    'type'		=> 'select',
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
	    'type'				=> 'select',
	    'default'			=> 'this_week',
	    'show_option_none'	=> false,
	    'row_classes'		=> 'church-metrics-dashboard-select2',
	    'options'			=> array(
	    	'this week'					=> __( 'This Week', 'church-metrics-dashboard' ),
	    	'last week'					=> __( 'Last Week', 'church-metrics-dashboard' ),
	    	'this month'				=> __( 'This Month', 'church-metrics-dashboard' ),
	    	'last month'				=> __( 'Last Month', 'church-metrics-dashboard' ),
	    	'this year'					=> __( 'This Year', 'church-metrics-dashboard' ),
	    	'last year'					=> __( 'Last Year', 'church-metrics-dashboard' ),
	    	'weekly-avg-this-year'		=> __( 'Weekly Average This Year', 'church-metrics-dashboard' ),
	    	'weekly-avg-last-year'		=> __( 'Weekly Average Last Year', 'church-metrics-dashboard' ),
	    	'weekly-avg-last-year-yoy'	=> __( 'Weekly Average Last Year (Year Over Year)', 'church-metrics-dashboard' ),
	    	'monthly-avg-this-year'		=> __( 'Monthly Average This Year', 'church-metrics-dashboard' ),
	    	'monthly-avg-last-year'		=> __( 'Monthly Average Last Year', 'church-metrics-dashboard' ),
	    	'monthly-avg-last-year-yoy'	=> __( 'Monthly Average Last Year (Year Over Year)', 'church-metrics-dashboard' ),
	    	'all-time'					=> __( 'All Time', 'church-metrics-dashboard' ),
	    ),
	) );
	
	$compare_to_id = $cmb->add_field( array(
		'id'	=> $prefix . 'compare_to',
		'type'	=> 'group',
		'description'	=> __( 'Compare To', 'church-metrics-dashboard' ),
		'options'		=> array(
			'group_title'	=> __( 'Comparison {#}', 'church-metrics-dashboard' ),
			'add_button'	=> __( 'Add Another Period', 'church-metrics-dashboard' ),
			'remove_button'	=> __( 'Remove Period', 'church-metrics-dashboard' ),
			'sortable'		=> true,
		),
	) );
	
	$cmb->add_group_field( $compare_to_id, array(
	    'name'				=> __( 'Period', 'church-metrics-dashboard' ),
	    'desc'				=> __( 'Select a period to compare', 'church-metrics-dashboard' ),
	    'id'				=> 'compare_period',
	    'type'				=> 'select',
	    'default'			=> 'nothing',
	    'show_option_none'	=> false,
	    'row_classes'		=> 'church-metrics-dashboard-select2',
	    'options'			=> array(
	    	'nothing'					=> __( 'None', 'church-metrics-dashboard' ),
	    	'this week'					=> __( 'This Week', 'church-metrics-dashboard' ),
	    	'this week last year'		=> __( 'This Week Last Year', 'church-metrics-dashboard' ),
	    	'last week'					=> __( 'Last Week', 'church-metrics-dashboard' ),
	    	'last week last year'		=> __( 'Last Week Last Year', 'church-metrics-dashboard' ),
	    	'this month'				=> __( 'This Month', 'church-metrics-dashboard' ),
	    	'this month last year'	=> __( 'This Month Last Year', 'church-metrics-dashboard' ),
	    	'last month'				=> __( 'Last Month', 'church-metrics-dashboard' ),
	    	'last month last year'	=> __( 'Last Month Last Year', 'church-metrics-dashboard' ),
	    	'this year'					=> __( 'This Year', 'church-metrics-dashboard' ),
	    	'last year'					=> __( 'Last Year', 'church-metrics-dashboard' ),
	    	'weekly-avg-this-year'		=> __( 'Weekly Average This Year', 'church-metrics-dashboard' ),
	    	'weekly-avg-last-year'		=> __( 'Weekly Average Last Year', 'church-metrics-dashboard' ),
	    	'weekly-avg-last-year-yoy'	=> __( 'Weekly Average Last Year (Year Over Year)', 'church-metrics-dashboard' ),
	    	'monthly-avg-this-year'		=> __( 'Monthly Average This Year', 'church-metrics-dashboard' ),
	    	'monthly-avg-last-year'		=> __( 'Monthly Average Last Year', 'church-metrics-dashboard' ),
	    	'monthly-avg-last-year-yoy'	=> __( 'Monthly Average Last Year (Year Over Year)', 'church-metrics-dashboard' ),
	    	'all-time'					=> __( 'All Time', 'church-metrics-dashboard' ),
	    ),
	) );
	
	/**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id'            => 'cm_dash_widgets_display_metabox',
        'title'         => __( 'Display', 'church-metrics-dashboard' ),
        'object_types'  => array( 'cm_dash_widgets', ), // Post type
        'context'       => 'side',
        'priority'      => 'default',
        'show_names'    => true, // Show field names on the left
    ) );
    
    $cmb->add_field( array(
	    'name'		=> __( 'Show on Dashboard', 'church-metrics-dashboard' ),
	    'id'		=> $prefix . 'dashboard_show',
	    'type'		=> 'radio_inline',
	    'default'	=> 'yes',
	    'show_option_none' => false,
	    'options'	=> array(
	        'yes'	=> __( 'Yes', 'church-metrics-dashboard' ),
	        'no'	=> __( 'No', 'church-metrics-dashboard' ),
	    ),
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

	
	$cmb->add_field( array(
	    'name'    => __( 'Shortcode', 'church-metrics-dashboard' ),
	    'desc'    => church_metrics_dashboard_shortcode_field_desc(),
	    'default' => 'church_metrics_dashboard_shortcode_field_default',
	    'id'      => $prefix . 'shortcode',
	    'type'    => 'text',
	    'attributes'	=> array(
		    'readonly'	=> 'readonly',
	    ),
	) );

}

function church_metrics_dashboard_shortcode_field_default( $field_args, $field ) {
	return '[church_metrics_dashboard id=' . $field->object_id . ']';
}

function church_metrics_dashboard_shortcode_field_desc() {
	ob_start();
	?>
	Use this shortcode to display this widget on the front-end of your site.<br />
		<strong>Additional Parameters:</strong> before, after, before_title, and after_title
	<?php
	return ob_get_clean();
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