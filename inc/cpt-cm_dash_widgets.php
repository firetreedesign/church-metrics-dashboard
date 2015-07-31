<?php
// Register Custom Post Type
add_action( 'init', function() {
	
	$labels = array(
		'name'                => _x( 'Dashboard Widgets', 'Post Type General Name', 'church-metrics-dashboard' ),
		'singular_name'       => _x( 'Dashboard Widget', 'Post Type Singular Name', 'church-metrics-dashboard' ),
		'menu_name'           => __( 'Church Metrics', 'church-metrics-dashboard' ),
		'name_admin_bar'      => __( 'Church Metrics', 'church-metrics-dashboard' ),
		'parent_item_colon'   => __( 'Parent Dashboard Widget:', 'church-metrics-dashboard' ),
		'all_items'           => __( 'All Dashboard Widgets', 'church-metrics-dashboard' ),
		'add_new_item'        => __( 'Add New Dashboard Widget', 'church-metrics-dashboard' ),
		'add_new'             => __( 'Add New', 'cm-dash-widgets' ),
		'new_item'            => __( 'New Dashboard Widget', 'church-metrics-dashboard' ),
		'edit_item'           => __( 'Edit Dashboard Widget', 'church-metrics-dashboard' ),
		'update_item'         => __( 'Update Dashboard Widget', 'church-metrics-dashboard' ),
		'view_item'           => __( 'View Dashboard Widget', 'church-metrics-dashboard' ),
		'search_items'        => __( 'Search Dashboard Widgets', 'church-metrics-dashboard' ),
		'not_found'           => __( 'Not found', 'church-metrics-dashboard' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'church-metrics-dashboard' ),
	);
	
	register_extended_post_type( 'cm_dash_widgets', array(
		
		'label'               => __( 'Church Metrics', 'church-metrics-dashboard' ),
		'description'         => __( 'Church Metrics Dashboard', 'church-metrics-dashboard' ),
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
				'title'		=> __( 'Campus', 'church-metrics-dashboard' ),
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
				'title'		=> __( 'Category', 'church-metrics-dashboard' ),
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
				'title'		=> __( 'Event', 'church-metrics-dashboard' ),
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
				'title'		=> __( 'Display Period', 'church-metrics-dashboard' ),
				'function'	=> function() {
					echo ucwords( get_post_meta( get_the_ID(), '_cm_dash_widgets_display_period', true ) );
				},
			),
			'compare_period'	=> array(
				'title'		=> __( 'Compare Period', 'church-metrics-dashboard' ),
				'function'	=> function() {
					echo ucwords( get_post_meta( get_the_ID(), '_cm_dash_widgets_compare_period', true ) );
				},
			),
			'visibility_user'	=> array(
				'title'		=> __( 'Visible To', 'church-metrics-dashboard' ),
				'function'	=> function() {
					
					// Visible to
					$visible_to = get_post_meta( get_the_ID(), '_cm_dash_widgets_visibility_user', true );
					
					if ( ! is_array( $visible_to ) ) {
						$visible_to = explode( ',', $visible_to );
					}
					
					foreach( $visible_to as $user ) {
						switch( $user ) {
							case 'all':
								echo 'All Users<br />';
								break;
							default:
								$user_data = get_userdata( $user );
								echo $user_data->display_name . '<br />';
								break;
						}
					}
					
				},
			),
		),
		
	),
	array(
		'singular'	=> 'Dashboard Widget',
		'plural'	=> 'Dashboard Widgets',
	) );
	
} );

/**
 * Filter the post updated messages
 */

add_filter('post_updated_messages', function( $messages ) {
	
	$post = get_post();
	
	$messages['cm_dash_widgets'] = array(
		0	=> '', // Unused. Messages start at index 1.
		1	=> sprintf( __('Dashboard Widget updated. <a href="%s">Visit Dashboard</a>', 'church-metrics-dashboard' ), esc_url( get_admin_url() ) ),
		2	=> __('Custom field updated.', 'church-metrics-dashboard' ),
		3	=> __('Custom field deleted.', 'church-metrics-dashboard' ),
		4	=> __('Dashboard Widget updated.', 'church-metrics-dashboard' ),
		/* translators: %s: date and time of the revision */
		5	=> isset($_GET['revision']) ? sprintf( __('Dashboard Widget restored to revision from %s', 'church-metrics-dashboard' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6	=> sprintf( __('Dashboard Widget published. <a href="%s">Visit Dashboard</a>', 'church-metrics-dashboard' ), esc_url( get_admin_url() ) ),
		7	=> __('Dashboard Widget saved.', 'church-metrics-dashboard' ),
		8	=> __('Dashboard Widget submitted.', 'church-metrics-dashboard' ),
		9	=> sprintf( __('Dashboard Widget scheduled for: <strong>%1$s</strong>.', 'church-metrics-dashboard' ),
		  // translators: Publish box date format, see http://php.net/date
		  date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
		10	=> __('Dashboard Widget draft updated.', 'church-metrics-dashboard' ),
	);
	
	return $messages;
} );