<?php

namespace App\Http\Controllers\Rprt\Chart;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Rprt\Base\BaseReportController;

use App\Helper\RestResponseMessages;
use App\Helper\DateFromToCalculator;

class ExpenseController extends BaseReportController
{
    public function byAccount( $period ) {

    	// Period : 30-days, this month, this quarter, this year, last month, last quarter, last year.

    	$date_range = DateFromToCalculator::calculateFromTo( $period );
        $result = $this->getExpenseList( $date_range[ 'from' ], $date_range[ 'to' ], 'TotalExpense' );

    	return RestResponseMessages::reportRetrieveSuccessMessage( 'Expense Report', $result );
    }

}
