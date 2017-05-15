<?php

namespace App\Http\Controllers\Rprt\Chart;

use Illuminate\Http\Request;
use App\Http\Controllers\Rprt\Base\BaseReportController;

use App\Helper\RestResponseMessages;
use App\Helper\DateFromToCalculator;

class ExpenseComparisonController extends BaseReportController
{
    public function index( $period, $account_type ) {

    	$date_segments = DateFromToCalculator::calculateFromToSegments( $period );

    	foreach ( $date_segments as $date_segment ) {

            $segment = [];
            $segment[ 'this_year' ] = $this->getExpenseAmount( $date_segment[ 'this_year' ][ 'from' ], $date_segment[ 'this_year' ][ 'to' ], 'TotalExpense' );
            $segment[ 'last_year' ] = $this->getExpenseAmount( $date_segment[ 'last_year' ][ 'from' ], $date_segment[ 'last_year' ][ 'to' ], 'TotalExpense' );

    		if ( $account_type != 'All accounts' ) {
    			$segment[ 'this_year' ] = $segment[ 'this_year' ]->where( 'account_id', $account[ $account_type ]->id );
    			$segment[ 'last_year' ] = $segment[ 'last_year' ]->where( 'account_id', $account[ $account_type ]->id );
    		}

            $segment[ 'display' ] = $date_segment[ 'Display' ];
            $result[] = $segment;
    	}

    	return RestResponseMessages::reportRetrieveSuccessMessage( 'Expense Comparison', $result );
    }
}
