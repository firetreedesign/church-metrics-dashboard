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
					$compare_to			= get_post_meta( get_the_ID(), '_cm_dash_widgets_compare_to', true );
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
								'compare_to'		=> $compare_to,
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
				$compare_to			= get_post_meta( get_the_ID(), '_cm_dash_widgets_compare_to', true );
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
						'compare_to'		=> $compare_to,
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
	    
	    // Determine if there is an event id
	    $event_id = NULL;
	    if ( strlen( $args['args']['event_id'] ) > 0 ) {
		    $event_id = $args['args']['event_id'];
	    }
	    		
		if ( ! is_array( $args['args']['category_id'] ) ) {
			$args['args']['category_id'] = explode( ',', $args['args']['category_id'] );
		}
		
		if ( is_array( $args['args']['compare_to'] ) ) {
			
			foreach ( (array) $args['args']['compare_to'] as $key => $period ) {
				
				// Determine the date range for the display period
			    $range			= $this->get_range( $args['args']['display_period'] );
			    $compare_range	= $this->get_range( $period['compare_period'] );
				
				$count_args = array(
					'display_period'	=> $args['args']['display_period'],
					'campus_id'			=> $args['args']['campus_id'],
					'category_id'		=> $args['args']['category_id'],
					'event_id'			=> $event_id,
					'start_time'		=> $range['start'],
					'end_time'			=> $range['end'],
				);
		
				$value_data			= $this->count( $count_args );
				$formatted_value	= $this->format_value( $value_data['value'], $value_data['format'] );
			
				$count_args = array(
					'display_period'	=> $period['compare_period'],
					'campus_id'			=> $args['args']['campus_id'],
					'category_id'		=> $args['args']['category_id'],
					'event_id'			=> $event_id,
					'start_time'		=> $compare_range['start'],
					'end_time'			=> $compare_range['end'],
				);
				
				$compare_value_data			= $this->count( $count_args );
				$formatted_compare_value	= $this->format_value( $compare_value_data['value'], $compare_value_data['format'] );

				// Get the difference between the two values
				$difference = $this->percentage_change( $value_data['value'], $compare_value_data['value'] );
				
				$data = array(
					'display_period'		=> $args['args']['display_period'],
					'display_period_title'	=> $this->period_desc( $args['args']['display_period'] ),
					'trend'					=> $difference['trend'],
					'trend_icon'			=> $difference['icon'],
					'difference'			=> $difference['diff'],
					'value_count'			=> $value_data['value'],
					'value_count_formatted'	=> $formatted_value,
					'compare_period'		=> $period['compare_period'],
					'compare_period_title'	=> $this->period_desc( $period['compare_period'] ),
					'compare_value_count'	=> $compare_value_data['value'],
					'compare_value_count_formatted'	=> $formatted_compare_value,
				);
		
				// Output our data
				echo $this->output( $data );
				
			}
			
		} else {
			
			// Determine the date range for the display period
		    $range			= $this->get_range( $args['args']['display_period'] );
		    $compare_range	= $this->get_range( $args['args']['compare_period'] );
			
			$count_args = array(
				'display_period'	=> $args['args']['display_period'],
				'campus_id'			=> $args['args']['campus_id'],
				'category_id'		=> $args['args']['category_id'],
				'event_id'			=> $event_id,
				'start_time'		=> $range['start'],
				'end_time'			=> $range['end'],
			);
	
			$value_data			= $this->count( $count_args );
			$formatted_value	= $this->format_value( $value_data['value'], $value_data['format'] );
			
			// Check if we are comparing any values
			if ( $args['args']['compare_period'] != 'nothing' ) {
				
				$count_args = array(
					'display_period'	=> $args['args']['compare_period'],
					'campus_id'			=> $args['args']['campus_id'],
					'category_id'		=> $args['args']['category_id'],
					'event_id'			=> $event_id,
					'start_time'		=> $compare_range['start'],
					'end_time'			=> $compare_range['end'],
				);
				
				$compare_value_data			= $this->count( $count_args );
				$formatted_compare_value	= $this->format_value( $compare_value_data['value'], $compare_value_data['format'] );
				
				// Get the difference between the two values
				$difference = $this->percentage_change( $value_data['value'], $compare_value_data['value'] );
				
			}
			
			if ( ! isset( $difference ) ) {
				$difference = array(
					'trend'	=> '',
					'diff'	=> '',
					'icon'	=> '',
				);
			}
					
			$data = array(
				'display_period'		=> $args['args']['display_period'],
				'display_period_title'	=> $this->period_desc( $args['args']['display_period'] ),
				'trend'					=> $difference['trend'],
				'trend_icon'			=> $difference['icon'],
				'difference'			=> $difference['diff'],
				'value_count'			=> $value_data['value'],
				'value_count_formatted'	=> $formatted_value,
				'compare_period'		=> $args['args']['compare_period'],
				'compare_period_title'	=> $this->period_desc( $args['args']['compare_period'] ),
				'compare_value_count'	=> ( isset( $compare_value_data['value'] ) ) ? $compare_value_data['value'] : '',
				'compare_value_count_formatted'	=> ( isset( $formatted_compare_value ) ) ? $formatted_compare_value : '',
			);
	
			// Output our data
			echo $this->output( $data );
			
		}
		
	}
	
	/**
	 * Generate the output
	 *
	 * @param	array	$data	Array of data to display
	 *
	 * @return 	string	HTML output.
	 */
	
	public function output( $data ) {
		ob_start(); ?>
		<div>
			<div class="cm_dash_widgets_container">
				<div class="cm_dash_widgets_heading">
					<?php echo $data['display_period_title']; ?>
					<?php if ( $data['trend'] != '' ) : ?>
						<span class="cm_dash_widgets_diff_<?php echo $data['trend']; ?>"><?php echo $data['trend_icon']; ?><?php echo $data['difference']; ?></span>
					<?php endif; ?>
				</div>
				<div class="cm_dash_widgets_value">
					<?php echo $data['value_count_formatted']; ?>
				</div>
			</div>
			<?php if ( $data['compare_period'] != 'nothing' ) : ?>
			<div class="cm_dash_widgets_container">
				<div class="cm_dash_widgets_heading">
					<?php echo $data['compare_period_title']; ?>
				</div>
				<div class="cm_dash_widgets_compare_value">
					<?php echo $data['compare_value_count_formatted']; ?>
				</div>
			</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
	
	/**
	 * Count our records
	 *
	 * @param	array	$data	An array of parameters to define our data
	 *
	 * @return 	int	The total count.
	 */
	
	public function count( $data ) {
		
		// Define our Church Metrics arguments
	    $cm_args = array(
			'user'	=> cm_dash_widgets_get_option('user'),
			'key'	=> cm_dash_widgets_get_option('key'),
		);
		
		// Initialize the Church Metrics API class
		$cm = new WP_Church_Metrics( $cm_args );
		
		$value_count = 0;
		
		foreach ( $data['category_id'] as $category ) {
			
			// Define our request arguments
			$request_args = array(
				'campus_id'			=> $data['campus_id'],
				'category_id'		=> $category,
				'event_id'			=> $data['event_id'],
				'start_time'		=> $data['start_time'],
				'end_time'			=> $data['end_time'],
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
		$value_count = $this->average( $data['display_period'], $value_count );
		
		$value_format = '';
		
		// Make sure that there is at least one record
		if ( is_array( $cm_body ) && count( $cm_body ) > 0 && $value_count != 'No data' ) {
			
			// Determine the value format from the first record
			$value_format = $cm_body[0]->category->format;
			
			
		}
		
		// Free up some memory
		unset( $cm, $cm_args, $cm_request, $cm_body );
		
		$return_data = array(
			'value'		=> $value_count,
			'format'	=> $value_format,
		);
		
		return $return_data;
		
	}
	
	/**
	 * Calculate the average
	 *
	 * @param	$display_period	string	The display period to average.
	 * @param	$value			mixed	The value to average.
	 *
	 * @return	int	The average value.
	 */
	
	public function average( $display_period, $value ) {
		
		switch( $display_period ) {
			
			case 'weekly-avg-this-year':
				$value = $value / date( 'W' , strtotime('today') );
				break;
			
			case 'weekly-avg-last-year':
				$value = $value / 52;
				break;
			
			case 'monthly-avg-this-year':
				$months_to_date = date( 'n', strtotime('today') ) - 1;
				if ( $months_to_date > 0 ) {
					$value = $value / $months_to_date;
				} else {
					$value = 'No data';
				}
				break;
			
			case 'monthly-avg-last-year':
				$value = $value / 12;
				break;
			
			case 'weekly-avg-last-year-yoy':
				$value = $value / date( 'W' , strtotime('today last year') );
				break;
			
			case 'monthly-avg-last-year-yoy':
				$months_to_date = date( 'n', strtotime('today last year') ) - 1;
				if ( $months_to_date > 0 ) {
					$value = $value / $months_to_date;
				} else {
					$value = 'No data';
				}
				break;
			
		}
		
		return $value;
		
	}
	
	/**
	 * Format the value
	 *
	 * @param	$value	The value to format.
	 * @param	$format	The format to convert to.
	 *
	 * @return	string	The formatted value.
	 */
	
	public function format_value( $value, $format ) {
		
		switch( $format ) {
			case 'currency':
				return '$' . number_format( $value );
				break;
			default:
				return (string)number_format( $value );
				break;
		}
		
	}
	
	/**
	 * Get the range
	 *
	 * @param	string	$range	The range to retrieve.
	 *
	 * @return	array	An array contains the date range.
	 */
	
	public function get_range( $range ) {
		
		switch( $range ) {
		    
		    case 'this week':
		    	return $this->get_range_week('this week');
		    	break;
		    
		    case 'last week':
		    	return $this->get_range_week('last week');
		    	break;
		    
		    case 'this month':
		    	return $this->get_range_month('this month');
		    	break;
		    
		    case 'last month':
		    	return $this->get_range_month('last month');
		    	break;
		    
		    case 'this year':
		    	return $this->get_range_year('this year');
		    	break;
		    
		    case 'last year':
		    	return $this->get_range_year('last year');
		    	break;
		    
		    case 'weekly-avg-this-year':
		    	return $this->get_range_year('this year');
		    	break;
		    	
		    case 'weekly-avg-last-year':
		    	return $this->get_range_year('last year');
		    	break;
		    
		    case 'monthly-avg-this-year':
		    	return $this->get_range_monthly_avg_year('this year');
		    	break;
		    
		    case 'monthly-avg-last-year':
		    	return $this->get_range_year('last year');
		    	break;
		    
		    case 'weekly-avg-last-year-yoy':
		    	return $this->get_range_year_weekly_yoy('last year');
		    	break;
		    
		    case 'monthly-avg-last-year-yoy':
		    	return $this->get_range_monthly_avg_year('last year');
		    	break;
		    
		    case 'this week last year':
		    	return $this->get_range_week('this week last year');
		    	break;
		    
		    case 'last week last year':
		    	return $this->get_range_week('last week last year');
		    	break;
		    
		    case 'this month last year':
		    	return $this->get_range_month('this month last year' );
		    	break;
		    
		    case 'last month last year':
		    	return $this->get_range_month('last month last year' );
		    	break;
		    
		    default:
		    	return $this->get_range_week('this week');
		    	break;
		    
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
				return array(
					'diff'	=> '',
					'trend'	=> '',
					'icon'	=> '',
				);
				
			}
			
			// The trend was 'down'
			return array(
				'diff'	=> '',
				'trend'	=> '',
				'icon'	=> '',
			);
			
		}
		
		// Check if the previous number was 0
		if ( $prev == 0 ) {
			
			// The trend was 'up'
			return array(
				'diff'	=> '',
				'trend'	=> '',
				'icon'	=> '',
			);
			
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
			'diff'	=> abs( round( $difference, 0 ) ) . '%',
			'trend'	=> $trend,
			'icon'	=> '<span class="dashicons dashicons-arrow-' . $trend . '"></span>',
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
						//echo ucwords( get_post_meta( get_the_ID(), '_cm_dash_widgets_display_period', true ) );
						echo ucwords( $this->period_desc( get_post_meta( get_the_ID(), '_cm_dash_widgets_display_period', true ) ) );
					},
				),
				'compare_to'	=> array(
					'title'		=> __( 'Compare To', 'church-metrics-dashboard' ),
					'function'	=> function() {
						$compare_period = get_post_meta( get_the_ID(), '_cm_dash_widgets_compare_period', true );
						if ( $compare_period == 'nothing' ) {
							$compare_to = get_post_meta( get_the_ID(), '_cm_dash_widgets_compare_to', true );
							foreach ( (array) $compare_to as $key => $period ) {
								echo ucwords( $this->period_desc( $period['compare_period'] ) ) . '<br />';
							}
						} else {
							echo ucwords( $this->period_desc( get_post_meta( get_the_ID(), '_cm_dash_widgets_compare_period', true ) ) );
						}
		
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
	
	/**
	 * Gets the description for a period
	 *
	 * @param	string	$period	The period to return a description for.
	 *
	 * @return	string	The period description.
	 */
	
	public function period_desc( $period ) {
		
		switch ( $period ) {
			
			case 'this week':
		    	return 'this week';
		    	break;
		    
		    case 'this week last year':
		    	return 'this week last year';
		    	break;
		    
		    case 'last week':
		    	return 'last week';
		    	break;
		    
		    case 'last week last year':
		    	return 'last week last year';
		    	break;
		    
		    case 'this month':
		    	return 'this month';
		    	break;
		    
		    case 'this month last year':
		    	return 'this month last year';
		    	break;
		    
		    case 'last month':
		    	return 'last month';
		    	break;
		    
		    case 'last month last year':
		    	return 'last month last year';
		    	break;
		    
		    case 'this year':
		    	return 'this year';
		    	break;
		    
		    case 'last year':
		    	return'last year';
		    	break;
		    
		    case 'weekly-avg-this-year':
		    	return 'weekly avg this year';
		    	break;
		    	
		    case 'weekly-avg-last-year':
		    	return 'weekly avg last year';
		    	break;
		    
		    case 'weekly-avg-last-year-yoy':
		    	return 'weekly avg last yr (yoy)';
		    	break;
		    
		    case 'monthly-avg-this-year':
		    	return 'monthly avg this year';
		    	break;
		    
		    case 'monthly-avg-last-year':
		    	return 'monthly avg last year';
		    	break;
		    
		    case 'monthly-avg-last-year-yoy':
		    	return 'monthly avg last yr (yoy)';
		    	break;
		    
		    case 'all-time':
		    	return 'all time';
		    	break;
		    
		    default:
		    	return '';
		    	break;
			
		}
		
	}
	
}