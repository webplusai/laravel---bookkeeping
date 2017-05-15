<?php

namespace App\Helper;

class DateFromToCalculator {

	public static function calculateFromTo( $period ) {

		$months = [ 'January', 'Feburary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];

		$from 	= date( 'Y-m-d' );
		$to 	= date( 'Y-m-d', strtotime( '+1 day' ) );

		if ( $period == 'Last 30 days' ) {

			$from 	= 	date( 'Y-m-d', strtotime( '-30 days' ) );
			$to 	= 	date( 'Y-m-d', strtotime( '+1 day' ) );

		} else if ( $period == 'This month' ) {

			$from 	= 	date( 'Y-m-01' );
			$to 	= 	date( 'Y-m-t', strtotime( date( 'Y-m-d' ) ) );

		} else if ( $period == 'This quarter' ) {

			$from 	= 	date( 'Y-m-d', strtotime( 'first day of '. $months[ ( int )( (date( 'm' ) - 1) / 3 ) * 3 ] ) );
			$to 	= 	date( 'Y-m-d', strtotime( 'last day of '. $months[ ( int )( (date( 'm' ) - 1) / 3 ) * 3 + 2 ] ) );

		} else if ( $period == 'This year' ) {

			$from 	= 	date( 'Y-m-d', strtotime( 'Jan 1' ) );
			$to 	= 	date( 'Y-m-d', strtotime( 'Dec 31' ) );

		} else if ( $period == 'Last month' ) {

			$from 	= 	date( 'Y-m-01', strtotime( '-1 month' ) );
			$to 	= 	date( 'Y-m-t', strtotime( '-1 month' ) );

		} else if ( $period == 'Last quarter' ) {

			$from 	= 	date( 'Y-m-d', strtotime( date( 'Y-m-d', strtotime( 'first day of ' . $months[ ( int )( (date( 'm' ) - 1) / 3 ) * 3 ] ) ) . ' -3 months' ) );
			$to 	= 	date( 'Y-m-d', strtotime( date( 'Y-m-d', strtotime( 'last day of ' . $months[ ( int )( (date( 'm' ) - 1) / 3 ) * 3 + 2 ] ) ) . ' -3 months' ) );

		} else if ( $period == 'Last year' ) {

			$from 	= 	date( 'Y-m-d', strtotime( date( 'Y-m-d', strtotime( 'Jan 1' ) ) . '-1 year' ) );
			$to 	= 	date( 'Y-m-d', strtotime( date( 'Y-m-d', strtotime( 'Dec 31' ) ) . '-1 year' ) );

		} else if ( $period == 'Last 4 months' ) {

			$from 	=	date( 'Y-m-d', strtotime( '-4 months' ) );
			$to 	=	date( 'Y-m-d', strtotime( '+1 day' ) );

		} else if ( $period == 'This year to date' ) {

			$from 	=	date( 'Y-01-01' );
			$to 	=	date( 'Y-m-d', strtotime( '+1 day' ) );

		}

		return [ 'from' => $from, 'to' => $to ];
	}

	public static function calculateFromToSegments( $period ) {

		$date_range = DateFromToCalculator::calculateFromTo( $period );
		$date_segments = [];

		$date_from = strtotime( $date_range[ 'from' ] );
		$date_to = strtotime( $date_range[ 'to' ] );

		if ( $period == 'Last 30 days' || $period == 'This month' || $period == 'Last month' ) {

			if ( date( 'D', $date_from ) != 'Sun' ) {
				array_push(
					$date_segments, 
					[
						'from' 		=> 		date( 'Y-m-d', $date_from ), 
						'to' 		=> 		date( 'Y-m-d', strtotime( 'Saturday', $date_from ) ), 
						'display' 	=> 		date( 'M d', $date_from )
					]
				);
			}

			for ( $i = strtotime( 'Sunday', $date_from ); $i <= $date_to; $i = strtotime( '+1 week', $i ) ) {
				array_push(
					$date_segments, 
					[
						'from' 		=> 		date( 'Y-m-d', $i ), 
						'to' 		=> 		date( 'Y-m-d', min( strtotime( '+6 days', $i ), $date_to ) ), 
						'display' 	=> 		date( 'M d', $i )
					]
				);
			}

		} else if ( $period == 'This quarter' || $period == 'Last quarter' ) {

			for ( $i = $date_from; $i <= $date_to; $i = strtotime( '+1 month',  $i ) ) {
				array_push(
					$date_segments, 
					[
						'from' 		=> 		date( 'Y-m-d', $i ), 
						'to' 		=> 		date( 'Y-m-d', strtotime( '+1 month -1 day', $i ) ), 
						'display' 	=> 		date( 'M Y', $i )
					]
				);
			}

		} else if ( $period == 'This year' || $period == 'Last year' ) {

			for ( $i = $date_from; $i <= $date_to; $i = strtotime( '+3 months',  $i ) ) {
				array_push(
					$date_segments, 
					[
						'from' 		=> 		date( 'Y-m-d', $i ), 
						'to' 		=> 		date( 'Y-m-d', strtotime( '+3 months -1 day', $i ) ), 
						'display' 	=> 		date( 'M Y', $i )
					]
				);
			}

		} else if ( $period == 'Last 4 months' ) {

			array_push( $date_segments, 
				[
					'from' 		=> 		$date_range[ 'from' ],
					'to' 		=> 		date( 'Y-m-01', strtotime( '+1 month -1 day', $date_from ) ),
					'display' 	=> 		date( 'd M', $date_from )
				]
			);

			for ( $i = strtotime( date( 'Y-m-01', strtotime( '+1 month', $date_from ) ) ); $i <= $date_to; $i = strtotime( '+1 month', $i ) ) {
				array_push($date_segments, 
					[
						'from' 		=> 		date( 'Y-m-d', $i ),
						'to' 		=> 		date( 'Y-m-d', min( strtotime( '+1 month -1 day', $i ), $date_to ) ),
						'display' 	=> 		date( 'M Y', $i )
					]
				);
			}

		} else if ( $period == 'Monthly' ) {

			$this_year = strtotime( date( 'Y-01-01' ) );
			$last_year = strtotime( date( 'Y-01-01', strtotime( '-1 year' ) ) );
			for ( $i = 0; $i < 12; $i++ ) {
				array_push( $date_segments,
					[
						'this_year' => [
							'from' 		=> 		date( 'Y-m-01', $this_year ),
							'to' 		=> 		date( 'Y-m-t', $this_year )
						],
						'last_year' => [
							'from' 		=> 		date( 'Y-m-01', $last_year ),
							'to' 		=> 		date( 'Y-m-t', $last_year )
						],
						'Display'		=>		date( 'M', $this_year )
					]
				);

				$this_year = strtotime( '+1 month', $this_year );
				$last_year = strtotime( '+1 month', $last_year );
			}

		} else if ( $period == 'Quarterly' ) {

			$this_year = strtotime( date( 'Y-01-01' ) );
			$last_year = strtotime( date( 'Y-01-01', strtotime( '-1 year' ) ) );

			$quarters = [ 'Quarter1', 'Quarter2', 'Quarter3', 'Quarter4' ];
			for ( $i = 0; $i < 4; $i++ ) {
				array_push( $date_segments, 
					[
						'this_year' => [
							'from' 		=> 		date( 'Y-m-01', $this_year ),
							'to' 		=> 		date( 'Y-m-t', strtotime( '+2 months', $this_year ) )
						],
						'last_year' => [
							'from' 		=> 		date( 'Y-m-01', $last_year ),
							'to' 		=> 		date( 'Y-m-t', strtotime( '+2 months', $last_year ) )
						],
						'Display' => $quarters[ $i ]
					]
				);

				$this_year = strtotime( '+3 months', $this_year );
				$last_year = strtotime( '+3 months', $last_year );
			}
			
		}

		return $date_segments;
	}
}