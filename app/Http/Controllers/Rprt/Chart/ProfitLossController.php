<?php

namespace App\Http\Controllers\Rprt\Chart;

use Illuminate\Http\Request;
use App\Http\Controllers\Rprt\Base\BaseReportController;

use App\Models\Sales;

use App\Helper\RestResponseMessages;
use App\Helper\DateFromToCalculator;

class ProfitLossController extends BaseReportController
{
    public function index( $period ) {

        $result             =   [];
    	$date_segments      =   DateFromToCalculator::calculateFromToSegments( $period );

    	foreach ( $date_segments as $date_segment ) {

            $segment[ 'profit' ] = $this->getIncome( $date_segment[ 'from' ], $date_segment[ 'to' ] );
            $segment[ 'loss' ] = $this->getExpenseAmount( $date_segment[ 'from' ], $date_segment[ 'to' ], 'TotalExpense' );
            $segment[ 'display' ] = $date_segment[ 'display' ];

            $result[] = $segment;
    	}

    	return RestResponseMessages::reportRetrieveSuccessMessage( 'Profit and Loss Report', $result );
    }
}
