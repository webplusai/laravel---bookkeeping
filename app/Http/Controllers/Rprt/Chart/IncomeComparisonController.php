<?php

namespace App\Http\Controllers\Rprt\Chart;

use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;

use App\Models\Sales;
use App\Helper\DateFromToCalculator;
use App\Helper\RestResponseMessages;

class IncomeComparisonController extends BaseController
{
    public function index( $period, $account ) {

    	$date_segments = DateFromToCalculator::calculateFromToSegments( $period );
    	$invoice_status = [ 'Sales' => 2, 'Unapplied Cash Payment Income' => 1 ];

    	foreach ( $date_segments as $date_segment ) {
    		$this_year = Sales::whereBetween( 'date', [ $date_segment[ 'this_year' ][ 'from' ], $date_segment[ 'this_year' ][ 'to' ] ] )->where( 'is_trash', '!=', 1 );
    		$last_year = Sales::whereBetween( 'date', [ $date_segment[ 'last_year' ][ 'from' ], $date_segment[ 'last_year' ][ 'to' ] ] )->where( 'is_trash', '!=', 1 );

    		if ( $account != 'All accounts' ) {
    			$this_year = $this_year->where( 'status', $invoice_status[ $account ] );
    			$last_year = $last_year->where( 'status', $invoice_status[ $account ] );
    		}

    		$this_year = $this_year->sum( 'total' );
    		$last_year = $last_year->sum( 'total' );

            $result[] = [ 'this_year' => $this_year, 'last_year' => $last_year, 'display' => $date_segment[ 'Display' ] ];
    	}

    	return RestResponseMessages::reportRetrieveSuccessMessage( 'Income Comparison', $result );
    }
}
