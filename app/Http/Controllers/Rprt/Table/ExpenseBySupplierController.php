<?php

namespace App\Http\Controllers\Rprt\Table;

use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;

use DB;
use App\Models\Expenses;
use App\Helper\RestResponseMessages;
use App\Helper\DateFromToCalculator;

class ExpenseBySupplierController extends BaseController
{
	public function getExpenseBySupplier( $startDate, $endDate ) {

        return [ 
                    'report'    => Expenses::where( function( $query ) {
                                        $query->where( 'transaction_type', $this->transactionTypes[ 'Expense' ] )->orWhere( 'transaction_type', $this->transactionTypes[ 'Bill' ] );
                                    } )
            						->whereBetween('date', [ $startDate, $endDate ] )
                                    ->where( 'is_trash', '!=', 1 )
            						->select( DB::raw( 'payee_id as payee_id' ), DB::raw( 'payee_type as payee_type' ), DB::raw( 'sum(total) as total' ) )
            						->groupBy( DB::raw( 'payee_id' ), DB::raw( 'payee_type' ) )
            						->get(),
                    'dateFrom'  =>  $startDate,
                    'dateTo'    =>  $endDate
                ];
	}

    public function period( $period ) {
    	$dateRange = DateFromToCalculator::calculateFromTo( $period );
    	$content = $this->getExpenseBySupplier( $dateRange[ 'from' ], $dateRange[ 'to' ] );
        return RestResponseMessages::reportRetrieveSuccessMessage( 'Expense by Supplier', $content );
    }

    public function range( $startDate, $endDate ) {
    	$content = $this->getExpenseBySupplier( $startDate, $endDate );
    	return RestResponseMessages::reportRetrieveSuccessMessage( 'Expense by Supplier', $content );
    }
}
