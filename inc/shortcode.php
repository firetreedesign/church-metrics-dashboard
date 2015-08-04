<?php
function church_metrics_dashboard_shortcode( $atts ) {
	
	// Combine the shortcode attributes
	$atts = shortcode_atts(
		array(
			'id'			=> null,
			'before'		=> null,
			'after'			=> null,
			'before_title'	=> '<h2>',
			'after_title'	=> '</h2>',
		),
		$atts,
		'church_metrics_dashboard' 
	);
	
	$html = '';
	
	if ( $atts['id'] ) {
		
		// Prepare our query
		$args = array(
			'post_type'			=> 'cm_dash_widgets',
			'post_status'		=> 'publish',
			'posts_per_page'	=> 1,
			'p'					=> $atts['id'],
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
				
				ob_start();
				
				echo $atts['before'];
				
				the_title( $atts['before_title'], $atts['after_title'] );
				
				cm_dash_widgets_dashboard_widget_callback( null,
					array(
						'args' => array(
							'campus_id'			=> $campus_id,
							'category_id'		=> $category_id,
							'event_id'			=> $event_id,
							'display'			=> $display,
							'display_period'	=> $display_period,
							'compare_period'	=> $compare_period,
						),
					)
				);
				
				echo $atts['after'];
				
				$html .= ob_get_clean();
			
			endwhile;
		
	}
	
	// Restore global post data.
	wp_reset_query();
		
	}
	
	return $html;
	
}
add_shortcode( 'church_metrics_dashboard', 'church_metrics_dashboard_shortcode' );