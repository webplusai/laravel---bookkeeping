<?php

namespace App\Http\Controllers\TRXN;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Helper\RestResponseMessages;

use App\Models\Sales;
use App\Models\CreditNote;
use App\Models\CreditNoteItem;

use App\Models\Invoice;
use App\Models\InvoiceItem;

use App\Models\Attachment;
use App\Models\MapInvoicePayment;
use App\Models\MapSalesAttachment;
use App\Models\MapCreditNotePayment;

use App\Models\Account;
use App\Models\Payment;
use App\Models\Customer;

class CreditNoteController extends TRXNController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $sales              =   $GLOBALS[ 'input' ][ 'transaction' ];
        $creditNote         =   $GLOBALS[ 'input' ][ 'creditNote' ];
        $creditNoteItems    =   array_filter( $GLOBALS[ 'input' ][ 'creditNoteItems' ] );

        $sales[ 'customer_id' ] = $this->customer[ $sales[ 'customer' ] ]->id;

        $unPaidInvoices = Sales::where( 'customer_id', $sales[ 'customer_id' ] )
                                    ->where( 'transaction_type', $this->transactionTypes[ 'Invoice' ] )
                                    ->where( 'status', '!=', $this->statuses[ 'Invoice' ][ 'Paid' ] )
                                    ->where( 'is_trash', '!=', 1 );
        $unPaidInvoiceTotal = $unPaidInvoices->sum( 'balance' );
        if ( $unPaidInvoiceTotal > 0 ) {
            $sales[ 'balance' ] = max( $sales[ 'total' ] - $unPaidInvoiceTotal, 0 );
            $sales[ 'status' ] = $sales[ 'balance' ] > 0 ? $this->statuses[ 'Credit Note' ][ 'Partial' ] : $this->statuses[ 'Credit Note' ][ 'Closed' ];
        }

        $sales = Sales::create( $sales );

        $creditNote[ 'sales_id' ] = $sales->id;
        $creditNote = CreditNote::create( $creditNote );

        foreach ( $creditNoteItems as $creditNoteItem ) {
            if ( isset( $creditNoteItem[ 'product_service' ] ) || $creditNoteItem[ 'item_type' ] == 2 ) {

                $productService = $this->product_service[ $creditNoteItem[ 'product_service' ] ];
                $creditNoteItem[ 'credit_note_id' ] = $creditNote->id;
                if ( $creditNoteItem[ 'item_type' ] != 2 )
                    $creditNoteItem[ 'product_service_id' ] = $productService->id;
                CreditNoteItem::create( $creditNoteItem );
            }
        }

        $unPaidInvoices = $unPaidInvoices->get();

        if ( count( $unPaidInvoices ) > 0 ) {

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
                    'memo' => $payment[ 'note' ],
                    'is_indirect' => 1
                ] 
            );

            $mapInvoicePayment[ 'payment_id' ] = $payment->id;

            $mapCreditNotePayment = [ 
                'payment_id' => $payment->id,
                'credit_note_id' => $creditNote->id,
                'payment' => 0
            ];

            $total = $sales[ 'total' ];

            foreach ( $unPaidInvoices as $invoice ) {
                if ( $total > 0 ) {
                    $status = $invoice->balance > $total ? 'Partial' : 'Paid';
                    Sales::find( $invoice->id )->update( [ 'status' => $this->statuses [ 'Invoice' ][ $status ], 'balance' => max( $invoice->balance - $total, 0 ) ] );

                    $this->createAuditLog(
                        [
                            'table_id' => $this->tableIdsForAudit[ 'Sales' ],
                            'record_id' => $invoice->id,
                            'trxn_id' => Sales::find( $invoice->id )->invoice_receipt_no,
                            'date_changed' => date( 'Y-m-d H:i:s' ),
                            'user_email' => \Auth::user()->email,
                            'event_text' => 'Edited ',
                            'target_name' => 'Invoice',
                            'person_id' => $invoice->customer_id,
                            'person_type' => $this->personTypes[ 'Customer' ],
                            'date' => $transaction[ 'date' ],
                            'amount' => $invoice->total,
                            'open_balance' => max( $invoice->balance - $total, 0 ),
                            'items' => InvoiceItem::where( 'invoice_id', Invoice::where( 'sales_id', $invoice->id )->first()->id )->get()->toArray(),
                            'is_indirect' => 1
                        ] 
                    );

                    $mapInvoicePayment[ 'invoice_id' ] = $invoice->id;
                    $mapInvoicePayment[ 'payment' ] = min( $invoice->balance, $total );
                    $mapCreditNotePayment[ 'payment' ] += $mapInvoicePayment[ 'payment' ];
                    MapInvoicePayment::create( $mapInvoicePayment );

                    $total -= $invoice->balance;
                }
            }

            MapCreditNotePayment::create( $mapCreditNotePayment );
        }

        if ( isset( $GLOBALS[ 'input' ][ 'attachments' ] ) ) {
            $attachments    =   $GLOBALS[ 'input' ][ 'attachments' ];
            foreach ( $attachments as $attachment ) {
                $attachment = Attachment::create( $attachment );
                MapSalesAttachment::create( [ 'sales_id' => $sales->id, 'attachment_id' => $attachment->id ] );
            }
        }

        Customer::find( $sales[ 'customer_id' ] )->update( [ 'balance' => Customer::find( $sales[ 'customer_id' ] )->balance - $sales[ 'total' ] ] );
        
        $accountId = $this->account[ 'Accounts Receivable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales[ 'total' ] ] );

        $accountId = $this->account[ 'Sales Revenues' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales[ 'total' ] ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $sales[ 'invoice_receipt_no' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Added ',
                'target_name' => 'Credit Note',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'open_balance' => $sales[ 'balance' ],
                'message' => $creditNote[ 'message' ],
                'memo' => $creditNote[ 'statement_memo' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage( 'Create Credit Note', $sales, 200 );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( $id )
    {
        $sales = Sales::find( $id );
        $creditNote = CreditNote::where( 'sales_id', $sales->id )->first();
        $creditNoteItems = CreditNoteItem::where( 'credit_note_id', $creditNote->id )->get();

        return RestResponseMessages::TRXNSuccessMessage( 'Retrieve Credit Note', [ 'transaction' => $sales, 'creditNote' => $creditNote, 'creditNoteItems' => $creditNoteItems ], 200 );
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
        $sales          =   $GLOBALS[ 'input' ][ 'transaction' ];
        $creditNote        =   $GLOBALS[ 'input' ][ 'creditNote' ];
        $creditNoteItems   =   array_filter( $GLOBALS[ 'input' ][ 'creditNoteItems' ] );

        $sales[ 'customer_id' ] = $this->customer[ $sales[ 'customer' ] ]->id;

        $zeroPayment = MapCreditNotePayment::where( 'credit_note_id', $creditNote[ 'id' ] )
                                        ->join( 'payment', 'map_credit_note_payment.payment_id', '=', 'payment.id' )
                                        ->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
                                        ->where( 'sales.total', 0 )
                                        ->select( 'sales.id as sales_id', 'payment.id as payment_id' )
                                        ->first();

        $orgInvoices = Sales::where( 'transaction_type', $this->transactionTypes[ 'Invoice' ] )
                                    ->where( 'customer_id', $sales[ 'customer_id' ] )
                                    ->where( 'is_trash', '!=', 1)
                                    ->get();

        foreach ( $orgInvoices as $orgInvoice ) {
            $orgBalance = MapInvoicePayment::where( 'invoice_id', $orgInvoice->id )->where( 'payment_id', $zeroPayment->payment_id )->first();
            if ( $orgBalance ) {
                $orgBalance = $orgInvoice->balance + $orgBalance->payment;
                $status = $orgBalance == $orgInvoice->total ? 'Unpaid' : ( $orgBalance == 0 ? 'Paid' : 'Partial' );
                $orgInvoice->update( [ 'status' => $status, 'balance' => $orgBalance ] );
            }
        }

        $unPaidInvoices = Sales::where( 'customer_id', $sales[ 'customer_id' ] )
                                    ->where( 'transaction_type', $this->transactionTypes[ 'Invoice' ] )
                                    ->where( 'status', '!=', $this->statuses[ 'Invoice' ][ 'Paid' ] )
                                    ->where( 'is_trash', '!=', 1 );

        $unPaidInvoiceTotal = $unPaidInvoices->sum( 'balance' );
        if ( $unPaidInvoiceTotal > 0 ) {
            $sales[ 'balance' ] = max( $sales[ 'total' ] - $unPaidInvoiceTotal, 0 );
            $sales[ 'status' ] = $sales[ 'balance' ] > 0 ? $this->statuses[ 'Credit Note' ][ 'Partial' ] : $this->statuses[ 'Credit Note' ][ 'Closed' ];
        }

        $orgSales = Sales::find( $id );
        Sales::find( $id )->update( $sales );

        CreditNote::find( $creditNote[ 'id' ] )->update( $creditNote );

        foreach ( $creditNoteItems as $creditNoteItem ) {
            if ( isset( $creditNoteItem[ 'product_service' ] ) || $creditNoteItem[ 'item_type' ] == 2 ) {

                $productService = $this->product_service[ $creditNoteItem[ 'product_service' ] ];
                $creditNoteItem[ 'credit_note_id' ] = $creditNote[ 'id' ];
                if ( $creditNoteItem[ 'item_type' ] != 2 )
                    $creditNoteItem[ 'product_service_id' ] = $productService->id;


                if ( isset( $creditNoteItem[ 'id' ] ) )
                    CreditNoteItem::find( $creditNoteItem[ 'id' ] )->update( $creditNoteItem );
                else
                    CreditNoteItem::create( $creditNoteItem );
            }
        }

        $unPaidInvoices = $unPaidInvoices->get();

        if ( count( $unPaidInvoices ) > 0 ) {

            $transaction = [
                'transaction_type' => $this->transactionTypes[ 'Payment' ],
                'customer_id' => $sales[ 'customer_id' ],
                'total' => 0,
                'status' => $this->statuses[ 'Payment' ][ 'Closed' ]
            ];
            $payment = [
                'note' => 'Created by Sejllat to link credits to charges.',
                'account_id' => $this->account[ 'Cash' ]->id,
            ]; 
            $transaction[ 'date' ] = $transaction[ 'due_date' ] = date( 'Y-m-d' );
            Sales::find( $zeroPayment->sales_id )->update( $transaction );
            $this->createAuditLog(
                [
                    'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                    'record_id' => $zeroPayment->sales_id,
                    'trxn_id' => $zeroPayment->payment_id,
                    'date_changed' => date( 'Y-m-d H:i:s' ), 
                    'user_email' => \Auth::user()->email, 
                    'event_text' => 'Edited ',
                    'target_name' => 'Payment',
                    'person_id' => $sales[ 'customer_id' ],
                    'person_type' => $this->personTypes[ 'Customer' ],
                    'date' => $sales[ 'date' ],
                    'amount' => Sales::find( $zeroPayment->sales_id )->total,
                    'open_balance' => Sales::find( $zeroPayment->sales_id )->balance,
                    'is_indirect' => 1
                ] 
            );

            Payment::find( $zeroPayment->payment_id )->update( $payment );

            $mapInvoicePayment[ 'payment_id' ] = $zeroPayment->payment_id;

            $mapCreditNotePayment = [ 
                'payment_id' => $zeroPayment->payment_id,
                'credit_note_id' => $creditNote[ 'id' ],
                'payment' => 0
            ];

            $total = $sales[ 'total' ];

            foreach ( $unPaidInvoices as $invoice ) {
                if ( $total > 0 ) {
                    $status = $invoice->balance > $total ? 'Partial' : 'Paid';
                    Sales::find( $invoice->id )->update( [ 'status' => $this->statuses [ 'Invoice' ][ $status ], 'balance' => max( $invoice->balance - $total, 0 ) ] );

                    $this->createAuditLog(
                        [
                            'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                            'record_id' => $invoice->id,
                            'trxn_id' => Sales::find( $invoice->id )->invoice_receipt_no,
                            'date_changed' => date( 'Y-m-d H:i:s' ), 
                            'user_email' => \Auth::user()->email, 
                            'event_text' => 'Edited ',
                            'target_name' => 'Invoice',
                            'person_id' => $sales[ 'customer_id' ],
                            'person_type' => $this->personTypes[ 'Customer' ],
                            'date' => $sales[ 'date' ],
                            'amount' => Sales::find( $invoice->id )->total,
                            'open_balance' => Sales::find( $invoice->id )->balance,
                            'items' => InvoiceItem::where( 'invoice_id', Invoice::where( 'sales_id', $invoice->id )->first()->id )->get()->toArray(),
                            'is_indirect' => 1
                        ] 
                    );

                    $mapInvoicePayment[ 'invoice_id' ] = $invoice->id;
                    $mapInvoicePayment[ 'payment' ] = min( $invoice->balance, $total );
                    $mapCreditNotePayment[ 'payment' ] += $mapInvoicePayment[ 'payment' ];

                    $orgMap = MapInvoicePayment::where( 'invoice_id', $mapInvoicePayment[ 'invoice_id' ] )->where( 'payment_id', $mapInvoicePayment[ 'payment_id' ] )->first();
                    if ( $orgMap ) {
                        $orgMap->update( $mapInvoicePayment );
                    } else {
                        MapInvoicePayment::create( $mapInvoicePayment );
                    }

                    $total -= $invoice->balance;
                }
            }

            MapCreditNotePayment::where( 'credit_note_id', $creditNote[ 'id' ] )->where( 'payment_id', $zeroPayment[ 'id' ] )->update( $mapCreditNotePayment );
        }

        if ( isset( $GLOBALS[ 'input' ][ 'attachments' ] ) ) {
            $attachments    =   $GLOBALS[ 'input' ][ 'attachments' ];
            foreach ( $attachments as $attachment ) {
                $attachment = Attachment::create( $attachment );
                MapSalesAttachment::create( [ 'sales_id' => $sales->id, 'attachment_id' => $attachment->id ] );
            }
        }

        Customer::find( $sales[ 'customer_id' ] )->update( [ 'balance' => Customer::find( $sales[ 'customer_id' ] )->balance - $sales[ 'total' ] + $orgSales->total ] );
        
        $accountId = $this->account[ 'Accounts Receivable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales[ 'total' ] + $orgSales->total ] );

        $accountId = $this->account[ 'Sales Revenues' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales[ 'total' ] + $orgSales->total ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $sales[ 'invoice_receipt_no' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Edited ',
                'target_name' => 'Credit Note',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'open_balance' => $sales[ 'balance' ],
                'message' => $creditNote[ 'message' ],
                'memo' => $creditNote[ 'statement_memo' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage( 'Update Credit Note', $sales, 200 );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id )
    {
        $sales = Sales::find( $id );
        $sales->update( [ 'is_trash' => 1 ] );

        $creditNote = CreditNote::where( 'sales_id', $sales[ 'id' ] )->first();
        $creditNoteItems = CreditNoteItem::where( 'credit_note_id', $creditNote->id )->get();

        Customer::find( $sales[ 'customer_id' ] )->update( [ 'balance' => Customer::find( $sales[ 'customer_id' ] )->balance + $sales->balance ] );
        
        $accountId = $this->account[ 'Accounts Receivable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales[ 'total' ] ] );

        $accountId = $this->account[ 'Sales Revenues' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales[ 'total' ] ] );

        $zeroPayment = MapCreditNotePayment::where( 'credit_note_id', $creditNote->id )
                                        ->join( 'payment', 'map_credit_note_payment.payment_id', '=', 'payment.id' )
                                        ->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
                                        ->where( 'sales.total', 0 )
                                        ->select( 'sales.id as sales_id', 'payment.id as payment_id' )
                                        ->first();

        Sales::find( $zeroPayment->sales_id )->update( [ 'is_trash' => 1 ] );

        $mapInvoicePayment = MapInvoicePayment::join( 'payment', 'map_invoice_payment.payment_id', '=', 'payment.id' )->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
            ->where( 'sales.id', $zeroPayment->sales_id )->get();

        $totalPayment = 0;
        foreach ( $mapInvoicePayment as $map ) {
            $sales = MapInvoicePayment::join( 'invoice', 'map_invoice_payment.invoice_id', '=', 'invoice.id' )->join( 'sales', 'invoice.sales_id', '=', 'sales.id' )
                ->where( 'payment_id', $map->payment_id )->first();
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
                ->where( 'credit_note_id', $map->credit_note_id )->where( 'payment_id', '!=', $zeroPayment->payment_id )->first();
            if ( $sales ) {
                $balance = Sales::find( $sales->id )->balance + $map->payment;
                $status = $balance == $sales->total ? $this->statuses[ 'Credit Note' ][ 'Unapplied' ] : $this->statuses[ 'Credit Note' ][ 'Partial' ];

                Sales::find( $sales->id )->update( [ 'balance' => $balance, 'status' => $status ] );
                $totalPayment += $map->payment;
            }
        }

        $sales = Sales::find( $id );
        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $sales[ 'invoice_receipt_no' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Deleted ',
                'target_name' => 'Credit Note',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'open_balance' => $sales[ 'balance' ],
                'message' => $creditNote[ 'message' ],
                'memo' => $creditNote[ 'statement_memo' ]
            ] 
        );

        Customer::find( Sales::find( $id )->customer_id )->update( [ 'balance' => Customer::find( Sales::find( $id )->customer_id )->balance + $totalPayment ] );
    }

    public function recoverDelete( $id ) {
        
        $sales = Sales::find( $id );
        $sales->update( [ 'is_trash' => 0 ] );

        $creditNote = CreditNote::where( 'sales_id', $sales[ 'id' ] )->first();
        $creditNoteItems = CreditNoteItem::where( 'credit_note_id', $creditNote->id )->get();

        $zeroPayment = MapCreditNotePayment::where( 'credit_note_id', $creditNote->id )
                                        ->join( 'payment', 'map_credit_note_payment.payment_id', '=', 'payment.id' )
                                        ->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
                                        ->where( 'sales.total', 0 )
                                        ->select( 'sales.id as sales_id', 'payment.id as payment_id' )
                                        ->first();

        Customer::find( $sales[ 'customer_id' ] )->update( [ 'balance' => Customer::find( $sales[ 'customer_id' ] )->balance - $sales->balance ] );
        
        $accountId = $this->account[ 'Accounts Receivable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales[ 'total' ] ] );

        $accountId = $this->account[ 'Sales Revenues' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales[ 'total' ] ] );

        Sales::find( $zeroPayment->sales_id )->update( [ 'is_trash' => 0 ] );

        $mapInvoicePayment = MapInvoicePayment::join( 'payment', 'map_invoice_payment.payment_id', '=', 'payment.id' )->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
            ->where( 'sales.id', $zeroPayment->sales_id )->get();

        $totalPayment = 0;
        foreach ( $mapInvoicePayment as $map ) {
            $sales = MapInvoicePayment::join( 'invoice', 'map_invoice_payment.invoice_id', '=', 'invoice.id' )->join( 'sales', 'invoice.sales_id', '=', 'sales.id' )
                ->where( 'payment_id', $map->payment_id )->first();
            if ( $sales ) {
                $balance = Sales::find( $sales->id )->balance - $map->payment;
                $status = $balance == $sales->total ? $this->statuses[ 'Payment' ][ 'Unapplied' ] : $this->statuses[ 'Payment' ][ 'Partial' ];

                Sales::find( $sales->id )->update( [ 'balance' => $balance, 'status' => $status ] );
                $totalPayment += $map->payment;
            }
        }

        $mapCreditNotePayment = MapCreditNotePayment::where( 'payment_id', $zeroPayment->payment_id )->get();

        foreach ( $mapCreditNotePayment as $map ) {
            $sales = MapCreditNotePayment::join( 'credit_note', 'map_credit_note_payment.credit_note_id', '=', 'credit_note.id' )->join( 'sales', 'credit_note.sales_id', '=', 'sales.id' )
                ->where( 'credit_note_id', $map->credit_note_id )->where( 'payment_id', '!=', $zeroPayment->payment_id )->first();
            if ( $sales ) {
                $balance = Sales::find( $sales->id )->balance - $map->payment;
                $status = $balance == $sales->total ? $this->statuses[ 'Credit Note' ][ 'Unapplied' ] : $this->statuses[ 'Credit Note' ][ 'Partial' ];

                Sales::find( $sales->id )->update( [ 'balance' => $balance, 'status' => $status ] );
                $totalPayment += $map->payment;
            }
        }

        $sales = Sales::find( $id );
        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $sales[ 'invoice_receipt_no' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Recovered ',
                'target_name' => 'Credit Note',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'open_balance' => $sales[ 'balance' ],
                'message' => $creditNote[ 'message' ],
                'memo' => $creditNote[ 'statement_memo' ]
            ] 
        );

        Customer::find( Sales::find( $id )->customer_id )->update( [ 'balance' => Customer::find( Sales::find( $id )->customer_id )->balance - $totalPayment ] );
    }
}
