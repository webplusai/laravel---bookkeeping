<?php

namespace App\Http\Controllers\TRXN;
use App\Http\Controllers\Base\BaseController;

use Illuminate\Http\Request;

use App\Models\Sales;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Attachment;
use App\Models\SalesReceipt;
use App\Models\ProductService;
use App\Models\SalesReceiptItem;
use App\Models\MapSalesAttachment;

use App\Helper\RestInputValidators;
use App\Helper\RestResponseMessages;
use App\Helper\StringConversionFunctions;

class SalesReceiptController extends TRXNController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store( Request $request )
    {
        $sales = $GLOBALS[ 'input' ][ 'transaction' ];
        $salesReceipt = $GLOBALS[ 'input' ][ 'salesReceipt' ];
        $salesReceiptItems = array_filter( $GLOBALS[ 'input' ][ 'salesReceiptItems' ] );

        $sales[ 'customer_id' ] = $this->customer[ $sales[ 'customer' ] ]->id;
        $sales = Sales::create( $sales );

        $salesReceipt[ 'sales_id' ] = $sales->id;
        $salesReceipt = SalesReceipt::create( $salesReceipt );

        $totalCostOfSalesAmount = 0;
        foreach ( $salesReceiptItems as $salesReceiptItem ) {
            if ( isset( $salesReceiptItem[ 'product_service' ] ) || $salesReceiptItem[ 'item_type' ] == 2 ) {
                $productService = $this->product_service[ $salesReceiptItem[ 'product_service' ] ];
                $salesReceiptItem[ 'sales_receipt_id' ] = $salesReceipt->id;
                if ( $salesReceiptItem[ 'item_type' ] != 2 )
                    $salesReceiptItem[ 'product_service_id' ] = $productService->id;
                SalesReceiptItem::create( $salesReceiptItem );

                if ( $productService->is_inventoriable )
                    $totalCostOfSalesAmount += ProductService::find( $salesReceiptItem[ 'product_service_id' ] )->purchase_price * $salesReceiptItem[ 'qty' ];
            }
        }

        if ( isset( $GLOBALS[ 'input' ][ 'attachments' ]) ) {
            $attachments = $GLOBALS[ 'input' ][ 'attachments' ];
            foreach ( $attachments as $attachment ) {
                $attachment = Attachment::create( $attachment );
                MapSalesAttachment::create( [ 'sales_id' => $sales->id, 'attachment_id' => $attachment->id ] );
            }
        }
        
        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales[ 'total' ] ] );

        $accountId = $this->account[ 'Sales Revenues' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales[ 'total' ] ] );

        $accountId = $this->account[ 'Cost of Sales/Services' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalCostOfSalesAmount ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $totalCostOfSalesAmount ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $sales[ 'invoice_receipt_no' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Added ',
                'target_name' => 'Sales Receipt',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'message' => $salesReceipt[ 'message' ],
                'memo' => $salesReceipt[ 'statement_memo' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage( 'Create Sales Receipt', $sales, 200 );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( $id )
    {
        $sales                  =   Sales::find( $id );
        $salesReceipt           =   SalesReceipt::where( 'sales_id', $id )->first();
        $salesReceiptItems      =   SalesReceiptItem::where( 'sales_receipt_id', $salesReceipt->id )->get();

        return RestResponseMessages::TRXNSuccessMessage('Get Sales Receipt', ['transaction' => $sales, 'salesReceipt' => $salesReceipt, 'salesReceiptItems' => $salesReceiptItems], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update( Request $request, $id )
    {
        $sales              = $GLOBALS[ 'input' ][ 'transaction' ];
        $salesReceipt       = $GLOBALS[ 'input' ][ 'salesReceipt' ];
        $salesReceiptItems  = array_filter( $GLOBALS[ 'input' ][ 'salesReceiptItems' ] );

        $sales[ 'customer_id' ] = $this->customer[ $sales[ 'customer' ] ]->id;
        $orgSales = Sales::find( $sales[ 'id' ] );
        Sales::find( $sales[ 'id' ] )->update( $sales );

        $salesReceipt[ 'sales_id' ] = $sales[ 'id' ];
        SalesReceipt::find( $salesReceipt[ 'id' ] )->update( $salesReceipt );

        $totalCostOfSalesAmount = 0;
        $totalOrgCostOfSalesAmount = 0;
        foreach ( $salesReceiptItems as $salesReceiptItem ) {
            if ( isset( $salesReceiptItem[ 'product_service' ] ) || $salesReceiptItem[ 'item_type' ] == 2 ) {
                $productService = $this->product_service[ $salesReceiptItem[ 'product_service' ] ];
                $salesReceiptItem[ 'sales_receipt_id' ] = $salesReceipt[ 'id' ];
                if ( $salesReceiptItem[ 'item_type' ] != 2 )
                    $salesReceiptItem[ 'product_service_id' ] = $productService->id;

                if ( $productService->is_inventoriable )
                    $totalCostOfSalesAmount += ProductService::find( $salesReceiptItem[ 'product_service_id' ] )->purchase_price * $salesReceiptItem[ 'qty' ];

                if ( isset( $salesReceiptItem[ 'id' ] ) ) {
                    if ( $productService->is_inventoriable )
                        $totalOrgCostOfSalesAmount += ProductService::find( $salesReceiptItem[ 'product_service_id' ] )->purchase_price * SalesReceiptItem::find( $salesReceiptItem[ 'id' ] )->qty;
                    SalesReceiptItem::find( $salesReceiptItem[ 'id' ] )->update( $salesReceiptItem );
                }
                else
                    SalesReceiptItem::create( $salesReceiptItem );
            }
        }

        if ( isset( $GLOBALS[ 'input' ][ 'attachments' ] ) ) {
            $attachments = $GLOBALS[ 'input' ][ 'attachments' ];
            foreach ( $attachments as $attachment ) {
                $attachment = Attachment::create( $attachment );
                MapSalesAttachment::create( [ 'sales_id' => $sales->id, 'attachment_id' => $attachment->id ] );
            }
        }
        
        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales[ 'total' ] - $orgSales[ 'total' ] ] );

        $accountId = $this->account[ 'Sales Revenues' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales[ 'total' ] - $orgSales[ 'total' ] ] );

        $accountId = $this->account[ 'Cost of Sales/Services' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalCostOfSalesAmount - $totalOrgCostOfSalesAmount ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $totalCostOfSalesAmount + $totalOrgCostOfSalesAmount ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $sales[ 'invoice_receipt_no' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Edited ',
                'target_name' => 'Sales Receipt',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'message' => $salesReceipt[ 'message' ],
                'memo' => $salesReceipt[ 'statement_memo' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage('Update Invoice', $sales, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( Request $request, $id )
    {
        $sales = Sales::find( $id );
        $sales->update( [ 'is_trash' => 1 ] );

        $salesReceipt = SalesReceipt::where( 'sales_id', $sales->id )->first();
        $salesReceiptItems = SalesReceiptItem::where( 'sales_receipt_id', $salesReceipt->id )->get();

        $totalCostOfSalesAmount = 0;
        foreach ( $salesReceiptItems as $salesReceiptItem ) {
            $productService = ProductService::find( $salesReceiptItem[ 'product_service_id' ] );
            if ( $productService->is_inventoriable )
                $totalCostOfSalesAmount += $productService->purchase_price * $salesReceiptItem[ 'qty' ];
        }
        
        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales->total ] );

        $accountId = $this->account[ 'Sales Revenues' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales->total ] );

        $accountId = $this->account[ 'Cost of Sales/Services' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $totalCostOfSalesAmount ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalCostOfSalesAmount ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $sales[ 'invoice_receipt_no' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Deleted ',
                'target_name' => 'Sales Receipt',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'message' => $salesReceipt[ 'message' ],
                'memo' => $salesReceipt[ 'statement_memo' ]
            ] 
        );
    }

    public function recoverDelete( Request $request, $id ) {

        $sales = Sales::find( $id );
        $sales->update( [ 'is_trash' => 0 ] );

        $salesReceipt = SalesReceipt::where( 'sales_id', $sales->id )->first();
        $salesReceiptItems = SalesReceiptItem::where( 'sales_receipt_id', $salesReceipt->id )->get();

        $totalCostOfSalesAmount = 0;
        foreach ( $salesReceiptItems as $salesReceiptItem ) {
            $productService = ProductService::find( $salesReceiptItem[ 'product_service_id' ] );
            if ( $productService->is_inventoriable )
                $totalCostOfSalesAmount += $productService->purchase_price * $salesReceiptItem[ 'qty' ];
        }
        
        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales->total ] );

        $accountId = $this->account[ 'Sales Revenues' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales->total ] );

        $accountId = $this->account[ 'Cost of Sales/Services' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalCostOfSalesAmount ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $totalCostOfSalesAmount ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $sales[ 'invoice_receipt_no' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Recovered ',
                'target_name' => 'Sales Receipt',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'message' => $salesReceipt[ 'message' ],
                'memo' => $salesReceipt[ 'statement_memo' ]
            ] 
        );
    }
}
