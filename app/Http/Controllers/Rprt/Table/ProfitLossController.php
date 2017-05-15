<?php

namespace App\Http\Controllers\Rprt\Table;

use Illuminate\Http\Request;
use App\Http\Controllers\Rprt\Base\BaseReportController;

use DB;
use App\Models\Sales;
use App\Models\Account;
use App\Models\BillAccount;
use App\Models\ExpenseAccount;
use App\Models\BillItem;
use App\Models\ExpenseItem;
use App\Models\InvoiceItem;
use App\Models\SalesReceiptItem;
use App\Models\AccountCategoryType;
use App\Helper\DateFromToCalculator;
use App\Helper\RestResponseMessages;

class ProfitLossController extends BaseReportController
{
	public function getProfitLoss( $startDate, $endDate ) {

		$result = [];

		$result[ 'salesOfProductServices' ] = 	$this->getIncome( $startDate, $endDate );
		$result[ 'totalIncome' ] 			= 	$result[ 'salesOfProductServices' ];
        $result[ 'costOfSalesServices' ] 	= 	$this->getExpenseList( $startDate, $endDate, 'CostOfSales' );
        $result[ 'expenses' ] 				= 	$this->getExpenseList( $startDate, $endDate, 'OtherExpense' );
        $result[ 'dateFrom' ] 				= 	$startDate;
        $result[ 'dateTo' ] 				= 	$endDate;

		return $result;
	}

    public function period( $period ) {
    	$dateRange = DateFromToCalculator::calculateFromTo( $period );
    	$result = $this->getProfitLoss( $dateRange[ 'from' ], $dateRange[ 'to' ] );
		return RestResponseMessages::reportRetrieveSuccessMessage( 'Profit and Loss', $result );
    }

    public function range( $startDate, $endDate ) {
    	$result = $this->getProfitLoss( $startDate, $endDate );
    	return RestResponseMessages::reportRetrieveSuccessMessage( 'Profit and Loss', $result );
    }
}
