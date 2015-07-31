<?php
add_action( 'wp_dashboard_setup', 'cm_dash_widgets_widget_setup' );

/**
 * Retrive all of the Dashboard Widgets and set them up
 */

function cm_dash_widgets_widget_setup() {

	// Prepare our query
	$args = array(
		'post_type'			=> 'cm_dash_widgets',
		'post_status'		=> 'publish',
		'posts_per_page'	=> -1,
	);
	$my_query = null;
	
	// Query the data
	$my_query = new WP_Query( $args );
	
	// Check if there are any results
	if ( $my_query->have_posts() ) {
		
		// Loop through each result
		while ( $my_query->have_posts() ) : $my_query->the_post();
    
			// Grab our meta data
			$campus_id			= get_post_meta( get_the_ID(), '_cm_dash_widgets_campus', true );
			$category_id		= get_post_meta( get_the_ID(), '_cm_dash_widgets_category', true );
			$event_id			= get_post_meta( get_the_ID(), '_cm_dash_widgets_event', true );
			$display			= get_post_meta( get_the_ID(), '_cm_dash_widgets_display', true );
			$display_period		= get_post_meta( get_the_ID(), '_cm_dash_widgets_display_period', true );
			$compare_period		= get_post_meta( get_the_ID(), '_cm_dash_widgets_compare_period', true );
			$visibility_user	= get_post_meta( get_the_ID(), '_cm_dash_widgets_visibility_user', true );
			
			if ( ! is_array( $visibility_user ) ) {
				$visibility_user = explode( ',', $visibility_user );
			}
			
			// Check the visibility to see if we should continue
			if ( ! in_array( 'all', $visibility_user ) ) {
				
				// Grab the current user id and check if we should display the widget
				$current_user_id = get_current_user_id();

			    if ( $current_user_id && ! in_array( $current_user_id, $visibility_user ) ) {
			        continue;
			    }
				
			}
			
			// Define our Dashboard Widget
			wp_add_dashboard_widget(
				'cm_dash_widgets_dashboard_widget_' . get_the_id(),
				get_the_title(),
				'cm_dash_widgets_dashboard_widget_callback',
				null,
				array(
					'campus_id'			=> $campus_id,
					'category_id'		=> $category_id,
					'event_id'			=> $event_id,
					'display'			=> $display,
					'display_period'	=> $display_period,
					'compare_period'	=> $compare_period,
				)
			);
    
		endwhile;
		
	}
	
	// Restore global post data.
	wp_reset_query();
	
}

/**
 * Display the Dashboard Widgets
 *
 * @param	string	$post
 * @param	array	$args	Array of arguments passed to the widget.
 *
 * @return	nothing
 */

function cm_dash_widgets_dashboard_widget_callback( $post, $args ) {
    
    // Determine the date range for the display period
    switch( $args['args']['display_period'] ) {
	    
	    case 'this week':
	    	$range = cm_dash_widgets_range_week('this week' );
	    	break;
	    
	    case 'last week':
	    	$range = cm_dash_widgets_range_week('last week' );
	    	break;
	    
	    case 'this month':
	    	$range = cm_dash_widgets_range_month('this month' );
	    	break;
	    
	    case 'last month':
	    	$range = cm_dash_widgets_range_month('last month' );
	    	break;
	    
	    case 'this year':
	    	$range = cm_dash_widgets_range_year('this year' );
	    	break;
	    
	    case 'last year':
	    	$range = cm_dash_widgets_range_year('last year' );
	    	break;
	    
	    default:
	    	$range = cm_dash_widgets_range_week('this week' );
	    	break;
	    
    }
    
    // Determine the date range for the compare period
    switch( $args['args']['compare_period'] ) {
	    
	    case 'last week':
	    	$compare_range = cm_dash_widgets_range_week('last week');
	    	break;
	    
	    case 'this week last year':
	    	$compare_range = cm_dash_widgets_range_week('this week last year');
	    	break;
	    
	    case 'last week last year':
	    	$compare_range = cm_dash_widgets_range_week('last week last year');
	    	break;
	    
	    case 'last month':
	    	$compare_range = cm_dash_widgets_range_month('last month' );
	    	break;
	    
	    case 'this month last year':
	    	$compare_range = cm_dash_widgets_range_month('this month last year' );
	    	break;
	    
	    case 'last month last year':
	    	$compare_range = cm_dash_widgets_range_month('last month last year' );
	    	break;
	    
	    case 'last year':
	    	$compare_range = cm_dash_widgets_range_year('last year' );
	    	break;
	    
	    default:
	    	$compare_range = cm_dash_widgets_range_week('last week');
	    	break;
	    
    }
    
    // Determine if there is an event id
    $event_id = NULL;
    if ( strlen( $args['args']['event_id'] ) > 0 ) {
	    $event_id = $args['args']['event_id'];
    }
    
    // Define our Church Metrics arguments
    $cm_args = array(
		'user'	=> cm_dash_widgets_get_option('user'),
		'key'	=> cm_dash_widgets_get_option('key'),
	);
	
	// Initialize the Church Metrics API class
	$cm = new WP_Church_Metrics( $cm_args );
	
	// Define our request arguments
	$request_args = array(
		'campus_id'			=> $args['args']['campus_id'],
		'category_id'		=> $args['args']['category_id'],
		'event_id'			=> $event_id,
		'start_time'		=> $range['start'],
		'end_time'			=> $range['end'],
	);
	
	// Request the records from the API
	$cm_request = $cm->records( $request_args );
	
	// Get the body from the response
	$cm_body = json_decode( wp_remote_retrieve_body( $cm_request ) );
	
	// Request might be paginated. Look for a 'next' page
	$next_link = $cm->get_link( $cm_request, 'next' );
	
	// Define our loop count
	$loop_count = 0;
	
	// Check if there is a 'next' page
	while ( $next_link && $loop_count <= 1000 ) {
		
		// Request the records from the API
		$cm_request_next = $cm->get( array( 'url' => $next_link ) );
		
		// Get the body from the response
		$cm_request_body = json_decode( wp_remote_retrieve_body( $cm_request_next ) );
		
		// Request might be paginated. Look for a 'next' page
		$next_link = $cm->get_link( $cm_request_next, 'next' );
		
		// Merge the response body with the previous response body
		$cm_body = array_merge( $cm_body, $cm_request_body );
		
		// Increment our loop count
		$loop_count++;
		
	}
	
	// Free up some memory
	unset( $cm_request_next, $cm_request_body );
	
	// Define our initial values
	$value_count = 0;
	$compare_value_count = 0;
	
	// Loop through each record and add up the values
	foreach( $cm_body as $record ) {
		$value_count += $record->value;
	}
	
	// Define our initial formatted value
	$value_count_formatted = $value_count;
	
	// Make sure that there is at least one record
	if ( is_array( $cm_body ) && count( $cm_body ) > 0 ) {
		
		// Determine the value format from the first record
		$value_format = $cm_body[0]->category->format;
		
		// Format the value accordingly
		switch( $value_format ) {
			case 'currency':
				$value_count_formatted = '$' . number_format( $value_count );
				break;
			default:
				$value_count_formatted = number_format( $value_count );
				break;
		}
	}
	
	// Free up some memory
	unset( $cm_request, $cm_body );
	
	// Check if we are comparing any values
	if ( $args['args']['compare_period'] != 'nothing' ) {
		
		// Define our request arguments
		$request_args = array(
			'campus_id'			=> $args['args']['campus_id'],
			'category_id'		=> $args['args']['category_id'],
			'event_id'			=> $event_id,
			'start_time'		=> $compare_range['start'],
			'end_time'			=> $compare_range['end'],
		);
		
		// Request the records from the API
		$cm_request = $cm->records( $request_args );
		
		// Get the body from the response
		$cm_body = json_decode( wp_remote_retrieve_body( $cm_request ) );
		
		// Request might be paginated. Look for a 'next' page
		$next_link = $cm->get_link( $cm_request, 'next' );
		
		// Define our loop count
		$loop_count = 0;
	
		// Check if there is a 'next' page
		while ( $next_link && $loop_count <= 1000 ) {
			
			// Request the records from the API
			$cm_request_next = $cm->get( array( 'url' => $next_link ) );
			
			// Get the body from the response
			$cm_request_body = json_decode( wp_remote_retrieve_body( $cm_request_next ) );
			
			// Request might be paginated. Look for a 'next' page
			$next_link = $cm->get_link( $cm_request_next, 'next' );
			
			// Merge the response body with the previous response body
			$cm_body = array_merge( $cm_body, $cm_request_body );
			
			// Increment our loop count
			$loop_count++;
		}
		
		// Free up some memory
		unset( $cm_request_next, $cm_request_body );
		
		// Loop through each record and add up the values
		foreach( $cm_body as $record ) {
			$compare_value_count += $record->value;
		}
		
		// Define our initial formatted value
		$compare_value_count_formatted = $compare_value_count;
		
		// Make sure that there is at least one record
		if ( is_array( $cm_body ) && count( $cm_body ) > 0 ) {
			
			// Determine the value format from the first record
			$compare_value_format = $cm_body[0]->category->format;
			
			// Format the value accordingly
			switch( $compare_value_format ) {
				case 'currency':
					$compare_value_count_formatted = '$' . number_format( $compare_value_count );
					break;
				default:
					$compare_value_count_formatted = number_format( $compare_value_count );
					break;
			}
			
		}
		
		// Free up some memory
		unset( $cm_request, $cm_body );
		
	}
	
	// Get the difference between the two values
	$difference = cm_dash_widgets_percentage_change( $value_count, $compare_value_count);
	
	// Setup this variable
	$difference_icon = '';
	
	// Depending on the trend, setup the appropriate icon and value
	switch( $difference['trend'] ) {
		
		case 'up':
			$difference_icon = '<span class="dashicons dashicons-arrow-up"></span>';
			$difference['diff'] = $difference['diff'] . '%';
			break;
		
		case 'down':
			$difference_icon = '<span class="dashicons dashicons-arrow-down"></span>';
			$difference['diff'] = $difference['diff'] . '%';
			break;
		
		default:
			$difference['diff'] = '';
			break;
			
	}
	
	// Output our data
	?>
	<div class="cm_dash_widgets_container">
		<div class="cm_dash_widgets_heading">
			<?php echo $args['args']['display_period']; ?> <span class="cm_dash_widgets_diff_<?php echo $difference['trend']; ?>"><?php echo $difference_icon; ?><?php echo $difference['diff']; ?></span>
		</div>
		<div class="cm_dash_widgets_value">
			<?php echo $value_count_formatted; ?>
		</div>
	</div>
	<?php
	// Check if we are comparing values
	if ( $args['args']['compare_period'] != 'nothing' ) {
	?>
		<div class="cm_dash_widgets_container">
			<div class="cm_dash_widgets_heading">
				<?php echo $args['args']['compare_period']; ?>
			</div>
			<div class="cm_dash_widgets_compare_value">
				<?php echo $compare_value_count_formatted; ?>
			</div>
		</div>
		<?php
	}
  
}