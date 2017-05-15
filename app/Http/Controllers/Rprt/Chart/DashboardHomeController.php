<?php

namespace App\Http\Controllers\Rprt\Chart;

use Illuminate\Http\Request;
use App\Http\Controllers\Rprt\Base\BaseReportController;

use App\Helper\DateFromToCalculator;
use App\Helper\RestResponseMessages;

class DashboardHomeController extends BaseReportController
{
    public function index() {

        $result = [];
    	$date_range = DateFromToCalculator::calculateFromTo( 'This year to date' );

        $result[ 'thisYearIncome' ] = $this->getIncome( $date_range[ 'from' ], $date_range[ 'to' ] );
        $result[ 'thisYearExpense' ] = $this->getExpenseAmount( $date_range[ 'from' ], $date_range[ 'to' ], 'TotalExpense' );

    	return RestResponseMessages::reportRetrieveSuccessMessage( 'Dashboard Home', $result, 200 );
    }
}
