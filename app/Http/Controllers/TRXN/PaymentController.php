<?php

namespace App\Http\Controllers\TRXN;

use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;

use DB;

use App\Models\Sales;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Attachment;
use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\MapInvoicePayment;
use App\Models\MapSalesAttachment;
use App\Models\MapCreditNotePayment;

use App\Helper\RestResponseMessages;

class PaymentController extends TRXNController
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
        $sales          =   $GLOBALS[ 'input' ][ 'transaction' ];
        $payment        =   $GLOBALS[ 'input' ][ 'payment' ];

        $sales[ 'customer_id' ] = $this->customer[ $sales[ 'customer' ] ]->id;

        $invoices = $sales[ 'customerInvoices' ];
        $invoiceSum = 0;
        foreach ( $invoices as $invoice ) {
            if ( isset( $invoice[ 'checked' ] ) && $invoice[ 'checked' ] == true && $invoice[ 'amount' ] != 0 ) {
                $invoiceSum += $invoice[ 'amount' ];
                $status = $invoice[ 'balance' ] > $invoice[ 'amount' ] ?  'Partial' : 'Paid';

                $this->createAuditLog(
                    [
                        'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                        'record_id' => $invoice[ 'id' ],
                        'trxn_id' => Sales::find( $invoice[ 'id' ] )->invoice_receipt_no,
                        'date_changed' => date( 'Y-m-d H:i:s' ), 
                        'user_email' => \Auth::user()->email, 
                        'event_text' => 'Edited ',
                        'target_name' => 'Invoice',
                        'person_id' => Sales::find( $invoice[ 'id' ] )->customer_id,
                        'person_type' => $this->personTypes[ 'Customer' ],
                        'date' => $sales[ 'date' ],
                        'amount' => Sales::find( $invoice[ 'id' ] )->total,
                        'open_balance' => Sales::find( $invoice[ 'id' ] )->balance,
                        'items' => InvoiceItem::where( 'invoice_id', Invoice::where( 'sales_id', $invoice[ 'id' ] )->first()->id )->get()->toArray(),
                        'is_indirect' => 1
                    ]
                );
                Sales::find( $invoice[ 'id' ] )->update( [ 'status' => $this->statuses[ 'Invoice' ][ $status ], 'balance' => max( $invoice[ 'balance' ] - $invoice[ 'amount' ], 0 ) ] );
            }
        }

        $creditNotes = $sales[ 'creditNotes' ];
        $creditNoteSum = 0;
        foreach ( $creditNotes as $creditNote ) {
            if ( isset( $creditNote[ 'checked' ] ) && $creditNote[ 'checked' ] == true && $creditNote[ 'amount' ] != 0 ) {
                $creditNoteSum += $creditNote[ 'amount' ];
                $status = $creditNote[ 'balance' ] > $creditNote[ 'amount' ] ? 'Partial' : 'Closed';

                $this->createAuditLog(
                    [
                        'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                        'record_id' => $creditNote[ 'id' ],
                        'trxn_id' => Sales::find( $creditNote[ 'id' ] )->invoice_receipt_no,
                        'date_changed' => date( 'Y-m-d H:i:s' ), 
                        'user_email' => \Auth::user()->email, 
                        'event_text' => 'Edited ',
                        'target_name' => 'Credit Note',
                        'person_id' => Sales::find( $creditNote[ 'id' ] )->customer_id,
                        'person_type' => $this->personTypes[ 'Customer' ],
                        'date' => $sales[ 'date' ],
                        'amount' => Sales::find( $creditNote[ 'id' ] )->total,
                        'open_balance' => Sales::find( $creditNote[ 'id' ] )->balance,
                        'items' => CreditNoteItem::where( 'sales_id', $creditNote[ 'id' ] )->get()->toArray(),
                        'is_indirect' => 1
                    ]
                );
                Sales::find( $creditNote[ 'id' ] )->update( [ 'status' => $this->statuses[ 'Credit Note' ][ $status ], 'balance' => max( $creditNote[ 'balance' ] - $creditNote[ 'amount' ], 0 ) ] );
            }
        }

        $sales[ 'balance' ] = max( $sales[ 'total' ] - $invoiceSum + $creditNoteSum, 0 );
        $status = $invoiceSum - $creditNoteSum >= $sales[ 'total' ] ? 'Closed' : ( $invoiceSum - $creditNoteSum == 0 ? 'Unapplied' : 'Partial' );
        $sales[ 'status' ] = $this->statuses[ 'Payment' ][ $status ];
        $sales = Sales::create($sales);

        $payment[ 'account_id' ] = $this->account[ $payment[ 'account' ] ]->id;
        $payment[ 'sales_id' ] = $sales->id;
        $payment = Payment::create( $payment );

        $mapInvoicePayment = [];
        $mapInvoicePayment[ 'payment_id' ] = $payment->id;
        foreach ( $invoices as $invoice ) {
            if ( isset( $invoice[ 'checked' ] ) && $invoice[ 'checked' ] == true && $invoice[ 'amount' ] != 0 ) {
                $mapInvoicePayment[ 'invoice_id' ] = Invoice::where( 'sales_id', $invoice[ 'id' ] )->first()->id;
                $mapInvoicePayment[ 'payment' ] = $invoice[ 'amount' ];
                MapInvoicePayment::create( $mapInvoicePayment );
            }
        }

        $mapCreditNotePayment = [];
        $mapCreditNotePayment[ 'payment_id' ] = $payment->id;
        foreach( $creditNotes as $creditNote ) {
            if ( isset( $creditNote[ 'checked' ] ) && $creditNote[ 'checked' ] == true && $creditNote[ 'amount' ] != 0 ) {
                $mapCreditNotePayment[ 'credit_note_id' ] = CreditNote::where( 'sales_id', $creditNote[ 'id' ] )->first()->id;
                $mapCreditNotePayment[ 'payment' ] = $creditNote[ 'amount' ];
                MapCreditNotePayment::create( $mapCreditNotePayment );
            }
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

        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales[ 'total' ] ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $payment[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Added ',
                'target_name' => 'Payment',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'open_balance' => $sales[ 'balance' ],
                'message' => $payment[ 'note' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage('Create Payment', $sales, 200);
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
        $payment        =   Payment::where( 'sales_id', $sales->id )->first();

        $sales[ 'customerInvoices' ] = MapInvoicePayment::where( 'payment_id', $payment->id )->join( 'invoice', 'map_invoice_payment.invoice_id', '=', 'invoice.id' )
                                            ->join( 'sales', 'sales.id', '=', 'invoice.sales_id' )
                                            ->select( 'sales.id', 'sales.date', 'sales.invoice_receipt_no', 'sales.total', 'sales.balance', 'map_invoice_payment.payment', DB::raw( '1 as checked' ) )
                                            ->where( 'is_trash', '!=', 1 )
                                            ->union( 
                                                Sales::where( 'transaction_type', $this->transactionTypes[ 'Invoice' ] )
                                                        ->where( 'customer_id', $sales->customer_id )->where( 'status', $this->statuses[ 'Invoice' ][ 'Unpaid' ] )
                                                        ->where( 'is_trash', '!=', 1 )
                                                        ->select( 'sales.id', 'sales.date', 'sales.invoice_receipt_no', 'sales.total', 'sales.balance', DB::raw( '0 as payment' ), DB::raw( '0 as checked' ) )
                                            )->get();
        $sales[ 'creditNotes' ] = MapCreditNotePayment::where( 'payment_id', $payment->id )->join( 'credit_note', 'map_credit_note_payment.credit_note_id', '=', 'credit_note.id' )
                                            ->join( 'sales', 'sales.id', '=', 'credit_note.sales_id' )
                                            ->select( 'sales.id', 'sales.date', 'sales.invoice_receipt_no', 'sales.total', 'sales.balance', 'map_credit_note_payment.payment', DB::raw( '1 as checked' ) )
                                            ->where( 'sales.is_trash', '!=', 1 )
                                            ->union(
                                                Sales::where( 'transaction_type', $this->transactionTypes[ 'Credit Note' ] )
                                                        ->where( 'customer_id', $sales->customer_id )->where( 'status', $this->statuses[ 'Credit Note' ][ 'Unapplied' ] )
                                                        ->where( 'is_trash', '!=', 1 )
                                                        ->select( 'sales.id', 'sales.date', 'sales.invoice_receipt_no', 'sales.total', 'sales.balance', DB::raw( '0 as payment' ), DB::raw( '0 as checked' ) )
                                            )->get();

        $sales->customer = Customer::find( $sales->customer_id )->name;
        $payment->account = Account::find( $payment->account_id )->name;

        return RestResponseMessages::TRXNSuccessMessage( 'Get Payment', [ 'transaction' => $sales, 'payment' => $payment ], 200 );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit( $id )
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
        $payment        =   $GLOBALS[ 'input' ][ 'payment' ];

        $sales[ 'customer_id' ] = $this->customer[ $sales[ 'customer' ] ]->id;
        
        $invoices = $sales[ 'customerInvoices' ];
        foreach ( $invoices as $invoice ) {
            $status = $invoice[ 'balance' ] == Sales::find( $invoice[ 'id' ] )->total ? 'Unpaid' : 'Partial';
            Sales::where( 'invoice_receipt_no', $invoice[ 'invoice_receipt_no' ] )->update( [ 'balance' => $invoice[ 'balance' ], 'status' => $this->statuses[ 'Invoice' ][ $status ] ] );
        }

        $creditNotes = $sales[ 'creditNotes' ];
        foreach ( $creditNotes as $creditNote ) {
            $status = $creditNote[ 'balance' ] == Sales::find( $creditNote[ 'id' ] )->total ? 'Unapplied' : 'Partial';
            Sales::where( 'invoice_receipt_no', $creditNote[ 'invoice_receipt_no' ] )->update( [ 'balance' => $creditNote[ 'balance' ], 'status' => $this->statuses[ 'Credit Note' ][ $status ] ] );
        }

        $invoices = $sales[ 'customerInvoices' ];
        $invoiceSum = 0;
        foreach ( $invoices as $invoice ) {
            if ( isset( $invoice[ 'checked' ] ) && $invoice[ 'checked' ] == true ) {
                $invoiceSum += $invoice[ 'amount' ];
                $status = $invoice[ 'balance' ] <=  $invoice[ 'amount' ] ? 'Paid' : ( $invoice [ 'amount' ] == 0 ? 'Unpaid' : 'Partial' );

                if ( Sales::find( $invoice[ 'id' ] )->balance != max( $invoice[ 'balance' ] - $invoice[ 'amount' ], 0 ) ) {
                    $this->createAuditLog(
                        [
                            'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                            'record_id' => $invoice[ 'id' ],
                            'trxn_id' => Sales::find( $invoice[ 'id' ] )->invoice_receipt_no,
                            'date_changed' => date( 'Y-m-d H:i:s' ), 
                            'user_email' => \Auth::user()->email, 
                            'event_text' => 'Edited ',
                            'target_name' => 'Credit Note',
                            'person_id' => Sales::find( $invoice[ 'id' ] )->customer_id,
                            'person_type' => $this->personTypes[ 'Customer' ],
                            'date' => $sales[ 'date' ],
                            'amount' => Sales::find( $invoice[ 'id' ] )->total,
                            'open_balance' => Sales::find( $invoice[ 'id' ] )->balance,
                            'items' => InvoiceItem::where( 'invoice_id', Invoice::where( 'sales_id', $invoice[ 'id' ] )->first()->id )->get()->toArray(),
                            'is_indirect' => 1
                        ]
                    );
                }
                Sales::find( $invoice[ 'id' ] )->update( [ 'status' => $this->statuses[ 'Invoice' ][ $status ], 'balance' => max( $invoice[ 'balance' ] - $invoice[ 'amount' ], 0 ) ] );
            }
        }

        $creditNotes = $sales[ 'creditNotes' ];
        $creditNoteSum = 0;
        foreach ( $creditNotes as $creditNote ) {
            if ( isset( $creditNote[ 'checked' ] ) && $creditNote[ 'checked' ] == true && $creditNote[ 'amount' ] != 0 ) {
                $creditNoteSum += $creditNote[ 'amount' ];
                $status = $creditNote[ 'balance' ] <= $creditNote[ 'amount' ] ? 'Closed' : ( $creditNote[ 'amount' ] == 0 ? 'Unapplied' : 'Partial' );

                if ( Sales::find( $creditNote[ 'id' ] )->balance !=  max( $creditNote[ 'balance' ] - $creditNote[ 'amount' ], 0 ) ) {
                    $this->createAuditLog(
                        [
                            'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                            'record_id' => $creditNote[ 'id' ],
                            'trxn_id' => Sales::find( $creditNote[ 'id' ] )->invoice_receipt_no,
                            'date_changed' => date( 'Y-m-d H:i:s' ), 
                            'user_email' => \Auth::user()->email, 
                            'event_text' => 'Edited ',
                            'target_name' => 'Credit Note',
                            'person_id' => Sales::find( $creditNote[ 'id' ] )->customer_id,
                            'person_type' => $this->personTypes[ 'Customer' ],
                            'date' => $sales[ 'date' ],
                            'amount' => Sales::find( $creditNote[ 'id' ] )->total,
                            'open_balance' => Sales::find( $creditNote[ 'id' ] )->balance,
                            'items' => CreditNoteItem::where( 'sales_id', $creditNote[ 'id' ] )->get()->toArray(),
                            'is_indirect' => 1
                        ]
                    );
                }
                Sales::find( $creditNote[ 'id' ] )->update( [ 'status' => $this->statuses[ 'Credit Note' ][ $status ], 'balance' => max( $creditNote[ 'balance' ] - $creditNote[ 'amount' ], 0 ) ] );
            }
        }

        $sales[ 'balance' ] = max( $sales[ 'total' ] - $invoiceSum + $creditNoteSum, 0 );
        $status = $invoiceSum - $creditNoteSum >= $sales[ 'total' ] ? 'Closed' : ( $invoiceSum - $creditNoteSum == 0 ? 'Unapplied' : 'Partial' );
        $sales[ 'status' ] = $this->statuses[ 'Payment' ][ $status ];
        $orgSales = Sales::find( $sales[ 'id' ] );
        Sales::find( $sales[ 'id' ] )->update( $sales );

        $payment[ 'account_id' ] = $this->account[ $payment[ 'account' ] ]->id;
        $payment[ 'sales_id' ] = $sales[ 'id' ];
        Payment::find( $payment[ 'id' ] )->update( $payment );

        $mapInvoicePayment = [];
        $mapInvoicePayment[ 'payment_id' ] = $payment[ 'id' ];
        foreach ( $invoices as $invoice ) {
            if ( isset( $invoice[ 'checked' ] ) ) {
                if ( $invoice[ 'checked' ] == true ) {
                    $mapInvoicePayment[ 'invoice_id' ] = Invoice::where( 'sales_id', $invoice[ 'id' ] )->first()->id;
                    $mapInvoicePayment[ 'payment' ] = $invoice[ 'amount' ];

                    $orgMap = MapInvoicePayment::where( 'invoice_id', $mapInvoicePayment[ 'invoice_id' ] )->where( 'payment_id', $payment[ 'id' ] )->first();
                    if ( $orgMap ) {
                        if ( (int)$invoice [ 'amount' ] > 0 )
                            $orgMap->update( $mapInvoicePayment );
                        else
                            MapInvoicePayment::find( $orgMap->id )->delete();
                    }
                    else
                        MapInvoicePayment::create( $mapInvoicePayment );
                } else {
                    $map = MapInvoicePayment::where( 'invoice_id', Invoice::where( 'sales_id', $invoice[ 'id' ] )->first()->id )->where( 'payment_id', $payment[ 'id' ] )->first();
                    if ( $map )
                        $map->delete();
                }
            }
        }

        $mapCreditNotePayment = [];
        $mapCreditNotePayment[ 'payment_id' ] = $payment[ 'id' ];
        foreach( $creditNotes as $creditNote ) {
            if ( isset( $creditNote[ 'checked' ] ) ) {
                if ( $creditNote[ 'checked' ] == true ) {
                    $mapCreditNotePayment[ 'credit_note_id' ] = CreditNote::where( 'sales_id', $creditNote[ 'id' ] )->first()->id;
                    $mapCreditNotePayment[ 'payment' ] = $creditNote[ 'amount' ];

                    $orgMap = MapCreditNotePayment::where( 'credit_note_id', $mapCreditNotePayment[ 'credit_note_id' ] )->where( 'payment_id', $payment[ 'id' ] )->first();
                    if ( $orgMap ) {
                        if ( (int)$creditNote[ 'amount' ] > 0  )
                            $orgMap->update( $mapCreditNotePayment );
                        else
                            MapCreditNotePayment::find( $orgMap->id )->delete();
                    }
                    else
                        MapCreditNotePayment::create( $mapCreditNotePayment );
                } else {
                    $map = MapCreditNotePayment::where( 'credit_note_id', CreditNote::where( 'sales_id', $creditNote[ 'id' ] )->first()->id )->where( 'payment_id', $payment[ 'id' ] )->first();
                    if ( $map )
                        $map->delete();
                }
            }
        }

        if ( isset( $GLOBALS[ 'input' ][ 'attachments' ] ) ) {
            $attachments    =   $GLOBALS[ 'input' ][ 'attachments' ];
            foreach ( $attachments as $attachment ) {
                $attachment = Attachment::create( $attachment );
                MapSalesAttachment::create( [ 'sales_id' => $sales->id, 'attachment_id' => $attachment->id ] );
            }
        }

        Customer::find( $sales[ 'customer_id' ] )->update( [ 'balance' => Customer::find( $sales[ 'customer_id' ] )->balance - $sales[ 'total' ] + $orgSales[ 'total' ] ] );

        $accountId = $this->account[ 'Accounts Receivable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales[ 'total' ] + $orgSales[ 'total' ] ] );

        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales[ 'total' ] - $orgSales[ 'total' ] ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $payment[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Edited ',
                'target_name' => 'Payment',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'open_balance' => $sales[ 'balance' ],
                'message' => $payment[ 'note' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage('Update Payment', $sales, 200);
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

        $payment = Payment::where( 'sales_id', $sales->id )->first();

        Customer::find( $sales->customer_id )->update( [ 'balance' => Customer::find( $sales->customer_id )->balance + $sales->balance ] );

        $accountId = $this->account[ 'Accounts Receivable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales->total ] );

        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales->total ] );

        $totalPayment = 0;
        $mapInvoicePayment = MapInvoicePayment::join( 'payment', 'map_invoice_payment.payment_id', '=', 'payment.id' )->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
            ->where( 'sales.id', $sales->id )->get();

        foreach ( $mapInvoicePayment as $map ) {
            $sales = MapInvoicePayment::join( 'invoice', 'map_invoice_payment.invoice_id', '=', 'invoice.id' )->join( 'sales', 'invoice.sales_id', '=', 'sales.id' )
                ->where( 'invoice_id', $map->invoice_id )->first();
            $balance = Sales::find( $sales->id )->balance + $map->payment;
            $status = $balance == $sales->total ? $this->statuses[ 'Invoice' ][ 'Unpaid' ] : $this->statuses[ 'Invoice' ][ 'Partial' ];

            Sales::find( $sales->id )->update( [ 'balance' => $balance, 'status' => $status ] );
            $totalPayment += $map->payment;
        }

        $sales = Sales::find( $id );
        $mapCreditNotePayment = MapCreditNotePayment::join( 'payment', 'map_credit_note_payment.payment_id', '=', 'payment.id' )->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
            ->where( 'sales.id', $sales->id )->get();

        foreach ( $mapCreditNotePayment as $map ) {
            $sales = MapCreditNotePayment::join( 'credit_note', 'map_credit_note_payment.credit_note_id', '=', 'credit_note.id' )->join( 'sales', 'credit_note.sales_id', '=', 'sales.id' )
                ->where( 'credit_note_id', $map->credit_note_id )->first();
            $balance = Sales::find( $sales->id )->balance + $map->payment;
            $status = $balance == $sales->total ? $this->statuses[ 'Credit Note' ][ 'Unapplied' ] : $this->statuses[ 'Credit Note' ][ 'Partial' ];

            Sales::find( $sales->id )->update( [ 'balance' => $balance, 'status' => $status ] );
            $totalPayment -= $map->payment;
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $payment[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Deleted ',
                'target_name' => 'Payment',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'open_balance' => $sales[ 'balance' ],
                'message' => $payment[ 'note' ]
            ] 
        );

        Customer::find( $sales->customer_id )->update( [ 'balance' => Customer::find( $sales->customer_id )->balance + $totalPayment ] );
    }

    public function recoverDelete( Request $request, $id ) {

        $sales = Sales::find( $id );
        $sales->update( [ 'is_trash' => 0 ] );

        $payment = Payment::where( 'sales_id', $sales->id )->first();

        Customer::find( $sales->customer_id )->update( [ 'balance' => Customer::find( $sales->customer_id )->balance - $sales->balance ] );

        $accountId = $this->account[ 'Accounts Receivable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $sales->total ] );

        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $sales->total ] );

        $mapInvoicePayment = MapInvoicePayment::join( 'payment', 'map_invoice_payment.payment_id', '=', 'payment.id' )->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
            ->where( 'sales.id', $sales->id )->get();

        $totalPayment = 0;
        foreach ( $mapInvoicePayment as $map ) {
            $sales = MapInvoicePayment::join( 'invoice', 'map_invoice_payment.invoice_id', '=', 'invoice.id' )->join( 'sales', 'invoice.sales_id', '=', 'sales.id' )
                ->where( 'invoice_id', $map->invoice_id )->first();
            $balance = Sales::find( $sales->id )->balance - $map->payment;
            $status = $balance == $sales->total ? $this->statuses[ 'Invoice' ][ 'Unpaid' ] : ( $balance == 0 ? $this->statuses[ 'Invoice' ][ 'Paid' ] : $this->statuses[ 'Invoice' ][ 'Partial' ] );

            Sales::find( $sales->id )->update( [ 'balance' => $balance, 'status' => $status ] );
            $totalPayment += $map->payment;
        }

        $sales = Sales::find( $id );
        $mapCreditNotePayment = MapCreditNotePayment::join( 'payment', 'map_credit_note_payment.payment_id', '=', 'payment.id' )->join( 'sales', 'payment.sales_id', '=', 'sales.id' )
            ->where( 'sales.id', $sales->id )->get();

        foreach( $mapCreditNotePayment as $map) {
            $sales = MapCreditNotePayment::join( 'credit_note', 'map_credit_note_payment.credit_note_id', '=', 'credit_note.id' )->join( 'sales', 'credit_note.sales_id', '=', 'sales.id' )
                ->where( 'credit_note_id', $map->credit_note_id )->first();
            $balance = Sales::find( $sales->id )->balance - $map->payment;
            $status = $balance == $sales->total ? $this->statuses[ 'Credit Note' ][ 'Unapplied' ] : ( $balance == 0 ? $this->statuses[ 'Credit Note' ][ 'Closed' ] : $this->statuses[ 'Credit Note' ][ 'Partial' ] );

            Sales::find( $sales->id )->update( [ 'balance' => $balance, 'status' => $status ] );
            $totalPayment -= $map->payment;
        }
        Customer::find( $sales->customer_id )->update( [ 'balance' => Customer::find( $sales->customer_id )->balance - $totalPayment ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Sales' ], 
                'record_id' => $sales[ 'id' ],
                'trxn_id' => $payment[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Recovered ',
                'target_name' => 'Payment',
                'person_id' => $sales[ 'customer_id' ],
                'person_type' => $this->personTypes[ 'Customer' ],
                'date' => $sales[ 'date' ],
                'amount' => $sales[ 'total' ],
                'open_balance' => $sales[ 'balance' ],
                'message' => $payment[ 'note' ]
            ] 
        );
    }
}
