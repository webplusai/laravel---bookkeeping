<?php

namespace App\Http\Controllers\Rprt\Table;

use Illuminate\Http\Request;
use App\Http\Controllers\Rprt\Base\BaseReportController;

use App\Models\Sales;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Invoice;


use App\Helper\RestResponseMessages;
use App\Helper\DateFromToCalculator;

class BalanceSheetController extends BaseReportController
{
    public function getBalanceSheet( $startDate, $endDate ) {

        $items = [ 'currentAssets', 'nonCurrentAssets', 'currentLiabilities', 'nonCurrentLiabilities', 'ownersEquity' ];
        $content = [];

        for ( $i = 1; $i <= 5; $i++ ) {
            $content[ $items[ $i - 1 ] ] = Account::where( 'account_category_type_id', $i )->select( 'name as accountName', 'balance as balance' )->groupBy( 'accountName' )->get();
            $content[ 'total' . ucfirst( $items[ $i - 1 ] ) ] = Account::where( 'account_category_type_id', $i )->sum( 'balance' );
        }

        $income = $this->getIncome( $startDate, $endDate );
        $expense = $this->getExpenseAmount( $startDate, $endDate, 'TotalExpense' );

        $content[ 'netIncome' ] = $income - $expense;
        $content[ 'retainedEarnings' ] = 0;
        $content[ 'dateFrom' ] = $startDate;
        $content[ 'dateTo' ] = $endDate;

        return $content;
    }

    public function period( $period ) {
    	$dateRange = DateFromToCalculator::calculateFromTo( $period );
        $content = $this->getBalanceSheet( $dateRange[ 'from' ], $dateRange[ 'to' ] );
    	return RestResponseMessages::reportRetrieveSuccessMessage( 'Balance Sheet', $content );
    }

    public function range( $startDate, $endDate ) {
        $content = $this->getBalanceSheet( $startDate, $endDate );
        return RestResponseMessages::reportRetrieveSuccessMessage( 'Balance Sheet', $content );
    }
}
