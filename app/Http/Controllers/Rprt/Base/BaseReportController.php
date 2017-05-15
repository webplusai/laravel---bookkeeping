<?php

namespace App\Http\Controllers\Rprt\Base;

use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;

use DB;

use App\Models\Sales;

use App\Models\BillItem;
use App\Models\ExpenseItem;
use App\Models\InvoiceItem;
use App\Models\SalesReceiptItem;

use App\Models\BillAccount;
use App\Models\ExpenseAccount;

use App\Models\Account;
use App\Models\AccountCategoryType;

class BaseReportController extends BaseController
{
    public $expenseCategoryId;
    public $costOfSalesServiceCategoryId;
    public $expenseCategoryAccountIds;
    public $totalExpenseSources = [ 'BillAccount', 'ExpenseAccount', 'BillItem', 'ExpenseItem', 'InvoiceItem', 'SalesReceiptItem' ];
    public $costOfSalesSources = [ 'BillAccount', 'ExpenseAccount', 'InvoiceItem', 'SalesReceiptItem' ];
    public $otherExpenseSources = [ 'BillAccount', 'ExpenseAccount', 'BillItem', 'ExpenseItem' ];

    public function __construct() {
    	parent::__construct();

    	$this->middleware( function( $request, $next ) {

    		$this->expenseCategoryId = AccountCategoryType::where( 'name', 'Operating Expense' )->first()->id;
	    	$this->costOfSalesServiceCategoryId = AccountCategoryType::where( 'name', 'Cost of Sales/Services' )->first()->id;
	    	$this->expenseCategoryAccountIds = Account::whereIn( 'account_category_type_id', [ $this->expenseCategoryId, $this->costOfSalesServiceCategoryId ] )->pluck( 'id' );
    		return $next( $request );

    	} );
    }

    public function expenseAccountQuery( $startDate, $endDate, $expenseType ) {
    	$result = ExpenseAccount::join( 'expense', 'expense_account.expense_id', '=', 'expense.id')
	                        ->join( 'expenses', 'expenses.id', '=', 'expense.expenses_id' );

	    if ( $expenseType == 'CostOfSales' )
	    	$result = $result->join( 'account', 'account.id', '=', 'expense_account.account_id' )
                       		->where( 'account.account_category_type_id', $this->costOfSalesServiceCategoryId );
        else if ( $expenseType == 'OtherExpense' )
        	$result = $result->join( 'account', 'expense_account.account_id', '=', 'account.id' )
             				 ->where( 'account.account_category_type_id', $this->expenseCategoryId );
        else if ( $expenseType == 'TotalExpense' )
        	$result = $result->whereIn( 'expense_account.account_id', $this->expenseCategoryAccountIds );

        return $result->whereBetween( 'date', [ $startDate, $endDate ] )
	                ->where( 'expenses.is_trash', '!=', 1 );
    }

    public function billAccountQuery( $startDate, $endDate, $expenseType ) {
    	$result = BillAccount::join( 'bill', 'bill_account.bill_id', '=', 'bill.id' )
	                        ->join( 'expenses', 'expenses.id', '=', 'bill.expenses_id' ) ;

	    if ( $expenseType == 'CostOfSales' )
	    	$result = $result->join( 'account', 'account.id', '=', 'bill_account.account_id' )
                       		->where( 'account.account_category_type_id', $this->costOfSalesServiceCategoryId );
        else if ( $expenseType == 'OtherExpense' )
        	$result = $result->join( 'account', 'account.id', '=', 'bill_account.account_id' )
                            ->where( 'account.account_category_type_id', $this->expenseCategoryId );
        else if ( $expenseType == 'TotalExpense' )
        	$result = $result->whereIn( 'bill_account.account_id', $this->expenseCategoryAccountIds );

        return $result->whereBetween( 'date', [ $startDate, $endDate ] )
	               	->where( 'expenses.is_trash', '!=', 1 );
    }

    public function expenseItemQuery( $startDate, $endDate, $expenseType ) {
    	return ExpenseItem::join( 'expense', 'expense_item.expense_id', '=', 'expense.id')
	                        ->join( 'expenses', 'expenses.id', '=', 'expense.expenses_id' )
	                        ->join( 'product_service', 'expense_item.product_service_id', '=', 'product_service.id' )
	                        ->whereBetween( 'date', [ $startDate, $endDate ] )
	                        ->where( 'product_service.is_inventoriable', 0 )
	                        ->where( 'expenses.is_trash', '!=', 1 );
    }

    public function billItemQuery( $startDate, $endDate, $expenseType ) {
    	return BillItem::join( 'bill', 'bill_item.bill_id', '=', 'bill.id')
	                        ->join( 'expenses', 'expenses.id', '=', 'bill.expenses_id' )
	                        ->join( 'product_service', 'bill_item.product_service_id', '=', 'product_service.id' )
	                        ->whereBetween( 'date', [ $startDate, $endDate ] )
	                        ->where( 'product_service.is_inventoriable', 0 )
	                        ->where( 'expenses.is_trash', '!=', 1 );
    }

    public function invoiceItemQuery( $startDate, $endDate, $expenseType ) {
    	return InvoiceItem::join( 'invoice', 'invoice_item.invoice_id', '=', 'invoice.id' )
                        ->join( 'sales', 'invoice.sales_id', '=', 'sales.id' )
                        ->join( 'product_service', 'invoice_item.product_service_id', '=', 'product_service.id' )
                        ->whereBetween( 'date', [ $startDate, $endDate ] )
                        ->where( 'product_service.is_inventoriable', 1 )
                        ->where( 'sales.is_trash', '!=', 1 );
    }

    public function salesReceiptItemQuery( $startDate, $endDate, $expenseType ) {
    	return SalesReceiptItem::join( 'sales_receipt', 'sales_receipt_item.sales_receipt_id', '=', 'sales_receipt.id' )
                        ->join( 'sales', 'sales_receipt.sales_id', '=', 'sales.id' )
                        ->join( 'product_service', 'sales_receipt_item.product_service_id', '=', 'product_service.id' )
                        ->where( 'product_service.is_inventoriable', 1 )
                        ->whereBetween( 'date', [ $startDate, $endDate ] )
                        ->where( 'sales.is_trash', '!=', 1 );
    }

    public function getExpenseAmountFromExpenseAccount( $startDate, $endDate, $expenseType ) {
    	return $this->expenseAccountQuery( $startDate, $endDate, $expenseType )->sum( 'amount' );
    }

    public function getExpenseAmountFromBillAccount( $startDate, $endDate, $expenseType ) {
    	return $this->billAccountQuery( $startDate, $endDate, $expenseType )->sum( 'amount' );
    }

    public function getExpenseAmountFromExpenseItem( $startDate, $endDate, $expenseType ) {
    	return $this->expenseItemQuery( $startDate, $endDate, $expenseType )->sum( 'amount' );
    }

    public function getExpenseAmountFromBillItem( $startDate, $endDate, $expenseType ) {
    	return $this->billItemQuery( $startDate, $endDate, $expenseType )->sum( 'amount' );
    }

    public function getExpenseAmountFromInvoiceItem( $startDate, $endDate, $expenseType ) {
    	return $this->invoiceItemQuery( $startDate, $endDate, $expenseType )
    				->select( DB::raw( 'sum(invoice_item.qty*product_service.purchase_price) as amount' ) )
                    ->first()->amount;
    }

    public function getExpenseAmountFromSalesReceiptItem( $startDate, $endDate, $expenseType ) {
    	return $this->salesReceiptItemQuery( $startDate, $endDate, $expenseType )
    				->select( DB::raw( 'sum(sales_receipt_item.qty*product_service.purchase_price) as amount' ) )
                    ->first()->amount;
    }

    public function getExpenseListByExpenseAccount( $startDate, $endDate, $expenseType ) {
    	return $this->expenseAccountQuery( $startDate, $endDate, $expenseType )
    				->selectRaw( 'expense_account.account_id as account_id, sum(amount) as expense' )
                    ->groupBy( 'account_id' );
    }

    public function getExpenseListByBillAccount( $startDate, $endDate, $expenseType ) {
    	return $this->billAccountQuery( $startDate, $endDate, $expenseType )
    				->selectRaw( 'bill_account.account_id as account_id, sum(amount) as expense' )
                    ->groupBy( 'account_id' );
    }

    public function getExpenseListByExpenseItem( $startDate, $endDate, $expenseType ) {
    	return $this->expenseItemQuery( $startDate, $endDate, $expenseType )
    				->selectRaw( Account::where( 'name', 'Purchases' )->first()->id . ' as account_id, amount as expense' );
    }

    public function getExpenseListByBillItem( $startDate, $endDate, $expenseType ) {
    	return $this->billItemQuery( $startDate, $endDate, $expenseType )
    				->selectRaw( Account::where( 'name', 'Purchases' )->first()->id . ' as account_id, amount as expense' );
    }

    public function getExpenseListByInvoiceItem( $startDate, $endDate, $expenseType ) {
        $query = $this->invoiceItemQuery( $startDate, $endDate, $expenseType );
        if ( count( $query->get() ) )
            return $query->selectRaw( Account::where( 'name', 'Cost of Sales/Services' )->first()->id . ' as account_id, sum(invoice_item.qty*product_service.purchase_price) as expense' );
        else
            return $query->selectRaw( Account::where( 'name', 'Cost of Sales/Services' )->first()->id . ' as account_id, 0 as expense' );
    }

    public function getExpenseListBySalesReceiptItem( $startDate, $endDate, $expenseType ) {
        $query = $this->salesReceiptItemQuery( $startDate, $endDate, $expenseType );
        if ( count( $query->get() ) )
    	    return $query->selectRaw( Account::where( 'name', 'Cost of Sales/Services' )->first()->id . ' as account_id, sum(sales_receipt_item.qty*product_service.purchase_price) as expense' );
        else
            return $query->selectRaw( Account::where( 'name', 'Cost of Sales/Services' )->first()->id . ' as account_id, 0 as expense' );
    }

    public function getExpenseAmount( $startDate, $endDate, $expenseType ) {
    	$expenseAmount = 0;
    	$expenseSourceName = lcfirst( $expenseType ) . 'Sources';
    	$expenseSources = $this->$expenseSourceName;
    	for ( $i = 0; $i < count( $expenseSources ); $i++ ) {
            $functionName = 'getExpenseAmountFrom' . $expenseSources[ $i ];
            $expenseAmount += $this->$functionName( $startDate, $endDate, $expenseType );
        }
        return $expenseAmount;
    }

    public function getExpenseList( $startDate, $endDate, $expenseType ) {
    	$expenseList = 0;
    	$expenseSourceName = lcfirst( $expenseType ) . 'Sources';
    	$expenseSources = $this->$expenseSourceName;
    	for ( $i = 0; $i < count( $expenseSources ); $i++ ) {
    		$functionName = 'getExpenseListBy' . $expenseSources[ $i ];
    		if ( $i == 0 )
    			$expenseList = $this->$functionName( $startDate, $endDate, $expenseType );
    		else
    			$expenseList = $expenseList->unionAll( $this->$functionName( $startDate, $endDate, $expenseType ) );
    	}
    	$expenseList = $expenseList->whereBetween( 'date', [ $startDate, $endDate ] )
			                        ->orderBy( 'account_id' )
			                        ->get();
		return $expenseList;
    }

    public function getIncome( $startDate, $endDate ) {
        return Sales::where( function( $query) {
                    $query->where( 'transaction_type', $this->transactionTypes[ 'Invoice' ] )->orWhere( 'transaction_type', $this->transactionTypes[ 'Sales Receipt' ] );
                } )
                ->where( 'is_trash', '!=', 1 )
                ->whereBetween( 'date', [ $startDate, $endDate ] )
                ->sum( 'total' );
    }
}
