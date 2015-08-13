<?php

/**
 * Church Metrics Dashboard
 *
 * Helps with retrieving data from Church Metrics.
 *
 * @version 1.0.0
 */

class Church_Metrics_Dashboard {
	
	/**
	 * Create a new instance
	 */
	 
	function __construct() {
		
		require_once( plugin_dir_path( __FILE__ ) . 'WP_Church_Metrics_Class.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'cmb2.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'extended-cpts/extended-cpts.php' );
		require_once( plugin_dir_path( __FILE__ ) . '../inc/cpt-cm_dash_widgets-settings.php' );
		
		
		add_action( 'init', array( $this, 'setup_cpt' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_action( 'wp_footer', array( $this, 'customize_css' ) );
		add_action( 'admin_footer', array( $this, 'customize_css' ) );
		add_action( 'admin_menu', array( $this, 'submenu_page' ) );
		add_action( 'load-post-new.php', array( $this, 'disable_new_post' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'display_styles' ) );
		
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		
		add_shortcode( 'church_metrics_dashboard', array( $this, 'shortcode' ) );
				
	}
	
	/**
	 * Display styles
	 */
	
	public function display_styles() {
		
		wp_enqueue_style( 'church-metrics-dashboard', plugin_dir_url( __FILE__ ) . '../css/display.css', array( 'dashicons' ), '1.0.1' );
		
	}
	
	/**
	 * Admin styles
	 */
	
	public function admin_styles( $hook ) {
		
		if ( 'index.php' != $hook ) {
	        return;
	    }
	
	    wp_register_style( 'cm_dash_widgets_admin_css', plugin_dir_url( __FILE__ ) . '../css/admin.css' );
	    wp_enqueue_style( 'cm_dash_widgets_admin_css' );
		
	}
	
	/**
	 * Disable new post
	 */
	
	public function disable_new_post() {
		
		if ( get_current_screen()->post_type == 'cm_dash_widgets' ) {
	    
		    $user = cm_dash_widgets_get_option('user');
		    $key = cm_dash_widgets_get_option('key');
		    
		    if ( $user == '' || $key == '' ) {
			    wp_die( "You must first setup the Church Metrics API." );
		    }
		    
	    }
		
	}
	
	/**
	 * Setup our submenu
	 */
	
	public function submenu_page() {
		
		add_submenu_page( 'edit.php?post_type=cm_dash_widgets', 'Customizer', 'Customizer', 'manage_options', admin_url( 'customize.php?autofocus[panel]=church_metrics_dashboard&return=edit.php?post_type=cm_dash_widgets' ) );
		
	}
	
	/**
	 * Output the Customizer CSS
	 */
	
	public function customize_css() {
		
		$primary_number_color = get_theme_mod( 'church_metrics_dashboard_primary_color', '#7fc661' );
	    $comparison_number_color = get_theme_mod( 'church_metrics_dashboard_comparison_color', '#64b4f2' );
	    $heading_color = get_theme_mod( 'church_metrics_dashboard_heading_color', '#aaa' );
		
	    ?>
	         <style type="text/css">
			 	.cm_dash_widgets_value { color: <?php echo $primary_number_color; ?>; }
				.cm_dash_widgets_compare_value { color: <?php echo $comparison_number_color; ?>; }
				.cm_dash_widgets_heading { color: <?php echo $heading_color; ?>; }
	         </style>
	    <?php
		
	}
	
	/**
	 * Register the Customizer settings
	 */
	
	public function customize_register( $wp_customize ) {
		
		/**
		 * Church Metrics Panel
		 */
		 
		$wp_customize->add_panel( 'church_metrics_dashboard', array(
			'title'			=> __( 'Church Metrics Dashboard', 'church-metrics-dashboard' ),
			'description'	=> __( '<p>This panel is used to manage the display settings for Church Metrics Dashboard.</p>', 'church-metrics-dashboard' ),
			'priority'		=> 160,
		) );
		
		/**
		 * Colors Section
		 */
		 
		$wp_customize->add_section( 'church_metrics_dashboard_colors', array(
			'title'			=> __( 'Colors', 'church-metrics-dashboard' ),
			'panel'			=> 'church_metrics_dashboard',
			'priority'		=> 120,
		) );
		
		// Primary Number Color
		$wp_customize->add_setting( 'church_metrics_dashboard_primary_color', array(
			'type'				=> 'theme_mod',
			'transport'			=> 'refresh',
			'default'			=> '#7fc661',
		) );
		
		$wp_customize->add_control(
			new WP_Customize_Color_Control( $wp_customize, 'church_metrics_dashboard_primary_color',
				array(
					'label'		=> __( 'Primary Number Color', 'church-metrics-dashboard' ),
					'section'	=> 'church_metrics_dashboard_colors',
				)
			)
		);
		
		// Comparision Number Color
		$wp_customize->add_setting( 'church_metrics_dashboard_comparison_color', array(
			'type'				=> 'theme_mod',
			'transport'			=> 'refresh',
			'default'			=> '#64b4f2',
		) );
		
		$wp_customize->add_control(
			new WP_Customize_Color_Control( $wp_customize, 'church_metrics_dashboard_comparison_color',
				array(
					'label'		=> __( 'Comparison Number Color', 'church-metrics-dashboard' ),
					'section'	=> 'church_metrics_dashboard_colors',
				)
			)
		);
		
		// Heading Color
		$wp_customize->add_setting( 'church_metrics_dashboard_heading_color', array(
			'type'				=> 'theme_mod',
			'transport'			=> 'refresh',
			'default'			=> '#aaa',
		) );
		
		$wp_customize->add_control(
			new WP_Customize_Color_Control( $wp_customize, 'church_metrics_dashboard_heading_color',
				array(
					'label'		=> __( 'Heading Color', 'church-metrics-dashboard' ),
					'section'	=> 'church_metrics_dashboard_colors',
				)
			)
		);
		
	}
	
	/**
	 * Setup the shortcode
	 */
	
	public function shortcode( $atts ) {
	
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
					
					$this->wp_dashboard_setup_display( null,
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
	
	/**
	 * Define the Dashboard Widgets
	 */
	
	public function wp_dashboard_setup() {
		
		// Prepare our query
		$args = array(
			'post_type'			=> 'cm_dash_widgets',
			'post_status'		=> 'publish',
			'posts_per_page'	=> -1,
			'meta_key'			=> '_cm_dash_widgets_dashboard_show',
			'meta_value'		=> 'yes'
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
					array( $this, 'wp_dashboard_setup_display' ),
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
	
	public function wp_dashboard_setup_display( $post, $args ) {
		
		$display_period_title = '';
		$compare_period_title = '';
		
		// Determine the date range for the display period
	    switch( $args['args']['display_period'] ) {
		    
		    case 'this week':
		    	$range = $this->get_range_week('this week');
		    	$display_period_title = 'this week';
		    	break;
		    
		    case 'last week':
		    	$range = $this->get_range_week('last week');
		    	$display_period_title = 'last week';
		    	break;
		    
		    case 'this month':
		    	$range = $this->get_range_month('this month');
		    	$display_period_title = 'this month';
		    	break;
		    
		    case 'last month':
		    	$range = $this->get_range_month('last month');
		    	$display_period_title = 'last month';
		    	break;
		    
		    case 'this year':
		    	$range = $this->get_range_year('this year');
		    	$display_period_title = 'this year';
		    	break;
		    
		    case 'last year':
		    	$range = $this->get_range_year('last year');
		    	$display_period_title = 'last year';
		    	break;
		    
		    case 'weekly-avg-this-year':
		    	$range = $this->get_range_year('this year');
		    	$display_period_title = 'weekly avg this year';
		    	break;
		    	
		    case 'weekly-avg-last-year':
		    	$range = $this->get_range_year('last year');
		    	$display_period_title = 'weekly avg last year';
		    	break;
		    
		    case 'monthly-avg-this-year':
		    	$range = $this->get_range_monthly_avg_year('this year');
		    	$display_period_title = 'monthly avg this year';
		    	break;
		    
		    case 'monthly-avg-last-year':
		    	$range = $this->get_range_year('last year');
		    	$display_period_title = 'monthly avg last year';
		    	break;
		    
		    case 'weekly-avg-last-year-yoy':
		    	$range = $this->get_range_year_weekly_yoy('last year');
		    	$display_period_title = 'weekly avg last yr (yoy)';
		    	break;
		    
		    case 'monthly-avg-last-year-yoy':
		    	$range = $this->get_range_monthly_avg_year('last year');
		    	$display_period_title = 'monthly avg last yr (yoy)';
		    	break;
		    
		    default:
		    	$range = $this->get_range_week('this week');
		    	$display_period_title = 'this week';
		    	break;
		    
	    }
	    
	    // Determine the date range for the compare period
	    switch( $args['args']['compare_period'] ) {
		    
		    case 'last week':
		    	$compare_range = $this->get_range_week('last week');
		    	$compare_period_title = 'last week';
		    	break;
		    
		    case 'this week last year':
		    	$compare_range = $this->get_range_week('this week last year');
		    	$compare_period_title = 'this week last year';
		    	break;
		    
		    case 'last week last year':
		    	$compare_range = $this->get_range_week('last week last year');
		    	$compare_period_title = 'last week last year';
		    	break;
		    
		    case 'last month':
		    	$compare_range = $this->get_range_month('last month' );
		    	$compare_period_title = 'last month';
		    	break;
		    
		    case 'this month last year':
		    	$compare_range = $this->get_range_month('this month last year' );
		    	$compare_period_title = 'this month last year';
		    	break;
		    
		    case 'last month last year':
		    	$compare_range = $this->get_range_month('last month last year' );
		    	$compare_period_title = 'last month last year';
		    	break;
		    
		    case 'last year':
		    	$compare_range = $this->get_range_year('last year' );
		    	$compare_period_title = 'last year';
		    	break;
		    
		    case 'weekly-avg-last-year':
		    	$compare_range = $this->get_range_year('last year');
		    	$compare_period_title = 'weekly avg last year';
		    	break;
		    
		    case 'monthly-avg-last-year':
		    	$compare_range = $this->get_range_year('last year');
		    	$compare_period_title = 'monthly avg last year';
		    	break;
		    
		    case 'weekly-avg-last-year-yoy':
		    	$compare_range = $this->get_range_year_weekly_yoy('last year');
		    	$compare_period_title = 'weekly avg last yr (yoy)';
		    	break;
		    
		    case 'monthly-avg-last-year-yoy':
		    	$compare_range = $this->get_range_monthly_avg_year('last year');
		    	$compare_period_title = 'monthly avg last yr (yoy)';
		    	break;
		    
		    default:
		    	$compare_range = $this->get_range_week('last week');
		    	$compare_period_title = 'last week';
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
		
		if ( ! is_array( $args['args']['category_id'] ) ) {
			$args['args']['category_id'] = explode( ',', $args['args']['category_id'] );
		}
		
		// Define our initial values
		$value_count = 0;
		$compare_value_count = 0;
		$value_format = '';
		
		foreach ( $args['args']['category_id'] as $category ) {
			
			// Define our request arguments
			$request_args = array(
				'campus_id'			=> $args['args']['campus_id'],
				'category_id'		=> $category,
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
			
			// Loop through each record and add up the values
			foreach( $cm_body as $record ) {
				$value_count += $record->value;
			}
			
		}
		
		// Calculate the average
		switch( $args['args']['display_period'] ) {
			
			case 'weekly-avg-this-year':
				$value_count = $value_count / date( 'W' , strtotime('today') );
				break;
			
			case 'weekly-avg-last-year':
				$value_count = $value_count / 52;
				break;
			
			case 'monthly-avg-this-year':
				$months_to_date = date( 'n', strtotime('today') ) - 1;
				if ( $months_to_date > 0 ) {
					$value_count = $value_count / $months_to_date;
				} else {
					$value_count = 'No data';
				}
				break;
			
			case 'monthly-avg-last-year':
				$value_count = $value_count / 12;
				break;
			
			case 'weekly-avg-last-year-yoy':
				$value_count = $value_count / date( 'W' , strtotime('today last year') );
				break;
			
			case 'monthly-avg-last-year-yoy':
				$months_to_date = date( 'n', strtotime('today last year') ) - 1;
				if ( $months_to_date > 0 ) {
					$value_count = $value_count / $months_to_date;
				} else {
					$value_count = 'No data';
				}
				break;
			
		}
		
		// Define our initial formatted value
		$value_count_formatted = $value_count;
		
		// Make sure that there is at least one record
		if ( is_array( $cm_body ) && count( $cm_body ) > 0 && $value_count != 'No data' ) {
			
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
			
			foreach ( $args['args']['category_id'] as $category ) {
			
				// Define our request arguments
				$request_args = array(
					'campus_id'			=> $args['args']['campus_id'],
					'category_id'		=> $category,
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
			
			}
			
			// Calculate the average
			switch( $args['args']['compare_period'] ) {
				
				case 'weekly-avg-last-year':
					$compare_value_count = $compare_value_count / 52;
					break;
					
				case 'monthly-avg-last-year':
					$compare_value_count = $compare_value_count / 12;
					break;
				
				case 'weekly-avg-last-year-yoy':
					$compare_value_count = $compare_value_count / date( 'W' , strtotime('today last year') );
					break;
				
				case 'monthly-avg-last-year-yoy':
					$months_to_date = date( 'n', strtotime('today last year') ) - 1;
					if ( $months_to_date > 0 ) {
						$compare_value_count = $compare_value_count / $months_to_date;
					} else {
						$compare_value_count = 'No data';
					}
					break;
				
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
		$difference = $this->percentage_change( $value_count, $compare_value_count );
		
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
				<?php echo $display_period_title; ?>
				<?php if ( $args['args']['compare_period'] != 'nothing' ) : ?>
					<span class="cm_dash_widgets_diff_<?php echo $difference['trend']; ?>"><?php echo $difference_icon; ?><?php echo $difference['diff']; ?></span>
				<?php endif; ?>
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
					<?php echo $compare_period_title; ?>
				</div>
				<div class="cm_dash_widgets_compare_value">
					<?php echo $compare_value_count_formatted; ?>
				</div>
			</div>
			<?php
		}
		
	}
	
	/**
	 * Determine the first and last days of the specified week
	 *
	 * @param	string	$datestr	String represenging the week. eg. 'this week'
	 *
	 * @return	array	An array containing the 'start' and 'end' dates
	 */
	
	public function get_range_week( $datestr ) {
		
		// Set the default timezone
		date_default_timezone_set( date_default_timezone_get() );
		
		// Convert $datestr to an actual date
		$dt = strtotime( $datestr );
		
		// Populate the array with the appropriate values
		$res['start']	= date( 'N', $dt ) == 7 ? gmdate( 'Y-m-d\T00:00:00\Z', $dt ) : gmdate( 'Y-m-d\T00:00:00\Z', strtotime( 'last sunday', $dt ) );
		$res['end']		= date( 'N', $dt ) == 6 ? gmdate( 'Y-m-d\T23:59:59\Z', $dt ) : gmdate( 'Y-m-d\T23:59:59\Z', strtotime( 'next saturday', $dt ) );
		
		// Return the array
		return $res;
		
	}
	
	/**
	 * Determine the first and last days of the specified month
	 *
	 * @param	string	$datestr	String representing the month. eg. 'this month'
	 *
	 * @return	array	An array containing the 'start' and 'end' dates
	 */
	
	public function get_range_month( $datestr ) {
		
		// Set the default timezone
		date_default_timezone_set( date_default_timezone_get() );
		
		// Convert $datestr to an actual date
		$dt = strtotime( $datestr );
		
		// Populate the array with the appropriate values
		$res['start']	= gmdate( 'Y-m-d\T00:00:00\Z', strtotime( 'first day of this month', $dt ) );
		$res['end']		= gmdate( 'Y-m-d\T23:59:59\Z', strtotime( 'last day of this month', $dt ) );
		
		// Return the array
		return $res;
	}
	
	/**
	 * Determine the first and last days of the specified year
	 *
	 * @param	string	$datestr	String representing the year. eg. 'this year'
	 *
	 * @return 	array	An array containing the 'start' and 'end' dates
	 */
	 
	function get_range_year( $datestr ) {
		
		// Set the default timezone
		date_default_timezone_set( date_default_timezone_get() );
		
		// Convert $datestr to an actual date
		$dt = strtotime( $datestr );
		
		// Populate the array with the appropriate values
		$res['start']	= gmdate( 'Y-m-d\T00:00:00\Z', strtotime( 'first day of january', $dt ) );
		$res['end']		= gmdate( 'Y-m-d\T23:59:59\Z', strtotime( 'last day of december', $dt ) );
		
		// Return the array
		return $res;
		
	}
	
	/**
	 * Determine the first and last days of the specified year
	 *
	 * @param	string	$datestr	String representing the year. eg. 'last year'
	 *
	 * @return 	array	An array containing the 'start' and 'end' dates
	 */
	 
	function get_range_year_weekly_yoy( $datestr ) {
		
		// Set the default timezone
		date_default_timezone_set( date_default_timezone_get() );
		
		// Convert $datestr to an actual date
		$dt = strtotime( 'this week ' . $datestr );
		
		// Populate the array with the appropriate values
		$res['start']	= gmdate( 'Y-m-d\T00:00:00\Z', strtotime( 'first day of january', $dt ) );
		$res['end']		= date( 'N', $dt ) == 6 ? gmdate( 'Y-m-d\T23:59:59\Z', $dt ) : gmdate( 'Y-m-d\T23:59:59\Z', strtotime( 'next saturday', $dt ) );
		
		// Return the array
		return $res;
		
	}
	
	/**
	 * Determine the first and last days of the specified year
	 *
	 * @return 	array	An array containing the 'start' and 'end' dates
	 */
	 
	function get_range_monthly_avg_year( $datestr ) {
		
		// Set the default timezone
		date_default_timezone_set( date_default_timezone_get() );
		
		// Convert $datestr to an actual date
		$dt = strtotime( 'last month ' . $datestr );
		
		// Populate the array with the appropriate values
		$res['start']	= gmdate( 'Y-m-d\T00:00:00\Z', strtotime( 'first day of january', $dt ) );
		$res['end']		= gmdate( 'Y-m-d\T23:59:59\Z', strtotime( 'last day of this month', $dt ) );
		
		// Return the array
		return $res;
		
	}
	
	/**
	 * Determine the difference between two numbers
	 *
	 * @param	int	$cur	Integer representing the current number
	 * @param	int	$prev	Integer representing the previous number
	 *
	 * @return	array	An array containing 'diff' and 'trend'
	 */
	
	public function percentage_change( $cur, $prev ) {
		
		// Check if the current number is 0
		if ( $cur == 0 ) {
			
			// Check if the previous number is 0
			if ( $prev == 0 ) {
				
				// There was no change
				return array('diff' => 0, 'trend' => '');
				
			}
			
			// The trend was 'down'
			return array('diff' => -( $prev * 100 ), 'trend' => 'down');
			
		}
		
		// Check if the previous number was 0
		if ( $prev == 0 ) {
			
			// The trend was 'up'
			return array('diff' => $cur * 100, 'trend' => 'up');
			
		}
		
		// Calculate the difference between the two numbers
		$difference = ( $cur - $prev ) / $prev * 100;
		
		// Setup our trend direction
		$trend = '';
		
		// Compare the numbers to determine the trend
		if ( $cur > $prev ) {
			$trend = 'up';
		} else if ( $cur < $prev ) {
			$trend = 'down';
		}
		
		// Return the data
		return array(
			'diff'	=> abs( round( $difference, 0 ) ),
			'trend'	=> $trend
		);
		
	}
	
	/**
	 * Define the post updated messages
	 */
	
	public function post_updated_messages( $messages ) {
		
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
		
	}
	
	/**
	 * Setup the Custom Post Type
	 */
	
	public function setup_cpt() {
		
		$labels = array(
			'name'                => _x( 'Church Metrics', 'Post Type General Name', 'church-metrics-dashboard' ),
			'singular_name'       => _x( 'Metric', 'Post Type Singular Name', 'church-metrics-dashboard' ),
			'menu_name'           => __( 'Church Metrics', 'church-metrics-dashboard' ),
			'name_admin_bar'      => __( 'Church Metrics', 'church-metrics-dashboard' ),
			'parent_item_colon'   => __( 'Parent Metric:', 'church-metrics-dashboard' ),
			'all_items'           => __( 'All Metrics', 'church-metrics-dashboard' ),
			'add_new_item'        => __( 'Add New Metric', 'church-metrics-dashboard' ),
			'add_new'             => __( 'Add New Metric', 'cm-dash-widgets' ),
			'new_item'            => __( 'New Church Metric', 'church-metrics-dashboard' ),
			'edit_item'           => __( 'Edit Church Metric', 'church-metrics-dashboard' ),
			'update_item'         => __( 'Update Church Metric', 'church-metrics-dashboard' ),
			'view_item'           => __( 'View Church Metric', 'church-metrics-dashboard' ),
			'search_items'        => __( 'Search Church Metrics', 'church-metrics-dashboard' ),
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
						
						$campuses = get_post_meta( get_the_ID(), '_cm_dash_widgets_campus', true );
						if ( ! is_array( $campuses ) ) {
							$campuses = explode( ',', $campuses );
						}
						
						foreach ( $campuses as $campus ) {
							
							// Define our request arguments
							$cm_request_args = array(
								'id' => $campus,
							);
							
							// Request the data from the API
							$cm_request = $cm->campus( $cm_request_args );
							$cm_body = wp_remote_retrieve_body( $cm_request );
							$cm_body = json_decode( $cm_body );
							echo $cm_body->slug . '<br />';
							
							// Free up some memory
							unset( $cm_body, $cm_request, $cm_request_args );
							
						}
						
						// Free up some memory
						unset( $cm, $cm_args );
	
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
						
						$categories = get_post_meta( get_the_ID(), '_cm_dash_widgets_category', true );
						if ( ! is_array( $categories ) ) {
							$categories = explode( ',', $categories );
						}
						
						foreach ( $categories as $category ) {
							
							// Define our request arguments
							$cm_request_args = array(
								'id' => $category,
							);
							
							// Request the data from the API
							$cm_request = $cm->category( $cm_request_args );
							$cm_body = wp_remote_retrieve_body( $cm_request );
							$cm_body = json_decode( $cm_body );
							echo $cm_body->name . '<br />';
							
							// Free up some memory
							unset( $cm_body, $cm_request, $cm_request_args );

							
						}
						
						// Free up some memory
						unset( $cm, $cm_args );
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
				'dashboard' => array(
					'title' => '<span class="dashicons dashicons-dashboard" title="Show on Dashboard"></span>',
					'function' => function() {
						switch( get_post_meta( get_the_ID(), '_cm_dash_widgets_dashboard_show', true ) ) {
							case 'yes':
								echo '<span class="dashicons dashicons-yes"></span>';
								break;
							
							default:
								break;
						}
					},
				),
			),
			
		),
		array(
			'singular'	=> 'Dashboard Widget',
			'plural'	=> 'Dashboard Widgets',
		) );
		
	}
		
}