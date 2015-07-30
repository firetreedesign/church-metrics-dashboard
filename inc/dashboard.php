<?php
add_action( 'wp_dashboard_setup', 'cm_dash_widgets_widget_setup' );

/**
 * Retrive all of the Dashboard Widgets and set them up
 */

function cm_dash_widgets_widget_setup() {

	$type = 'cm_dash_widgets';
	$args = array(
		'post_type'			=> $type,
		'post_status'		=> 'publish',
		'posts_per_page'	=> -1,
	);

	$my_query = null;
	$my_query = new WP_Query($args);
	
	if ( $my_query->have_posts() ) {
		
		while ( $my_query->have_posts() ) : $my_query->the_post();
    
			$campus_id = get_post_meta( get_the_ID(), '_cm_dash_widgets_campus', true );
			$category_id = get_post_meta( get_the_ID(), '_cm_dash_widgets_category', true );
			$event_id = get_post_meta( get_the_ID(), '_cm_dash_widgets_event', true );
			$display = get_post_meta( get_the_ID(), '_cm_dash_widgets_display', true );
			$display_period = get_post_meta( get_the_ID(), '_cm_dash_widgets_display_period', true );
			$compare_period = get_post_meta( get_the_ID(), '_cm_dash_widgets_compare_period', true );
			$visibility_user = get_post_meta( get_the_ID(), '_cm_dash_widgets_visibility_user', true );
			
			
			if ( $visibility_user != 'all' ) {
				
				$current_user_id = get_current_user_id();

			    if ( $current_user_id && $visibility_user != $current_user_id ) {
			        continue;
			    }
				
			}
			
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
	
	wp_reset_query();  // Restore global post data stomped by the_post().
	
}

/**
 * Display the Dashboard Widgets
 */

function cm_dash_widgets_dashboard_widget_callback( $var, $args ) {
    
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
	    
    }
    
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
	    
    }
    
    $event_id = NULL;
    if ( strlen( $args['args']['event_id'] ) > 0 ) {
	    $event_id = $args['args']['event_id'];
    }
    
    $cm_args = array(
		'user'	=> cm_dash_widgets_get_option('user'),
		'key'	=> cm_dash_widgets_get_option('key'),
	);
	
	$cm = new WP_Church_Metrics( $cm_args );
	
	// Request values
	$request_args = array(
		'campus_id'			=> $args['args']['campus_id'],
		'category_id'		=> $args['args']['category_id'],
		'event_id'			=> $event_id,
		'start_time'		=> $range['start'],
		'end_time'			=> $range['end'],
	);
	
	$cm_request = $cm->records( $request_args );
	$cm_body = json_decode( wp_remote_retrieve_body( $cm_request ) );
	$next_link = $cm->get_link( $cm_request, 'next' );
	$loop_count = 0;
	
	while ( $next_link ) {
		$cm_request_next = $cm->get( array( 'url' => $next_link ) );
		$cm_request_body = json_decode( wp_remote_retrieve_body( $cm_request_next ) );
		$next_link = $cm->get_link( $cm_request_next, 'next' );
		
		$cm_body = array_merge( $cm_body, $cm_request_body );
		
		if ( $loop_count > 1000 ) {
			break;
		}
	}
	
	unset( $cm_request_next );
	unset( $cm_request_body );
	
	$value_count = 0;
	$compare_value_count = 0;
	foreach( $cm_body as $record ) {
		$value_count += $record->value;
	}
	
	$value_count_formatted = $value_count;
	
	if ( is_array( $cm_body ) && count( $cm_body ) > 0 ) {
		$value_format = $cm_body[0]->category->format;
		switch( $value_format ) {
			case 'currency':
				$value_count_formatted = '$' . number_format( $value_count );
				break;
			default:
				$value_count_formatted = number_format( $value_count );
				break;
		}
	}
	
	unset( $cm_request );
	unset( $cm_body );
	
	// Request values to compare
	if ( $args['args']['compare_period'] != 'nothing' ) {
		
		$request_args = array(
			'campus_id'			=> $args['args']['campus_id'],
			'category_id'		=> $args['args']['category_id'],
			'event_id'			=> $event_id,
			'start_time'		=> $compare_range['start'],
			'end_time'			=> $compare_range['end'],
		);
		
		$cm_request = $cm->records( $request_args );
		$cm_body = json_decode( wp_remote_retrieve_body( $cm_request ) );
		$next_link = $cm->get_link( $cm_request, 'next' );
		$loop_count = 0;
	
		while ( $next_link ) {
			$cm_request_next = $cm->get( array( 'url' => $next_link ) );
			$cm_request_body = json_decode( wp_remote_retrieve_body( $cm_request_next ) );
			$next_link = $cm->get_link( $cm_request_next, 'next' );
			
			$cm_body = array_merge( $cm_body, $cm_request_body );
			
			if ( $loop_count > 1000 ) {
				break;
			}
		}
		
		unset( $cm_request_next );
		unset( $cm_request_body );
		
		//var_dump( $cm_body );
		
		$compare_value_count = 0;
		foreach( $cm_body as $record ) {
			$compare_value_count += $record->value;
		}
		
		$compare_value_count_formatted = $compare_value_count;
		
		if ( is_array( $cm_body ) && count( $cm_body ) > 0 ) {
			$compare_value_format = $cm_body[0]->category->format;
			switch( $compare_value_format ) {
				case 'currency':
					$compare_value_count_formatted = '$' . number_format( $compare_value_count );
					break;
				default:
					$compare_value_count_formatted = number_format( $compare_value_count );
					break;
			}
		}
		
		unset( $cm_request );
		unset( $cm_body );

		
	}
	
	
	$difference = cm_dash_widgets_percentage_change( $value_count, $compare_value_count);
	$difference_icon = '';
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

function cm_dash_widgets_range_year( $datestr ) {
	date_default_timezone_set( date_default_timezone_get() );
	$dt = strtotime( $datestr );
	$res['start'] = gmdate('Y-m-d\T00:00:00\Z', strtotime('first day of january', $dt ) );
	$res['end'] = gmdate('Y-m-d\T23:59:59\Z', strtotime('last day of december', $dt ) );
	return $res;
}

function cm_dash_widgets_range_month( $datestr ) {
	date_default_timezone_set( date_default_timezone_get() );
	$dt = strtotime( $datestr );
	$res['start'] = gmdate('Y-m-d\T00:00:00\Z', strtotime('first day of this month', $dt ) );
	$res['end'] = gmdate('Y-m-d\T23:59:59\Z', strtotime('last day of this month', $dt ) );
	return $res;
}

function cm_dash_widgets_range_week( $datestr ) {
	date_default_timezone_set( date_default_timezone_get() );
	$dt = strtotime( $datestr );
	$res['start'] = date('N', $dt ) == 7 ? gmdate('Y-m-d\T00:00:00\Z', $dt ) : gmdate('Y-m-d\T00:00:00\Z', strtotime('last sunday', $dt ) );
	$res['end'] = date('N', $dt ) == 6 ? gmdate('Y-m-d\T23:59:59\Z', $dt ) : gmdate('Y-m-d\T23:59:59\Z', strtotime('next saturday', $dt ) );
	return $res;
}

function cm_dash_widgets_percentage_change( $cur, $prev ) {
	if ( $cur == 0 ) {
		if ( $prev == 0 ) {
			return array('diff' => 0, 'trend' => '');
		}
		return array('diff' => -( $prev * 100 ), 'trend' => 'down');
	}
	if ( $prev == 0 ) {
		return array('diff' => $cur * 100, 'trend' => 'up');
	}
	$difference = ( $cur - $prev ) / $prev * 100;
	$trend = '';
	if ( $cur > $prev ) {
		$trend = 'up';
	} else if ( $cur < $prev ) {
		$trend = 'down';
	}
	return array('diff' => abs( round( $difference, 0 ) ), 'trend' => $trend );
}