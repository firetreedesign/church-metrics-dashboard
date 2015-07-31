<?php
if ( ! class_exists( 'WP_Church_Metrics' ) ) :

	/**
	 * WP Church Metrics
	 *
	 * Helps with retrieving data from Church Metrics.
	 *
	 * @version 1.0.0
	 */
	
	class WP_Church_Metrics {
		
		private $api_url;
		private $auth_user;
		private $auth_key;
		private $cache_prefix;
		
		
		/**
		 * Create a new instance
		 *
		 * @param	array	$args	An array of the arguments.
		 */
		 
		function __construct( $args ) {
			
			// Set the defaults
			$defaults = array(
				'api_url'		=> 'https://churchmetrics.com/api/v1',
				'user'			=> NULL,
				'key'			=> NULL,
				'cache_prefix'	=> 'wpcm_',
			);
			
			// Parse the arguments
			$args = wp_parse_args( $args, $defaults );
			
			// Populate our variables
			$this->api_url		= $args['api_url'];
			$this->auth_user	= $args['user'];
			$this->auth_key		= $args['key'];
			$this->cache_prefix	= $args['cache_prefix'];
			
		}
		
		/**
		 * GET the data from Church Metrics.
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function get( $args ) {
			
			// Set the defaults
			$defaults = array(
				'url'	=> NULL,
				'cache'	=> 60 * MINUTE_IN_SECONDS,
			);
			
			// Parse the arguments
			$args = wp_parse_args( $args, $defaults );
			
			// Retrieve the transient data
			$transient_name = md5( $args['url'] );
			$data = get_transient( $this->cache_prefix . $transient_name );
			
			// Check for a cached copy in the transient data
			if ( false === $data ) {
				
				$get_args = array(
					'headers'	=> array(
						'X-Auth-User'	=> $this->auth_user,
						'X-Auth-Key'	=> $this->auth_key,
					),
					'timeout'	=> 300,
				);
				
				$response = wp_remote_get( $args['url'], $get_args );
				if ( ! is_wp_error( $response ) ) {
					
					$data = $response;
					set_transient( $this->cache_prefix . $transient_name, $data, $args['cache'] );
					
				} else {
					
					$data = 'WordPress encountered an error.';
				
				}
				
			}
				
			return $data;
			
		}
		
		/**
		 * Retrieve the appropriate link from the header
		 *
		 * @param	string	$data	A JSON string containing the data.
		 * @param	string	$direction	next, last, first, prev.
		 *
		 * @return	string	A URL string.
		 */
		
		public function get_link( $data, $direction ) {
			
			$link_header = wp_remote_retrieve_header( $data, 'link' );
			if ( ! $link_header ) {
				return false;
			}
			$links = explode( ",", $link_header );
			
			foreach( $links as $link ) {
				$link_data = explode( "; ", $link );
				if ( $link_data[1] == "rel='" . $direction . "'" ) {
					$link_data[0] = str_replace( '<', '', $link_data[0] );
					$link_data[0] = str_replace( '>', '', $link_data[0] );
					return $link_data[0];
				}
			}
			
			return false;
			
		}
		
		/**
		 * Get the campuses
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		 
		public function campuses( $args = array() ) {
		 
			$api_path = '/campuses.json';
			
			// Set the defaults
			$defaults = array(
				'cache'	=> 1 * DAY_IN_SECONDS,
			);
			 
			$args = wp_parse_args( $args, $defaults );
			 
			try {
				 
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				 
				// GET the data
				$data = $this->get( $data_args );
				 
				return $data;
				 
			} catch ( Exception $e ) {
				 
				return false;
				 
			}
		 
		}
		 
		/**
		 * Get a specific campus
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function campus( $args = array() ) {
			
			$api_path = '/campuses/%d.json';
			
			// Set the defaults
			$defaults = array(
				'id' => NULL,
				'cache'	=> 1 * DAY_IN_SECONDS,
			);
			
			$args = wp_parse_args( $args, $defaults );
			
			// Return FALSE if id is NULL
			if ( is_null( $args['id'] ) ) {
				return false;
			}
			
			// Insert the campus id into the string
			$api_path = sprintf( $api_path, $args['id'] );
			
			try {
				
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				
				// GET the data
				$data = $this->get( $data_args );
				
				return $data;
				
			} catch( Exception $e ) {
				
				return false;
				
			}
			
		}
		
		/**
		 * Get campus weekly totals
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function campus_weekly_totals( $args = array() ) {
			
			$api_path = '/campuses/%d/weekly_totals.json';
			
			// Set the defaults
			$defaults = array(
				'id'				=> NULL,
				'category_id'		=> NULL,
				'week_reference'	=> NULL,
				'cache'				=> 60 * MINUTE_IN_SECONDS,
			);
			
			$args = wp_parse_args( $args, $defaults );
			
			// Return FALSE if id or category_id are NULL
			if ( is_null( $args['id'] ) || is_null( $args['category_id'] ) ) {
				return false;
			}
			
			// Insert the category id into the string
			$api_path = sprintf( $api_path, $args['id'] );
			
			// Add the category_id query arg
			$api_path = add_query_arg( 'category_id', $args['category_id'], $api_path );
			
			// Add the week_reference query arg
			if ( ! is_null( $args['week_reference'] ) ) {
				$api_path = add_query_arg( 'week_reference', $args['week_reference'], $api_path );
			}
			
			try {
				
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				
				// GET the data
				$data = $this->get( $data_args );
				
				return $data;
				
			} catch( Exception $e ) {
				
				return false;
				
			}
			
		}
		
		/**
		 * Get the categories
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function categories( $args = array() ) {
			
			$api_path = '/categories.json';
			
			// Set the defaults
			$defaults = array(
				'cache'	=> 1 * DAY_IN_SECONDS,
			);
			 
			$args = wp_parse_args( $args, $defaults );
			 
			try {
				 
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				 
				// GET the data
				$data = $this->get( $data_args );
				 
				return $data;
				 
			} catch ( Exception $e ) {
				 
				return false;
				 
			}
			
		}
		
		/**
		 * Get a specific category
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function category( $args = array() ) {
			
			$api_path = '/categories/%d.json';
			
			// Set the defaults
			$defaults = array(
				'id'	=> NULL,
				'cache'	=> 1 * DAY_IN_SECONDS,
			);
			
			$args = wp_parse_args( $args, $defaults );
			
			// Return FALSE if id is NULL
			if ( is_null( $args['id'] ) ) {
				return false;
			}
			
			// Insert the variables into the string
			$api_path = sprintf( $api_path, $args['id'] );
			
			try {
				
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				
				// GET the data
				$data = $this->get( $data_args );
				
				return $data;
				
			} catch( Exception $e ) {
				
				return false;
				
			}
			
		}
		
		/**
		 * Get the events
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function events( $args = array() ) {
			
			$api_path = '/events.json';
			
			// Set the defaults
			$defaults = array(
				'cache'	=> 60 * MINUTE_IN_SECONDS,
			);
			 
			$args = wp_parse_args( $args, $defaults );
			 
			try {
				 
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				 
				// GET the data
				$data = $this->get( $data_args );
				 
				return $data;
				 
			} catch ( Exception $e ) {
				 
				return false;
				 
			}
			
		}
		
		/**
		 * Get a specific event
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function event( $args = array() ) {
			
			$api_path = '/events/%d.json';
			
			// Set the defaults
			$defaults = array(
				'id'	=> NULL,
				'cache'	=> 1 * DAY_IN_SECONDS,
			);
			
			$args = wp_parse_args( $args, $defaults );
			
			// Return FALSE if id is NULL
			if ( is_null( $args['id'] ) ) {
				return false;
			}
			
			// Insert the event id into the string
			$api_path = sprintf( $api_path, $args['id'] );
			
			try {
				
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				
				// GET the data
				$data = $this->get( $data_args );
				
				return $data;
				
			} catch( Exception $e ) {
				
				return false;
				
			}
			
		}
		
		/**
		 * Get the organization
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function organization( $args = array() ) {
			
			$api_path = '/organizations/me.json';
			
			// Set the defaults
			$defaults = array(
				'cache'	=> 1 * DAY_IN_SECONDS,
			);
			 
			$args = wp_parse_args( $args, $defaults );
			 
			try {
				 
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				 
				// GET the data
				$data = $this->get( $data_args );
				 
				return $data;
				 
			} catch ( Exception $e ) {
				 
				return false;
				 
			}
			
		}
		
		/**
		 * Get organization weekly totals
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function organization_weekly_totals( $args = array() ) {
			
			$api_path = '/organizations/weekly_totals.json';
			
			// Set the defaults
			$defaults = array(
				'category_id'		=> NULL,
				'week_reference'	=> NULL,
				'cache'	=> 60 * MINUTE_IN_SECONDS,
			);
			
			$args = wp_parse_args( $args, $defaults );
			
			// Return FALSE if id or category_id are NULL
			if ( is_null( $args['category_id'] ) ) {
				return false;
			}
			
			// Add the category_id query arg
			$api_path = add_query_arg( 'category_id', $args['category_id'], $api_path );
			
			// Add the week_reference query arg
			if ( ! is_null( $args['week_reference'] ) ) {
				$api_path = add_query_arg( 'week_reference', $args['week_reference'], $api_path );
			}
			
			try {
				
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				
				// GET the data
				$data = $this->get( $data_args );
				
				return $data;
				
			} catch( Exception $e ) {
				
				return false;
				
			}
			
		}
		
		/**
		 * Get the projections
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function projections( $args = array() ) {
			
			$api_path = '/projections.json';
			
			// Set the defaults
			$defaults = array(
				'page'			=> 1,
				'per_page'		=> 30,
				'campus_id'		=> NULL,
				'category_id'	=> NULL,
				'start_week'	=> NULL,
				'end_week'		=> NULL,
				'cache'			=> 60 * MINUTE_IN_SECONDS,
			);
			 
			$args = wp_parse_args( $args, $defaults );
			
			// Add the page query arg
			$api_path = add_query_arg( 'page', $args['page'], $api_path );
			
			// Add the per_page query arg
			$api_path = add_query_arg( 'per_page', $args['per_page'], $api_path );
			
			// Add the campus_id query arg
			if ( ! is_null( $args['campus_id'] ) ) {
				$api_path = add_query_arg( 'campus_id', $args['campus_id'], $api_path );
			}
			
			// Add the category_id query arg
			if ( ! is_null( $args['category_id'] ) ) {
				$api_path = add_query_arg( 'category_id', $args['category_id'], $api_path );
			}
			
			// Add the start_week query arg
			if ( ! is_null( $args['start_week'] ) ) {
				$api_path = add_query_arg( 'start_week', $args['start_week'], $api_path );
			}
			
			// Add the end_week query arg
			if ( ! is_null( $args['end_week'] ) ) {
				$api_path = add_query_arg( 'end_week', $args['end_week'], $api_path );
			}
			 
			try {
				 
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				 
				// GET the data
				$data = $this->get( $data_args );
				 
				return $data;
				 
			} catch ( Exception $e ) {
				 
				return false;
				 
			}
			
		}
		
		/**
		 * Get a specific projection
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function projection( $args = array() ) {
			
			$api_path = '/projection/%d.json';
			
			// Set the defaults
			$defaults = array(
				'id' 	=> NULL,
				'cache'	=> 60 * MINUTE_IN_SECONDS,
			);
			
			$args = wp_parse_args( $args, $defaults );
			
			// Return FALSE if id is NULL
			if ( is_null( $args['id'] ) ) {
				return false;
			}
			
			// Insert the event id into the string
			$api_path = sprintf( $api_path, $args['id'] );
			
			try {
				
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				
				// GET the data
				$data = $this->get( $data_args );
				
				return $data;
				
			} catch( Exception $e ) {
				
				return false;
				
			}
			
		}
		
		/**
		 * Get the records
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function records( $args = array() ) {
			
			$api_path = '/records.json';
			
			// Set the defaults
			$defaults = array(
				'page'				=> 1,
				'per_page'			=> 30,
				'campus_id'			=> NULL,
				'category_id'		=> NULL,
				'event_id'			=> NULL,
				'start_week'		=> NULL,
				'end_week'			=> NULL,
				'start_time'		=> NULL,
				'end_time'			=> NULL,
				'week_reference'	=> NULL,
				'cache'				=> 60 * MINUTE_IN_SECONDS,
			);
			 
			$args = wp_parse_args( $args, $defaults );
			
			// Add the page query arg
			$api_path = add_query_arg( 'page', $args['page'], $api_path );
			
			// Add the per_page query arg
			$api_path = add_query_arg( 'per_page', $args['per_page'], $api_path );
			
			// Add the campus_id query arg
			if ( ! is_null( $args['campus_id'] ) ) {
				$api_path = add_query_arg( 'campus_id', $args['campus_id'], $api_path );
			}
			
			// Add the category_id query arg
			if ( ! is_null( $args['category_id'] ) ) {
				$api_path = add_query_arg( 'category_id', $args['category_id'], $api_path );
			}
			
			// Add the event_id query arg
			if ( ! is_null( $args['event_id'] ) ) {
				$api_path = add_query_arg( 'event_id', $args['event_id'], $api_path );
			}
			
			// Add the start_week query arg
			if ( ! is_null( $args['start_week'] ) ) {
				$api_path = add_query_arg( 'start_week', $args['start_week'], $api_path );
			}
			
			// Add the end_week query arg
			if ( ! is_null( $args['end_week'] ) ) {
				$api_path = add_query_arg( 'end_week', $args['end_week'], $api_path );
			}
			
			// Add the start_time query arg
			if ( ! is_null( $args['start_time'] ) ) {
				$api_path = add_query_arg( 'start_time', $args['start_time'], $api_path );
			}
			
			// Add the end_time query arg
			if ( ! is_null( $args['end_time'] ) ) {
				$api_path = add_query_arg( 'end_time', $args['end_time'], $api_path );
			}
			
			// Add the week_reference query arg
			if ( ! is_null( $args['week_reference'] ) ) {
				$api_path = add_query_arg( 'week_reference', $args['week_reference'], $api_path );
			}
			 
			try {
				 
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				 
				// GET the data
				$data = $this->get( $data_args );
				 
				return $data;
				 
			} catch ( Exception $e ) {
				 
				return false;
				 
			}
			
		}
		
		/**
		 * Get a specific record
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function record( $args = array() ) {
			
			$api_path = '/records/%d.json';
			
			// Set the defaults
			$defaults = array(
				'id'	=> NULL,
				'cache'	=> 60 * MINUTE_IN_SECONDS,
			);
			
			$args = wp_parse_args( $args, $defaults );
			
			// Return FALSE if id is NULL
			if ( is_null( $args['id'] ) ) {
				return false;
			}
			
			// Insert the event id into the string
			$api_path = sprintf( $api_path, $args['id'] );
			
			try {
				
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				
				// GET the data
				$data = $this->get( $data_args );
				
				return $data;
				
			} catch( Exception $e ) {
				
				return false;
				
			}
			
		}
		
		/**
		 * Get the regions
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function regions( $args = array() ) {
			
			$api_path = '/regions.json';
			
			// Set the defaults
			$defaults = array(
				'cache'	=> 1 * DAY_IN_SECONDS,
			);
			 
			$args = wp_parse_args( $args, $defaults );
			 
			try {
				 
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				 
				// GET the data
				$data = $this->get( $data_args );
				 
				return $data;
				 
			} catch ( Exception $e ) {
				 
				return false;
				 
			}
			
		}
		
		/**
		 * Get a specific region
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function region( $args = array() ) {
			
			$api_path = '/regions/%d.json';
			
			// Set the defaults
			$defaults = array(
				'id'	=> NULL,
				'cache'	=> 1 * DAY_IN_SECONDS,
			);
			
			$args = wp_parse_args( $args, $defaults );
			
			// Return FALSE if id is NULL
			if ( is_null( $args['id'] ) ) {
				return false;
			}
			
			// Insert the event id into the string
			$api_path = sprintf( $api_path, $args['id'] );
			
			try {
				
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				
				// GET the data
				$data = $this->get( $data_args );
				
				return $data;
				
			} catch( Exception $e ) {
				
				return false;
				
			}
			
		}
		
		/**
		 * Get the service times
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function service_times( $args = array() ) {
			
			$api_path = '/service_times.json';
			
			// Set the defaults
			$defaults = array(
				'page'				=> 1,
				'per_page'			=> 30,
				'event_id'			=> NULL,
				'cache'				=> 60 * MINUTE_IN_SECONDS,
			);
			 
			$args = wp_parse_args( $args, $defaults );
			
			// Add the page query arg
			$api_path = add_query_arg( 'page', $args['page'], $api_path );
			
			// Add the per_page query arg
			$api_path = add_query_arg( 'per_page', $args['per_page'], $api_path );
			
			// Add the event_id query arg
			if ( ! is_null( $args['event_id'] ) ) {
				$api_path = add_query_arg( 'event_id', $args['event_id'], $api_path );
			}
			 
			try {
				 
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				 
				// GET the data
				$data = $this->get( $data_args );
				 
				return $data;
				 
			} catch ( Exception $e ) {
				 
				return false;
				 
			}
			
		}
		
		/**
		 * Get a specific serivice time
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function service_time( $args = array() ) {
			
			$api_path = '/service_times/%d.json';
			
			// Set the defaults
			$defaults = array(
				'id'	=> NULL,
				'cache'	=> 1 * DAY_IN_SECONDS,
			);
			
			$args = wp_parse_args( $args, $defaults );
			
			// Return FALSE if id is NULL
			if ( is_null( $args['id'] ) ) {
				return false;
			}
			
			// Insert the event id into the string
			$api_path = sprintf( $api_path, $args['id'] );
			
			try {
				
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				
				// GET the data
				$data = $this->get( $data_args );
				
				return $data;
				
			} catch( Exception $e ) {
				
				return false;
				
			}
			
		}
		
		/**
		 * Get the users
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function users( $args = array() ) {
			
			$api_path = '/users.json';
			
			// Set the defaults
			$defaults = array(
				'cache'	=> 60 * MINUTE_IN_SECONDS,
			);
			 
			$args = wp_parse_args( $args, $defaults );
			 
			try {
				 
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				 
				// GET the data
				$data = $this->get( $data_args );
				 
				return $data;
				 
			} catch ( Exception $e ) {
				 
				return false;
				 
			}
			
		}
		
		/**
		 * Get a specific user
		 *
		 * @param	array	$args	An array of the arguments.
		 *
		 * @return	string	A JSON string containing the data.
		 */
		
		public function user( $args = array() ) {
			
			$api_path = '/user/%d.json';
			
			// Set the defaults
			$defaults = array(
				'id'	=> NULL,
				'cache'	=> 1 * DAY_IN_SECONDS,
			);
			
			$args = wp_parse_args( $args, $defaults );
			
			// Return FALSE if id is NULL
			if ( is_null( $args['id'] ) ) {
				return false;
			}
			
			// Insert the event id into the string
			$api_path = sprintf( $api_path, $args['id'] );
			
			try {
				
				$data_args = array(
					'url'	=> $this->api_url . $api_path,
					'cache'	=> $args['cache'],
				);
				
				// GET the data
				$data = $this->get( $data_args );
				
				return $data;
				
			} catch( Exception $e ) {
				
				return false;
				
			}
			
		}
		
	}

endif;