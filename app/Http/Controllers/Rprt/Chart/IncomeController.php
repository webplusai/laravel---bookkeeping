<?php

namespace App\Http\Controllers\Rprt\Chart;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;
use App\Models\Sales;
use App\Models\Payment;
use App\Models\InvoiceItem;

use App\Helper\RestResponseMessages;
use App\Helper\DateFromToCalculator;

class IncomeController extends BaseController
{
    public function barChart() {
        $open_invoices = Sales::where( 'transaction_type', $this->transactionTypes[ 'Invoice' ] )
                                ->where( function( $query ) {
                                    $query->where( 'status', $this->statuses[ 'Invoice' ][ 'Unpaid' ] )->orWhere( 'status', $this->statuses[ 'Invoice' ][ 'Partial' ] );
                                } )
                                ->where( 'is_trash', '!=', 1 )
                                ->whereBetween( 'date', [ date( 'Y-m-d', strtotime( '-365 days' ) ), date( 'Y-m-d', strtotime( '+1 day' ) ) ] )
                                ->sum( 'balance' );

        $over_due = Sales::where( 'transaction_type', $this->transactionTypes[ 'Invoice' ] )
                                ->where( function( $query ) {
                                    $query->where( 'status', $this->statuses[ 'Invoice' ][ 'Unpaid' ] )->orWhere( 'status', $this->statuses[ 'Invoice' ][ 'Partial' ] );
                                } )
                                ->where( 'is_trash', '!=', 1 )
                                ->whereBetween( 'date', [ date( 'Y-m-d', strtotime( '-365 days' ) ), date( 'Y-m-d', strtotime( '+1 day' ) ) ] )
                                ->where( 'due_date', '<', date( 'Y-m-d' ) )
                                ->sum( 'balance' );

        $paid_last_30_days = Sales::where( 'transaction_type', $this->transactionTypes[ 'Payment' ] )
                                ->where( 'is_trash', '!=', 1 )
                                ->whereBetween( 'date', [ date( 'Y-m-d', strtotime( '-30 days' ) ), date( 'Y-m-d', strtotime( '+1 day' ) ) ] )
                                ->sum( 'total' )
                            + Sales::where( 'transaction_type', $this->transactionTypes[ 'Sales Receipt' ] )
                                ->whereBetween( 'date', [ date( 'Y-m-d', strtotime( '-30 days' ) ), date( 'Y-m-d', strtotime( '+1 day' ) ) ] )
                                ->where( 'is_trash', '!=', 1 )
                                ->sum( 'total' );

    	$content = array( 'open_invoices' => $open_invoices, 'over_due' => $over_due, 'paid_last_30_days' => $paid_last_30_days );
    	return RestResponseMessages::reportRetrieveSuccessMessage( 'Income Report(Bar)', $content );
    }

    public function circleChart( $period ) {
        $date_range = DateFromToCalculator::calculateFromTo( $period );

        $content = DB::table( 'invoice_item' )
                            ->join( 'invoice', 'invoice.id', '=', 'invoice_item.invoice_id' )
                            ->join( 'sales', 'sales.id', '=', 'invoice.sales_id' )
                            ->select( DB::raw( 'invoice_item.product_service_id as product_service_id' ), DB::raw( 'sum(amount) as income' ) )
                            ->groupBy( 'product_service_id' )
                            ->union(
                                DB::table( 'sales_receipt_item' )
                                    ->join( 'sales_receipt', 'sales_receipt.id', '=', 'sales_receipt_item.sales_receipt_id' )
                                    ->join( 'sales', 'sales.id', '=', 'sales_receipt.sales_id' )
                                    ->select( DB::raw( 'sales_receipt_item.product_service_id as product_service_id' ), DB::raw( 'sum(amount) as income' ) )
                                    ->groupBy( 'product_service_id' )
                            )
                            ->whereBetween( 'date', [ $date_range[ 'from' ], $date_range[ 'to' ] ] )
                            ->where( 'sales.is_trash', '!=', 1 )
                            ->orderBy( 'product_service_id' )
                            ->get();

        return RestResponseMessages::reportRetrieveSuccessMessage( 'Income Report(Chart)', $content );
    }
}
