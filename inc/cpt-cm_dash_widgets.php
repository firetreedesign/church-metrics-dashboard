<?php
// Register Custom Post Type
add_action( 'init', function() {
	
	$labels = array(
		'name'                => _x( 'Dashboard Widgets', 'Post Type General Name', 'cm-dash-widgets' ),
		'singular_name'       => _x( 'Dashboard Widget', 'Post Type Singular Name', 'cm-dash-widgets' ),
		'menu_name'           => __( 'Church Metrics', 'cm-dash-widgets' ),
		'name_admin_bar'      => __( 'Church Metrics', 'cm-dash-widgets' ),
		'parent_item_colon'   => __( 'Parent Dashboard Widget:', 'cm-dash-widgets' ),
		'all_items'           => __( 'All Dashboard Widgets', 'cm-dash-widgets' ),
		'add_new_item'        => __( 'Add New Dashboard Widget', 'cm-dash-widgets' ),
		'add_new'             => __( 'Add New', 'cm-dash-widgets' ),
		'new_item'            => __( 'New Dashboard Widget', 'cm-dash-widgets' ),
		'edit_item'           => __( 'Edit Dashboard Widget', 'cm-dash-widgets' ),
		'update_item'         => __( 'Update Dashboard Widget', 'cm-dash-widgets' ),
		'view_item'           => __( 'View Dashboard Widget', 'cm-dash-widgets' ),
		'search_items'        => __( 'Search Dashboard Widgets', 'cm-dash-widgets' ),
		'not_found'           => __( 'Not found', 'cm-dash-widgets' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'cm-dash-widgets' ),
	);
	
	register_extended_post_type( 'cm_dash_widgets', array(
		
		'label'               => __( 'Church Metrics', 'cm-dash-widgets' ),
		'description'         => __( 'Church Metrics Dashboard', 'cm-dash-widgets' ),
		'labels'              => $labels,
		'supports'            => array( 'title', ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 70,
		'menu_icon'           => 'dashicons-chart-line',
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'can_export'          => true,
		'has_archive'         => false,		
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'rewrite'             => false,
		'capability_type'     => 'post',
		
		'archive_in_nav_menus'	=> false,
		'quick_edit'			=> false,
		'dashboard_glance'		=> false,
		'show_in_feed'			=> false,
		'admin_cols'			=> array(
			'title',
			'campus'	=> array(
				'title'		=> __( 'Campus', 'cm-dash-widgets' ),
				'function'	=> function() {
					
					// Define the Church Metrics credentials
					$cm_args = array(
						'user'	=> cm_dash_widgets_get_option('user'),
						'key'	=> cm_dash_widgets_get_option('key'),
					);
					
					// Initialize the Church Metrics class
					$cm = new WP_Church_Metrics( $cm_args );
					
					// Define our request arguments
					$cm_request_args = array(
						'id' => get_post_meta( get_the_ID(), '_cm_dash_widgets_campus', true ),
					);
					
					// Request the data from the API
					$cm_request = $cm->campus( $cm_request_args );
					$cm_body = wp_remote_retrieve_body( $cm_request );
					$cm_body = json_decode( $cm_body );
					echo $cm_body->slug;
					
					// Free up some memory
					unset( $cm_body, $cm_request, $cm, $cm_args );

				},
			),
			'category'	=> array(
				'title'		=> __( 'Category', 'cm-dash-widgets' ),
				'function'	=> function() {
					
					// Define the Church Metrics credentials
					$cm_args = array(
						'user'	=> cm_dash_widgets_get_option('user'),
						'key'	=> cm_dash_widgets_get_option('key'),
					);
					
					// Initialize the Church Metrics class
					$cm = new WP_Church_Metrics( $cm_args );
					
					// Define our request arguments
					$cm_request_args = array(
						'id' => get_post_meta( get_the_ID(), '_cm_dash_widgets_category', true ),
					);
					
					// Request the data from the API
					$cm_request = $cm->category( $cm_request_args );
					$cm_body = wp_remote_retrieve_body( $cm_request );
					$cm_body = json_decode( $cm_body );
					echo $cm_body->name;
					
					// Free up some memory
					unset( $cm_body, $cm_request, $cm, $cm_args );
				},
			),
			'event'	=> array(
				'title'		=> __( 'Event', 'cm-dash-widgets' ),
				'function'	=> function() {
					
					// Retrieve the event id
					$event_id = get_post_meta( get_the_ID(), '_cm_dash_widgets_event', true );
					
					// If there is an event id, then let's continue
					if ( strlen( $event_id ) > 0 ) {
					
						// Define the Church Metrics credentials
						$cm_args = array(
							'user'	=> cm_dash_widgets_get_option('user'),
							'key'	=> cm_dash_widgets_get_option('key'),
						);
						
						// Initialize the Church Metrics class
						$cm = new WP_Church_Metrics( $cm_args );
						
						// Define our request arguments
						$cm_request_args = array(
							'id' => $event_id,
						);
						
						// Request the data from the API
						$cm_request = $cm->event( $cm_request_args );
						$cm_body = wp_remote_retrieve_body( $cm_request );
						$cm_body = json_decode( $cm_body );
						echo $cm_body->name;
						
						// Free up some memory
						unset( $cm_body, $cm_request, $cm, $cm_args );
					
					}
				},
			),
			'display_period'	=> array(
				'title'		=> __( 'Display Period', 'cm-dash-widgets' ),
				'function'	=> function() {
					echo ucwords( get_post_meta( get_the_ID(), '_cm_dash_widgets_display_period', true ) );
				},
			),
			'compare_period'	=> array(
				'title'		=> __( 'Compare Period', 'cm-dash-widgets' ),
				'function'	=> function() {
					echo ucwords( get_post_meta( get_the_ID(), '_cm_dash_widgets_compare_period', true ) );
				},
			),
			'visibility_user'	=> array(
				'title'		=> __( 'Visible To', 'cm-dash-widgets' ),
				'function'	=> function() {
					
					// Retrieve the data for the specified user
					$user_data = get_userdata( get_post_meta( get_the_ID(), '_cm_dash_widgets_visibility_user', true ) );
					if ( $user_data ) {
						echo $user_data->display_name;
					} else {
						echo 'All Users';
					}
				},
			),
		),
		
	), array(
		'singular'	=> 'Dashboard Widget',
		'plural'	=> 'Dashboard Widgets',
	) );
	
} );