<?php
/**
 * Determine the first and last days of the specified year
 *
 * @param	string	$datestr	String representing the year. eg. 'this year'
 *
 * @return 	array	An array containing the 'start' and 'end' dates
 */
 
function cm_dash_widgets_range_year( $datestr ) {
	
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
 * Determine the first and last days of the specified month
 *
 * @param	string	$datestr	String representing the month. eg. 'this month'
 *
 * @return	array	An array containing the 'start' and 'end' dates
 */

function cm_dash_widgets_range_month( $datestr ) {
	
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
 * Determine the first and last days of the specified week
 *
 * @param	string	$datestr	String represenging the week. eg. 'this week'
 *
 * @return	array	An array containing the 'start' and 'end' dates
 */

function cm_dash_widgets_range_week( $datestr ) {
	
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
 * Determine the difference between two numbers
 *
 * @param	int	$cur	Integer representing the current number
 * @param	int	$prev	Integer representing the previous number
 *
 * @return	array	An array containing 'diff' and 'trend'
 */

function cm_dash_widgets_percentage_change( $cur, $prev ) {
	
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