<?php

namespace App\Http\Controllers\TRXN;

use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;

use App\Models\Sales;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Attachment;
use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\InvoiceItem;
use App\Models\ProductService;
use App\Models\MapInvoicePayment;
use App\Models\MapSalesAttachment;
use App\Models\MapCreditNotePayment;

use App\Helper\RestInputValidators;
use App\Helper\RestResponseMessages;
use App\Helper\StringConversionFunctions;

class InvoiceController extends TRXNController
{

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
        $sales          =   $GLOBALS[ 'input' ][ 'transaction' ];
        $invoice        =   $GLOBALS[ 'input' ][ 'invoice' ];
        $invoiceItems   =   array_filter( $GLOBALS[ 'input' ][ 'invoiceItems' ] );

        $sales[ 'customer_id' ] = $this->customer[ $sales[ 'customer' ] ]->id;
        $unClosedPayments = Sales::where( 'customer_id', $sales[ 'customer_id' ] )
                                    ->where( 'transaction_type', $this->transactionTypes[ 'Payment' ] )
                                    ->where( 'status', '!=', $this->statuses[ 'Payment' ][ 'Closed' ] )
                                    ->where( 'is_trash', '!=', 1 );
        $unClosedCreditNotes = Sales::where( 'customer_id', $sales[ 'customer_id' ] )
                                    ->where( 'transaction_type', $this->transactionTypes[ 'Credit Note' ] )
                                    ->where( 'status', '!=', $this->statuses[ 'Credit Note' ][ 'Closed' ] )
                                    ->where( 'is_trash', '!=', 1 );

        $unClosedPaymentTotal = $unClosedPayments->sum( 'balance' );
        $unClosedCreditNoteTotal = $unClosedCreditNotes->sum( 'balance' );
        if ( $unClosedPaymentTotal + $unClosedCreditNoteTotal > 0 ) {
            $sales[ 'balance' ] = max( $sales[ 'total' ] - $unClosedPaymentTotal - $unClosedCreditNoteTotal, 0 );
            $sales[ 'status' ] = $sales[ 'balance' ] > 0 ? $this->statuses[ 'Invoice' ][ 'Partial' ] : $this->statuses[ 'Invoice' ][ 'Paid' ];
        }
        $sales = Sales::create( $sales );

        $invoice[ 'sales_id' ] = $sales->id;
        $invoice = Invoice::create( $invoice );

        $unClosedPayments = $unClosedPayments->get();
        $mapInvoicePayment = [ 'invoice_id' => $invoice->id ];
        $total = $sales[ 'total' ];
        foreach ( $unClosedPayments as $payment ) {
            if ( $total > 0 ) {
                $status = $payment->balance > $total ? 'Partial' : 'Closed';
                Sales::find( $payment->id )->update( [ 'status' => $this->statuses[ 'Payment' ][ $status ], 'balance' => max( $payment->balance - $total, 0 ) ] );

                $this->createAuditLog(
                    [
                        'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                        'record_id' => $payment->id,
                        'trxn_id' => $payment->id,
                        'date_changed' => date( 'Y-m-d H:i:s' ), 
                        'user_email' => \Auth::user()->email, 
                        'event_text' => 'Edited ',
                        'target_name' => 'Payment',
                        'person_id' => Sales::find( $payment->id )->customer_id,
                        'person_type' => $this->personTypes[ 'Customer' ],
                        'date' => $sales[ 'date' ],
                        'amount' => Sales::find( $payment->id )->total,
                        'open_balance' => Sales::find( $payment->id )->balance
                    ] 
                );

                $mapInvoicePayment[ 'payment' ] = min( $payment->balance, $total );
                $mapInvoicePayment[ 'payment_id' ] = Payment::where( 'sales_id', $payment->id )->first()->id;
                MapInvoicePayment::create( $mapInvoicePayment );

                $total -= $payment->balance;
            }
        }

        if ( $total > 0 ) {
            $unClosedCreditNotes = $unClosedCreditNotes->get();

            if ( count( $unClosedCreditNotes ) > 0 ) {
                $transaction = [
                    'transaction_type' => $this->transactionTypes[ 'Payment' ],
                    'customer_id' => $sales->customer_id,
                    'total' => 0,
                    'status' => $this->statuses[ 'Payment' ][ 'Closed' ]
                ];
                $payment = [
                    'note' => 'Created by Sejllat to link credits to charges.',
                    'account_id' => $this->account[ 'Cash' ]->id,
                ]; 
                $transaction[ 'date' ] = $transaction[ 'due_date' ] = date( 'Y-m-d' );
                $transaction = Sales::create( $transaction );

                $payment[ 'sales_id' ] = $transaction->id;
                $payment = Payment::create( $payment );

                $this->createAuditLog(
                    [
                        'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                        'record_id' => $transaction[ 'id' ],
                        'trxn_id' => $payment[ 'id' ],
                        'date_changed' => date( 'Y-m-d H:i:s' ), 
                        'user_email' => \Auth::user()->email, 
                        'event_text' => 'Added ',
                        'target_name' => 'Payment',
                        'person_id' => $transaction[ 'customer_id' ],
                        'person_type' => $this->personTypes[ 'Customer' ],
                        'date' => $transaction[ 'date' ],
                        'amount' => $transaction[ 'total' ],
                        'open_balance' => $transaction[ 'balance' ],
                        'message' => $payment[ 'note' ],
                    ] 
                );

                $mapInvoicePayment[ 'invoice_id' ] = $invoice->id;
                $mapInvoicePayment[ 'payment_id' ] = $payment->id;
                $mapInvoicePayment[ 'payment' ] = 0;

                $mapCreditNotePayment = [ 'payment_id' => $payment->id ];

                foreach ( $unClosedCreditNotes as $creditNote ) {
                    if ( $total > 0 ) {
                        $status = $creditNote->balance > $total ? 'Partial' : 'Closed';
                        Sales::find( $creditNote->id )->update( [ 'status' => $this->statuses[ 'Credit Note' ][ $status ], 'balance' => max( $creditNote->balance - $total, 0 ) ] );

                        $this->createAuditLog(
                            [
                                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                                'record_id' => $creditNote->id,
                                'trxn_id' => Sales::find( $creditNote->id )->invoice_receipt_no,
                                'date_changed' => date( 'Y-m-d H:i:s' ), 
                                'user_email' => \Auth::user()->email, 
                                'event_text' => 'Edited ',
                                'target_name' => 'Credit Note',
                                'person_id' => Sales::find( $creditNote->id )->customer_id,
                                'person_type' => $this->personTypes[ 'Customer' ],
                                'date' => $sales[ 'date' ],
                                'amount' => Sales::find( $creditNote->id )->total,
                                'open_balance' => Sales::find( $creditNote->id )->balance,
                                'items' => CreditNoteItem::where( 'credit_note_id', CreditNote::where( 'sales_id', $creditNote->id )->first()->id )->get()->toArray(),
                                'is_indirect' => 1
                            ] 
                        );

                        $mapCreditNotePayment[ 'credit_note_id' ] = CreditNote::where( 'sales_id', $creditNote->id )->first()->id;
                        $mapCreditNotePayment[ 'payment' ] = min( $creditNote->balance, $total );
                        $mapInvoicePayment[ 'payment' ] += $mapCreditNotePayment[ 'payment' ];
                        MapCreditNotePayment::create( $mapCreditNotePayment );

                        $total -= $creditNote->balance;
                    }
                }

                MapInvoicePayment::create( $mapInvoicePayment );
            }
        }


        $totalCostOfSalesAmount = 0;
        foreach ( $invoiceItems as $invoiceItem ) {
            if ( isset( $invoiceItem[ 'product_service' ] ) || $invoiceItem[ 'item_type' ] == 2 ) {

                $productService = $this->product_service[ $invoiceItem[ 'product_service' ] ];
                $invoiceItem[ 'invoice_id' ] = $invoice->id;
                if ( $invoiceItem[ 'item_type' ] != 2 )
                    $invoiceItem[ 'product_service_id' ] = $productService->id;
                InvoiceItem::create( $invoiceItem );

                if ( $productService->is_inventoriable == 1 )
                    $totalCostOfSalesAmount += ProductService::find( $invoiceItem[ 'product_service_id' ] )->purchase_price * $invoiceItem[ 'qty' ];
            }
        }
 
        if ( isset( $GLOBALS[ 'input' ][ 'attachments' ] ) ) {
            $attachments    =   $GLOBALS[ 'input' ][ 'attachments' ];
            foreach ( $attachments as $attachment ) {
                $attachment = Attachment::create( $attachment );
                MapSalesAttachment::create( [ 'sales_id' => $sales->id, 'attachment_id' => $attachment->id ] );
            }
        }

        Customer::find( $sales[ 'customer_id' ] )->update( [ 'balance' => Customer::find( $sales[ 'customer_id' ] )->balance + $sales[ 'total' ] ] );
        
        $accountId = $this->account[ 'Accounts Receivable' ]->id;
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
                'target_name' => 'Invoice',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'open_balance' => $sales[ 'balance' ],
                'message' => $invoice[ 'message' ],
                'memo' => $invoice[ 'statement_memo' ],
                'items' => $invoiceItems
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage( 'Create Invoice', $sales, 200 );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( $id )
    {
        $sales          =   Sales::find( $id );
        $invoice        =   Invoice::where( 'sales_id', $id )->first();
        $invoiceItems   =   InvoiceItem::where( 'invoice_id', $invoice->id )->get();

        return RestResponseMessages::TRXNSuccessMessage( 'Get Invoice', [ 'transaction' => $sales, 'invoice' => $invoice, 'invoiceItems' => $invoiceItems ], 200 );
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
        $sales = $GLOBALS[ 'input' ][ 'transaction' ];
        $invoice = $GLOBALS[ 'input' ][ 'invoice' ];
        $invoiceItems = array_filter( $GLOBALS[ 'input' ][ 'invoiceItems' ] );

        $sales[ 'customer_id' ] = $this->customer[ $sales[ 'customer' ] ]->id;
        $orgPayments = Sales::where( 'transaction_type', $this->transactionTypes[ 'Payment' ] )
                             ->where( 'customer_id', $sales[ 'customer_id' ] )
                             ->where( 'total', '!=', 0 )
                             ->where( 'is_trash', '!=', 1 )
                             ->get();
        foreach ( $orgPayments as $orgPayment ) {
            $orgBalance = MapInvoicePayment::where( 'invoice_id', $invoice[ 'id' ] )->where( 'payment_id', Payment::where( 'sales_id', $orgPayment->id )->first()->id )->first();
            if ( $orgBalance ) {
                $orgBalance = $orgPayment->balance + $orgBalance->payment;
                $status = $orgBalance == $orgPayment->total ? 'Unapplied' : ( $orgBalance == 0 ? 'Closed' : 'Partial' );
                $orgPayment->update( [ 'balance' => $orgBalance, 'status' => $this->statuses[ 'Payment' ][ $status ] ] );
            }
        }

        $zeroPayment = MapInvoicePayment::where( 'invoice_id', $invoice[ 'id' ] )
                                        ->join( 'payment', 'map_invoice_payment.payment_id', '=', 'payment.id' )
                                        ->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
                                        ->where( 'sales.total', 0 )
                                        ->select( 'sales.id as sales_id', 'payment.id as payment_id' )
                                        ->first();

        $orgCreditNotes = Sales::where( 'transaction_type', $this->transactionTypes[ 'Credit Note' ] )
                                ->where( 'customer_id', $sales[ 'customer_id' ] )
                                ->where( 'is_trash', '!=', 1 )
                                ->get();
                                
        foreach ( $orgCreditNotes as $orgCreditNote ) {
            $orgBalance = MapCreditNotePayment::where( 'credit_note_id', CreditNote::where( 'sales_id', $orgCreditNote->id )->first()->id )->where( 'payment_id', $zeroPayment->payment_id )->first();
            if ( $orgBalance ) {
                $orgBalance = $orgCreditNote->balance + $orgBalance->payment;
                $status = $orgBalance == $orgCreditNote->total ? 'Unapplied' : ( $orgBalance == 0 ? 'Closed' : 'Partial' );
                $orgCreditNote->update( [ 'balance'=> $orgBalance, 'status' => $this->statuses[ 'Credit Note'][ $status ] ] );
            }
        }
        
        $unClosedPayments = Sales::where( 'customer_id', $sales[ 'customer_id' ] )
                                ->where( 'transaction_type', $this->transactionTypes[ 'Payment' ] )
                                ->where( 'status', '!=', $this->statuses[ 'Payment' ][ 'Closed' ] )
                                ->where( 'is_trash', '!=', 1 );
        $unClosedPaymentTotal = $unClosedPayments->sum( 'balance' );

        $unClosedCreditNotes = Sales::where( 'customer_id', $sales[ 'customer_id' ] )
                                ->where( 'transaction_type', $this->transactionTypes[ 'Credit Note' ] )
                                ->where( 'status', '!=', $this->statuses[ 'Credit Note' ][ 'Closed' ] )
                                ->where( 'is_trash', '!=', 1);
        $unClosedCreditNoteTotal = $unClosedCreditNotes->sum( 'balance' );

        if ( $unClosedPaymentTotal + $unClosedCreditNoteTotal > 0 ) {
            $sales[ 'balance' ] = max( $sales[ 'total' ] - $unClosedPaymentTotal - $unClosedCreditNoteTotal, 0 );
            $sales[ 'status' ] = $sales[ 'balance' ] > 0 ? $this->statuses[ 'Invoice' ][ 'Partial' ] : $this->statuses[ 'Invoice' ][ 'Paid' ];
        }
        $orgSales = Sales::find( $sales[ 'id' ] );
        Sales::find( $sales[ 'id' ] )->update( $sales );

        $invoice[ 'sales_id' ] = $sales[ 'id' ];
        Invoice::find( $invoice[ 'id' ] )->update( $invoice );

        $mapInvoicePayment = [];
        $mapInvoicePayment[ 'invoice_id' ] = $invoice[ 'id' ];
        $unClosedPayments = $unClosedPayments->get();

        $total = $sales[ 'total' ];
        if ( count( $unClosedPayments ) > 0 ) {
            foreach ( $unClosedPayments as $payment ) {
                if ( $total > 0 ) {
                    $status = $payment->balance > $total ? 'Partial' : 'Closed';
                    if ( Sales::find( $payment->id )->balance != max( $payment->balance - $total, 0 ) ) {
                        $this->createAuditLog(
                            [
                                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                                'record_id' => $payment->id,
                                'trxn_id' => $payment->id,
                                'date_changed' => date( 'Y-m-d H:i:s' ), 
                                'user_email' => \Auth::user()->email, 
                                'event_text' => 'Edited ',
                                'target_name' => 'Payment',
                                'person_id' => Sales::find( $payment->id )->customer_id,
                                'person_type' => $this->personTypes[ 'Customer' ],
                                'date' => $sales[ 'date' ],
                                'amount' => Sales::find( $payment->id )->total,
                                'open_balance' => Sales::find( $payment->id )->balance
                            ] 
                        );
                    }
                    Sales::find( $payment->id )->update( [ 'status' => $this->statuses[ 'Payment' ][ $status ], 'balance' => max( $payment->balance - $total, 0 ) ] );

                    $mapInvoicePayment[ 'payment' ] = min( $payment->balance, $total );
                    $mapInvoicePayment[ 'payment_id' ] = Payment::where( 'sales_id', $payment->id )->first()->id;
                    $orgMap = MapInvoicePayment::where( 'invoice_id', $mapInvoicePayment[ 'invoice_id' ] )->where( 'payment_id', $mapInvoicePayment[ 'payment_id' ] )->first();
                    if ( $orgMap )
                        $orgMap->update( $mapInvoicePayment );
                    else
                        MapInvoicePayment::create( $mapInvoicePayment );

                    $total -= $payment->balance;
                }
            }
        }

        if ( $total > 0 ) {
            $unClosedCreditNotes = $unClosedCreditNotes->get();

            if ( count( $unClosedCreditNotes ) > 0 ) {
                $transaction = [
                    'transaction_type' => $this->transactionTypes[ 'Payment' ],
                    'customer_id' => $this->customer[ $sales[ 'customer' ] ]->id,
                    'total' => 0,
                    'status' => $this->statuses[ 'Payment' ][ 'Closed' ]
                ];
                $payment = [
                    'note' => 'Created by Sejllat to link credits to charges.',
                    'account_id' => $this->account[ 'Cash' ]->id,
                ]; 
                $transaction[ 'date' ] = $transaction[ 'due_date' ] = date( 'Y-m-d' );

                $payment = Sales::find( $zeroPayment->sales_id )->update( $transaction );

                $mapInvoicePayment[ 'invoice_id' ] = $invoice[ 'id' ];
                $mapInvoicePayment[ 'payment_id' ] = $zeroPayment->payment_id;
                $mapInvoicePayment[ 'payment' ] = 0;

                $mapCreditNotePayment = [ 'payment_id' => $zeroPayment->payment_id ];

                foreach ( $unClosedCreditNotes as $creditNote ) {
                    if ( $total > 0 ) {
                        $status = $creditNote->balance > $total ? 'Partial' : 'Closed';
                        if ( Sales::find( $creditNote->id )->balance != max( $creditNote->balance - $total, 0 ) ) {
                            $this->createAuditLog(
                                [
                                    'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                                    'record_id' => $creditNote->id,
                                    'trxn_id' => Sales::find( $creditNote->id )->invoice_receipt_no,
                                    'date_changed' => date( 'Y-m-d H:i:s' ), 
                                    'user_email' => \Auth::user()->email, 
                                    'event_text' => 'Edited ',
                                    'target_name' => 'Credit Note',
                                    'person_id' => Sales::find( $creditNote->id )->customer_id,
                                    'person_type' => $this->personTypes[ 'Customer' ],
                                    'date' => $sales[ 'date' ],
                                    'amount' => Sales::find( $creditNote->id )->total,
                                    'open_balance' => Sales::find( $creditNote->id )->balance,
                                    'items' => CreditNoteItem::where( 'credit_note_id', CreditNote::where( 'sales_id', $creditNote->id )->first()->id )->get()->toArray(),
                                    'is_indirect' => 1
                                ] 
                            );
                        }
                        Sales::find( $creditNote->id )->update( [ 'status' => $this->statuses[ 'Credit Note' ][ $status ], 'balance' => max( $creditNote->balance - $total, 0 ) ] );

                        $mapCreditNotePayment[ 'credit_note_id' ] = CreditNote::where( 'sales_id', $creditNote->id )->first()->id;
                        $mapCreditNotePayment[ 'payment' ] = min( $creditNote->balance, $total );
                        $mapInvoicePayment[ 'payment' ] += $mapCreditNotePayment[ 'payment' ];
                        $orgMap = MapCreditNotePayment::where( 'credit_note_id', $mapCreditNotePayment[ 'credit_note_id' ] )->where( 'payment_id', $mapCreditNotePayment[ 'payment_id' ] )->first();
                        if ( $orgMap )
                            $orgMap->update( $mapCreditNotePayment );
                        else
                            MapCreditNotePayment::create( $mapCreditNotePayment );

                        $total -= $creditNote->balance;
                    }
                }

                MapInvoicePayment::where( 'invoice_id', $mapInvoicePayment[ 'invoice_id' ] )->where( 'payment_id', $mapInvoicePayment[ 'payment_id' ] )->update( $mapInvoicePayment );
            }
        }

        $totalCostOfSalesAmount = 0;
        $totalOrgCostOfSalesAmount = 0;
        foreach ( $invoiceItems as $invoiceItem ) {
            if ( isset( $invoiceItem[ 'product_service' ] ) || $invoiceItem[ 'item_type' ] == 2 ) {
                $productService = $this->product_service[ $invoiceItem[ 'product_service' ] ];
                $invoiceItem[ 'invoice_id' ] = $invoice[ 'id' ];
                if ( $invoiceItem[ 'item_type' ] != 2 )
                    $invoiceItem[ 'product_service_id' ] = $productService->id;

                if ( $productService->is_inventoriable == 1 )
                    $totalCostOfSalesAmount += ProductService::find( $invoiceItem[ 'product_service_id' ] )->purchase_price * $invoiceItem[ 'qty' ];

                if ( isset( $invoiceItem[ 'id' ] ) ) {
                    if ( $productService->is_inventoriable == 1 )
                        $totalOrgCostOfSalesAmount += ProductService::find( $invoiceItem[ 'product_service_id' ] )->purchase_price * InvoiceItem::find( $invoiceItem[ 'id' ] )->qty;
                    InvoiceItem::find( $invoiceItem[ 'id' ] )->update( $invoiceItem );
                }
                else
                    InvoiceItem::create( $invoiceItem );
            }
        }

        if ( isset( $GLOBALS[ 'input' ][ 'attachments' ]) ) {
            $attachments = $GLOBALS[ 'input' ][ 'attachments' ];
            foreach ( $attachments as $attachment ) {
                $attachment = Attachment::create( $attachment );
                MapSalesAttachment::create( [ 'sales_id' => $sales->id, 'attachment_id' => $attachment->id ] );
            }
        }

        Customer::find( $sales[ 'customer_id' ] )->update( [ 'balance' => Customer::find( $sales[ 'customer_id' ] )->balance + $sales[ 'total' ] - $orgSales->total ] );
        
        $accountId = $this->account[ 'Accounts Receivable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales[ 'total' ] - $orgSales->total ] );

        $accountId = $this->account[ 'Sales Revenues' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales[ 'total' ] - $orgSales->total ] );

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
                'target_name' => 'Invoice',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'open_balance' => $sales[ 'balance' ],
                'message' => $invoice[ 'message' ],
                'memo' => $invoice[ 'statement_memo' ]
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
        $sales->update( [ 'is_trash'=> 1 ] );

        $invoice = Invoice::where( 'sales_id', $sales->id )->first();
        $invoiceItems = InvoiceItem::where( 'invoice_id', $invoice->id )->get();

        $totalCostOfSalesAmount = 0;
        foreach ( $invoiceItems as $invoiceItem ) {
            $productService = ProductService::find( $invoiceItem[ 'product_service_id' ] );
            if ( $productService->is_inventoriable == 1 )
                $totalCostOfSalesAmount += $productService->purchase_price * $invoiceItem[ 'qty' ];
        }

        Customer::find( $sales->customer_id )->update( [ 'balance' => Customer::find( $sales->customer_id )->balance - $sales->balance ] );
        
        $accountId = $this->account[ 'Accounts Receivable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales->total ] );

        $accountId = $this->account[ 'Sales Revenues' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales->total ] );

        $accountId = $this->account[ 'Cost of Sales/Services' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $totalCostOfSalesAmount ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalCostOfSalesAmount ] );

        $zeroPayment = MapInvoicePayment::where( 'invoice_id', $invoice[ 'id' ] )
                                        ->join( 'payment', 'map_invoice_payment.payment_id', '=', 'payment.id' )
                                        ->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
                                        ->where( 'sales.total', 0 )
                                        ->select( 'sales.id as sales_id', 'payment.id as payment_id' )
                                        ->first();

        if ( $zeroPayment ) {
            Sales::find( $zeroPayment->sales_id )->update( [ 'is_trash' => 1 ] );

            $mapInvoicePayment = MapInvoicePayment::join( 'invoice', 'map_invoice_payment.invoice_id', '=', 'invoice.id' )->join( 'sales', 'invoice.sales_id', '=', 'sales.id' )
                ->where( 'sales.id', $sales->id )->get();

            $totalPayment = 0;
            foreach ( $mapInvoicePayment as $map ) {
                $sales = MapInvoicePayment::join( 'payment', 'map_invoice_payment.payment_id', '=', 'payment.id' )->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
                    ->where( 'payment_id', $map->payment_id )->where( 'payment_id', '!=', $zeroPayment->payment_id )->first();
                if ( $sales ) {
                    $balance = Sales::find( $sales->id )->balance + $map->payment;
                    $status = $balance == $sales->total ? $this->statuses[ 'Payment' ][ 'Unapplied' ] : $this->statuses[ 'Payment' ][ 'Partial' ];

                    Sales::find( $sales->id )->update( [ 'balance' => $balance, 'status' => $status ] );
                    $totalPayment += $map->payment;
                }
            }

            $mapCreditNotePayment = MapCreditNotePayment::where( 'payment_id', $zeroPayment->payment_id )->get();
            foreach ( $mapCreditNotePayment as $map ) {
                $sales = MapCreditNotePayment::join( 'credit_note', 'map_credit_note_payment.credit_note_id', '=', 'credit_note.id' )->join( 'sales', 'credit_note.sales_id', '=', 'sales.id' )
                    ->where( 'credit_note_id', $map->credit_note_id )->first();

                if ( $sales ) {
                    $balance = Sales::find( $sales->id )->balance + $map->payment;
                    $status = $balance == $sales->total ? $this->statuses[ 'Credit Note' ][ 'Unapplied' ] : $this->statuses[ 'Credit Note' ][ 'Partial' ];

                    Sales::find( $sales->id )->update( [ 'balance' => $balance, 'status' => $status ] );
                    $totalPayment += $map->payment;
                }   
            }

            Customer::find( Sales::find( $id )->customer_id )->update( [ 'balance' => Customer::find( Sales::find( $id )->customer_id )->balance - $totalPayment ] );
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $sales[ 'invoice_receipt_no' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Deleted ',
                'target_name' => 'Invoice',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'open_balance' => $sales[ 'balance' ],
                'message' => $invoice[ 'message' ],
                'memo' => $invoice[ 'statement_memo' ]
            ] 
        );
    }

    public function recoverDelete( Request $request, $id ) {
        $sales = Sales::find( $id );
        $sales->update( [ 'is_trash'=> 0 ] );

        $invoice = Invoice::where( 'sales_id', $sales->id )->first();
        $invoiceItems = InvoiceItem::where( 'invoice_id', $invoice->id )->get();

        $totalCostOfSalesAmount = 0;
        foreach ( $invoiceItems as $invoiceItem ) {
            $productService = ProductService::find( $invoiceItem[ 'product_service_id' ] );
            if ( $productService->is_inventoriable == 1 )
                $totalCostOfSalesAmount += $productService->purchase_price * $invoiceItem[ 'qty' ];
        }

        Customer::find( $sales->customer_id )->update( [ 'balance' => Customer::find( $sales->customer_id )->balance + $sales->balance ] );
        
        $accountId = $this->account[ 'Accounts Receivable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales->total ] );

        $accountId = $this->account[ 'Sales Revenues' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales->total ] );

        $accountId = $this->account[ 'Cost of Sales/Services' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalCostOfSalesAmount ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $totalCostOfSalesAmount ] );

        $zeroPayment = MapInvoicePayment::where( 'invoice_id', $invoice[ 'id' ] )
                                        ->join( 'payment', 'map_invoice_payment.payment_id', '=', 'payment.id' )
                                        ->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
                                        ->where( 'sales.total', 0 )
                                        ->select( 'sales.id as sales_id', 'payment.id as payment_id' )
                                        ->first();

        if ( $zeroPayment ) {
            Sales::find( $zeroPayment->sales_id )->update( [ 'is_trash' => 0 ] );

            $mapInvoicePayment = MapInvoicePayment::join( 'invoice', 'map_invoice_payment.invoice_id', '=', 'invoice.id' )->join( 'sales', 'invoice.sales_id', '=', 'sales.id' )
                ->where( 'sales.id', $sales->id )->get();

            $totalPayment = 0;
            foreach ( $mapInvoicePayment as $map ) {
                $sales = MapInvoicePayment::join( 'payment', 'map_invoice_payment.payment_id', '=', 'payment.id' )->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
                    ->where( 'payment_id', $map->payment_id )->where( 'payment_id', '!=', $zeroPayment->payment_id )->first();
                if ( $sales ) {
                    $balance = Sales::find( $sales->id )->balance - $map->payment;
                    $status = $balance == $sales->total ? $this->statuses[ 'Payment' ][ 'Unapplied' ] : ( $balance == 0 ? $this->statuses[ 'Payment' ][ 'Closed' ] : $this->statuses[ 'Payment' ][ 'Partial' ] );

                    Sales::find( $sales->id )->update( [ 'balance' => $balance, 'status' => $status ] );
                    $totalPayment += $map->payment;
                }
            }

            $mapCreditNotePayment = MapCreditNotePayment::where( 'payment_id', $zeroPayment->payment_id )->get();
            foreach ( $mapCreditNotePayment as $map ) {
                $sales = MapCreditNotePayment::join( 'credit_note', 'map_credit_note_payment.credit_note_id', '=', 'credit_note.id' )
                    ->join( 'sales', 'credit_note.sales_id', '=', 'sales.id' )
                    ->where( 'credit_note_id', $map->credit_note_id )->first();
                if ( $sales ) {
                    $balance = Sales::find( $sales->id )->balance - $map->payment;
                    $status = $balance == $sales->total ? $this->statuses[ 'Credit Note' ][ 'Unapplied' ] : $this->statuses[ 'Credit Note' ][ 'Partial' ];

                    Sales::find( $sales->id )->update( [ 'balance' => $balance, 'status' => $status ] );
                    $totalPayment += $map->payment;
                }
            }

            Customer::find( $sales->customer_id )->update( [ 'balance' => Customer::find( $sales->customer_id )->balance + $totalPayment ] );
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $sales[ 'invoice_receipt_no' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Recovered ',
                'target_name' => 'Invoice',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'open_balance' => $sales[ 'balance' ],
                'message' => $invoice[ 'message' ],
                'memo' => $invoice[ 'statement_memo' ]
            ] 
        );
    }
}
