<?php

namespace App\Http\Controllers\TRXN;

use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;

use DB;
use App\Models\Bill;
use App\Models\Account;
use App\Models\Expenses;
use App\Models\Supplier;
use App\Models\Attachment;
use App\Models\BillItem;
use App\Models\BillAccount;
use App\Models\BillPayment;
use App\Models\SupplierCredit;
use App\Models\SupplierCreditItem;
use App\Models\SupplierCreditAccount;
use App\Models\MapBillBillPayment;
use App\Models\MapExpensesAttachment;
use App\Models\MapSupplierCreditBillPayment;

use App\Helper\RestResponseMessages;

class BillPaymentController extends TRXNController
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
    public function store(Request $request)
    {
        $expenses          =   $GLOBALS[ 'input' ][ 'transaction' ];
        $billPayment       =   $GLOBALS[ 'input' ][ 'billPayment' ];

        $billSum = 0;

        $bills = $expenses[ 'supplierBills' ];
        foreach ( $bills as $bill ) {
            if ( isset( $bill[ 'checked' ] ) && $bill[ 'checked' ] == true ) {
                $billSum += $bill[ 'amount' ];
                $status = $bill[ 'balance' ] > $bill[ 'amount' ] ?  'Partial' : 'Paid';

                Expenses::find( $bill[ 'id' ] )->update( [ 'status' => $this->statuses[ 'Bill' ][ $status ], 'balance' => max( $bill[ 'balance' ] - $bill[ 'amount' ], 0 ) ] );

                $billId = Bill::where( 'expenses_id', $bill[ 'id' ] )->first()->id;
                $itemDetails = BillItem::where( 'bill_id', $billId )->get()->toArray();
                $accountDetails = BillAccount::where( 'bill_id', $billId )->get()->toArray();
                $items = array_merge( $itemDetails, $accountDetails );
                $this->createAuditLog(
                    [
                        'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                        'record_id' => $bill[ 'id' ],
                        'trxn_id' => Bill::where( 'expenses_id', $bill[ 'id' ] )->first()->id,
                        'date_changed' => date( 'Y-m-d H:i:s' ), 
                        'user_email' => \Auth::user()->email, 
                        'event_text' => 'Edited ',
                        'target_name' => 'Bill',
                        'person_id' => $this->supplier[ $expenses[ 'supplier' ] ]->id,
                        'person_type' => $this->personTypes[ 'Supplier' ],
                        'date' => $expenses[ 'date' ],
                        'amount' => Expenses::find( $bill[ 'id' ] )->total,
                        'open_balance' => max( $bill[ 'balance' ] - $bill[ 'amount' ], 0 ),
                        'items' => $items,
                        'is_indirect' => 1
                    ] 
                );
            }
        }

        $supplierCredits = $expenses[ 'supplierCredits' ];
        foreach ( $supplierCredits as $supplierCredit ) {
            if ( isset( $supplierCredit[ 'checked' ] ) && $supplierCredit[ 'checked' ] == true ) {
                $billSum -= $supplierCredit[ 'amount' ];
                $status = $supplierCredit[ 'balance' ] > $supplierCredit[ 'amount' ] ? 'Partial' : 'Closed';
                Expenses::find( $supplierCredit[ 'id' ] )->update( [ 'status' => $this->statuses[ 'Supplier Credit' ][ $status ], 'balance' => max( $supplierCredit[ 'balance' ] - $supplierCredit[ 'amount' ], 0 ) ] );

                $supplierCreditId = SupplierCredit::where( 'expenses_id', $supplierCredit[ 'id' ] )->first()->id;
                $itemDetails = SupplierCreditItem::where( 'supplier_credit_id', $supplierCreditId )->get()->toArray();
                $accountDetails = SupplierCreditAccount::where( 'supplier_credit_id', $supplierCreditId )->get()->toArray();
                $items = array_merge( $itemDetails, $accountDetails );
                $this->createAuditLog(
                    [
                        'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                        'record_id' => $supplierCredit[ 'id' ],
                        'trxn_id' => SupplierCredit::where( 'expenses_id', $supplierCredit[ 'id' ] )->first()->id,
                        'date_changed' => date( 'Y-m-d H:i:s' ), 
                        'user_email' => \Auth::user()->email, 
                        'event_text' => 'Edited ',
                        'target_name' => 'Supplier Credit',
                        'person_id' => $this->supplier[ $expenses[ 'supplier' ] ]->id,
                        'person_type' => $this->personTypes[ 'Supplier' ],
                        'date' => $expenses[ 'date' ],
                        'amount' => Expenses::find( $supplierCredit[ 'id' ] )->total,
                        'open_balance' => max( $supplierCredit[ 'balance' ] - $supplierCredit[ 'amount' ], 0 ),
                        'items' => $items,
                        'is_indirect' => 1
                    ] 
                );
            }
        }

        $expenses[ 'payee_id' ] = $this->supplier[ $expenses[ 'supplier' ] ]->id;
        $expenses[ 'payee_type' ] = $this->personTypes[ 'Supplier' ];

        $expenses[ 'balance' ] = max( $expenses[ 'total' ] - $billSum, 0 );
        $status = $billSum >= $expenses[ 'total' ] ? 'Closed' : ( $billSum == 0 ? 'Unapplied' : 'Partial' );
        $expenses[ 'status' ] = $this->statuses[ 'Bill Payment' ][ $status ];
        $expenses = Expenses::create( $expenses );

        $billPayment[ 'account_id' ] = $this->account[ $billPayment[ 'account' ] ]->id;
        $billPayment[ 'expenses_id' ] = $expenses->id;
        $billPayment = BillPayment::create( $billPayment );

        $mapBillBillPayment = [ 'bill_payment_id' => $billPayment->id ];
        $mapBillBillPayment[ 'bill_payment_id' ] = $billPayment->id;
        foreach ( $bills as $bill ) {
            if ( isset( $bill[ 'checked' ] ) && $bill[ 'checked' ] == true ) {
                $mapBillBillPayment[ 'bill_id' ] = Bill::where( 'expenses_id', $bill[ 'id' ] )->first()->id;
                $mapBillBillPayment[ 'payment' ] = $bill[ 'amount' ];
                if ( $bill[ 'amount' ] > 0 )
                    MapBillBillPayment::create( $mapBillBillPayment );
            }
        }

        $mapSupplierCreditBillPayment = [ 'bill_payment_id' => $billPayment->id ];
        foreach ( $supplierCredits as $supplierCredit ) {
            if ( isset( $supplierCredit[ 'checked' ] ) && $supplierCredit[ 'checked' ] == true ) {
                $mapSupplierCreditBillPayment[ 'supplier_credit_id' ] = SupplierCredit::where( 'expenses_id', $supplierCredit[ 'id' ] )->first()->id;
                $mapSupplierCreditBillPayment[ 'payment' ] = $supplierCredit[ 'amount' ];
                if ( $supplierCredit[ 'amount' ] > 0 )
                    MapSupplierCreditBillPayment::create( $mapSupplierCreditBillPayment );
            }
        }


        if ( isset( $GLOBALS[ 'input' ][ 'attachments' ] ) ) {
            $attachments    =   $GLOBALS[ 'input' ][ 'attachments' ];
            foreach ( $attachments as $attachment ) {
                $attachment = Attachment::create( $attachment );
                MapExpensesAttachment::create( [ 'expenses_id' => $expenses->id, 'attachment_id' => $attachment->id ] );
            }
        }

        $accountId = $this->account[ 'Accounts Payable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses[ 'total' ] ] );

        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses[ 'total' ] ] );

        Supplier::find( $expenses[ 'payee_id' ] )->update( [ 'balance' => Supplier::find( $expenses[ 'payee_id' ] )->balance - $expenses[ 'total' ] ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $billPayment[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Added ',
                'target_name' => 'Bill Payment',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'open_balance' => $expenses[ 'balance' ],
                'message' => $billPayment[ 'note' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage('Create Bill Payment', $expenses, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $expenses          =   Expenses::find( $id );
        $billPayment       =   BillPayment::where( 'expenses_id', $expenses->id )->first();

        $expenses[ 'supplierBills' ] = MapBillBillPayment::where( 'bill_payment_id', $billPayment->id )->join( 'bill', 'map_bill_bill_payment.bill_id', '=', 'bill.id' )
                                            ->join( 'expenses', 'expenses.id', '=', 'bill.expenses_id' )
                                            ->select( 'expenses.id', 'expenses.payee_id', 'expenses.date', 'expenses.total', 'expenses.balance', 'map_bill_bill_payment.payment', DB::raw( '1 as checked' ) )
                                            ->where( 'is_trash', '!=', 1 )
                                            ->union( 
                                                Expenses::where( 'transaction_type', $this->transactionTypes[ 'Bill' ] )
                                                            ->where( 'payee_id', $expenses->payee_id )
                                                            ->where( 'status', $this->statuses[ 'Bill' ][ 'Unpaid' ] )
                                                            ->select( 'expenses.id', 'expenses.payee_id', 'expenses.date', 'expenses.total', 'expenses.balance', DB::raw( '0 as payment' ), DB::raw( '0 as checked' ) )
                                                            ->where( 'is_trash', '!=', 1 )
                                            )->get();

        $expenses[ 'supplierCredits' ] = MapSupplierCreditBillPayment::where( 'bill_payment_id', $billPayment->id )->join( 'supplier_credit', 'map_supplier_credit_bill_payment.supplier_credit_id', '=', 'supplier_credit.id')
                                            ->join( 'expenses', 'expenses.id', '=', 'supplier_credit.expenses_id' )
                                            ->select( 'expenses.id', 'expenses.payee_id', 'expenses.date', 'expenses.total', 'expenses.balance', 'map_supplier_credit_bill_payment.payment',  DB::raw( '1 as checked' ) )
                                            ->where( 'is_trash', '!=', 1 )
                                            ->union(
                                                Expenses::where( 'transaction_type', $this->transactionTypes[ 'Supplier Credit' ] )
                                                            ->where( 'payee_id', $expenses->payee_id ) 
                                                            ->where( 'status', $this->statuses[ 'Supplier Credit'][ 'Unapplied' ] )
                                                            ->select( 'expenses.id', 'expenses.payee_id', 'expenses.date', 'expenses.total', 'expenses.balance', DB::raw( '0 as payment' ), DB::raw( '0 as checked' ) )
                                                            ->where( 'is_trash', '!=', 1 )
                                            )->get();

        $expenses->supplier = Supplier::find( $expenses->payee_id )->name;
        $billPayment->account = Account::find( $billPayment->account_id )->name;

        return RestResponseMessages::TRXNSuccessMessage( 'Get Bill Payment', [ 'transaction' => $expenses, 'billPayment' => $billPayment ], 200 );
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
    public function update(Request $request, $id)
    {
        $expenses          =   $GLOBALS[ 'input' ][ 'transaction' ];
        $billPayment       =   $GLOBALS[ 'input' ][ 'billPayment' ];

        $bills = $expenses[ 'supplierBills' ];
        foreach ( $bills as $bill ) {
            $billId = Bill::where( 'expenses_id', $bill[ 'id' ] )->first()->id;
            $status = $bill[ 'balance' ] == Expenses::find( $bill[ 'id' ] )->total ? 'Unpaid' : 'Partial';
            Expenses::find( $bill[ 'id' ] )->update( [ 'balance' => $bill[ 'balance' ], 'status' => $this->statuses[ 'Bill' ][ $status ] ] );
        }

        $supplierCredits = $expenses[ 'supplierCredits' ];
        foreach ( $supplierCredits as $supplierCredit ) {
            $supplierCreditId = SupplierCredit::where( 'expenses_id', $supplierCredit[ 'id' ] )->first()->id;
            $status = $supplierCredit[ 'balance' ] == Expenses::find( $supplierCredit[ 'id' ] )->total ? 'Unapplied' : 'Partial';
            Expenses::find( $supplierCredit[ 'id' ] )->update( [ 'balance' => $supplierCredit[ 'balance' ], 'status' => $this->statuses[ 'Supplier Credit' ][ $status ] ] );
        }

        $billSum = 0;
        foreach ( $bills as $bill ) {
            if ( isset( $bill[ 'checked' ] ) && $bill[ 'checked' ] == true ) {
                $billSum += $bill[ 'amount' ];
                $status = $bill [ 'balance' ] <= $bill[ 'amount' ] ?  'Paid' : ( $bill[ 'amount' ] == 0 ? 'Unpaid' : 'Partial' );

                if ( Expenses::find( $bill[ 'id' ] )->balance != max( $bill[ 'balance' ] - $bill[ 'amount' ], 0 ) ) {
                    $billId = Bill::where( 'expenses_id', $bill[ 'id' ] )->first()->id;
                    $itemDetails = BillItem::where( 'bill_id', $billId )->get()->toArray();
                    $accountDetails = BillAccount::where( 'bill_id', $billId )->get()->toArray();
                    $items = array_merge( $itemDetails, $accountDetails );
                    $this->createAuditLog(
                        [
                            'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                            'record_id' => $bill[ 'id' ],
                            'trxn_id' => Bill::where( 'expenses_id', $bill[ 'id' ] )->first()->id,
                            'date_changed' => date( 'Y-m-d H:i:s' ), 
                            'user_email' => \Auth::user()->email, 
                            'event_text' => 'Edited ',
                            'target_name' => 'Bill',
                            'person_id' => $expenses[ 'payee_id' ],
                            'person_type' => $expenses[ 'payee_type' ],
                            'date' => $expenses[ 'date' ],
                            'amount' => Expenses::find( $bill[ 'id' ] )->total,
                            'open_balance' => max( $bill[ 'balance' ] - $bill[ 'amount' ], 0 ),
                            'items' => $items,
                            'is_indirect' => 1
                        ] 
                    );
                }
                Expenses::find( $bill[ 'id' ] )->update( [ 'status' => $this->statuses[ 'Bill' ][ $status ], 'balance' => max( $bill[ 'balance' ] - $bill[ 'amount' ], 0 ) ] );
            } 
        }

        foreach ( $supplierCredits as $supplierCredit ) {
            if ( isset( $supplierCredit[ 'checked' ] ) && $supplierCredit[ 'checked' ] == true ) {
                $billSum -= $supplierCredit[ 'amount' ];
                $status = $supplierCredit[ 'balance' ] <= $supplierCredit[ 'amount' ] ? 'Closed' : ( $supplierCredit[ 'amount' ] == 0 ? 'Unapplied' : 'Partial' );

                if ( Expenses::find( $supplierCredit[ 'id' ] )->balance != max( $supplierCredit[ 'balance' ] - $supplierCredit[ 'amount' ], 0 ) ) {
                    $supplierCreditId = SupplierCredit::where( 'expenses_id', $supplierCredit[ 'id' ] )->first()->id;
                    $itemDetails = SupplierCreditItem::where( 'supplier_credit_id', $supplierCreditId )->get()->toArray();
                    $accountDetails = SupplierCreditAccount::where( 'supplier_credit_id', $supplierCreditId )->get()->toArray();
                    $items = array_merge( $itemDetails, $accountDetails );
                    $this->createAuditLog(
                        [
                            'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                            'record_id' => $supplierCredit[ 'id' ],
                            'trxn_id' => SupplierCredit::where( 'expenses_id', $supplierCredit[ 'id' ] )->first()->id,
                            'date_changed' => date( 'Y-m-d H:i:s' ), 
                            'user_email' => \Auth::user()->email, 
                            'event_text' => 'Edited ',
                            'target_name' => 'Supplier Credit',
                            'person_id' => $expenses[ 'payee_id' ],
                            'person_type' => $expenses[ 'payee_type' ],
                            'date' => $expenses[ 'date' ],
                            'amount' => Expenses::find( $supplierCredit[ 'id' ] )->total,
                            'open_balance' => max( $supplierCredit[ 'balance' ] - $supplierCredit[ 'amount' ], 0 ),
                            'items' => $items,
                            'is_indirect' => 1
                        ] 
                    );
                }
                Expenses::find( $supplierCredit[ 'id' ] )->update( [ 'status' => $this->statuses[ 'Supplier Credit' ][ $status ], 'balance' => max( $supplierCredit[ 'balance' ] - $supplierCredit[ 'amount' ], 0 ) ] );
            }
        }

        $expenses[ 'payee_id' ] = $this->supplier[ $expenses[ 'supplier' ] ]->id;
        $expenses[ 'payee_type' ] = $this->personTypes[ 'Supplier' ];

        $expenses[ 'account_id' ] =  $this->account[ $billPayment[ 'account' ] ]->id;
        $expenses[ 'balance' ] = max( $expenses[ 'total' ] - $billSum, 0 );
        $status = $billSum >= $expenses[ 'total' ] ? 'Closed' : ( $billSum == 0 ? 'Unapplied' : 'Partial' );
        $expenses[ 'status' ] = $this->statuses[ 'Bill Payment' ][ $status ];

        $orgExpenses = Expenses::find( $expenses[ 'id' ] );
        Expenses::find( $expenses[ 'id' ] )->update( $expenses );

        $billPayment[ 'account_id' ] = $expenses[ 'account_id' ];
        $billPayment[ 'expenses_id' ] = $expenses[ 'id' ];
        BillPayment::find( $billPayment[ 'id' ] )->update( $billPayment );

        $mapBillBillPayment = [ 'bill_payment_id' => $billPayment[ 'id' ] ];
        foreach ( $bills as $bill ) {
            if ( isset( $bill[ 'checked' ] ) && $bill[ 'checked' ] == true ) {
                $mapBillBillPayment[ 'bill_id' ] = Bill::where( 'expenses_id', $bill[ 'id' ] )->first()->id;
                $mapBillBillPayment[ 'payment' ] = $bill[ 'amount' ];
                $orgMap = MapBillBillPayment::where( 'bill_id', $mapBillBillPayment[ 'bill_id' ] )->where( 'bill_payment_id', $mapBillBillPayment[ 'bill_payment_id' ] )->first();
                if ( $bill[ 'amount' ] > 0 ) {
                    if ( $orgMap )
                        MapBillBillPayment::find( $orgMap->id )->update( $mapBillBillPayment );
                    else
                        MapBillBillPayment::create( $mapBillBillPayment );
                } else {
                    if ( $orgMap )
                        MapBillBillPayment::find( $orgMap->id )->delete();
                }
            }
        }

        $mapSupplierCreditBillPayment = [ 'bill_payment_id' => $billPayment[ 'id' ] ];
        foreach ( $supplierCredits as $supplierCredit ) {
            if ( isset( $supplierCredit[ 'checked' ] ) && $supplierCredit[ 'checked' ] == true ) {
                $mapSupplierCreditBillPayment[ 'supplier_credit_id' ] = SupplierCredit::where( 'expenses_id', $supplierCredit[ 'id' ] )->first()->id;
                $mapSupplierCreditBillPayment[ 'payment' ] = $supplierCredit[ 'amount' ];
                $orgMap = MapSupplierCreditBillPayment::where( 'supplier_credit_id', $mapSupplierCreditBillPayment[ 'supplier_credit_id' ] )->where( 'bill_payment_id', $mapSupplierCreditBillPayment[ 'bill_payment_id' ] )->first();
                if ( $supplierCredit[ 'amount' ] > 0 ) {
                    if ( $orgMap )
                        MapSupplierCreditBillPayment::find( $orgMap->id )->update( $mapSupplierCreditBillPayment );
                    else
                        MapSupplierCreditBillPayment::create( $mapSupplierCreditBillPayment );
                } else {
                    if ( $orgMap )
                        MapSupplierCreditBillPayment::find( $orgMap->id )->delete();
                }
            }
        }

        if ( isset( $GLOBALS[ 'input' ][ 'attachments' ] ) ) {
            $attachments    =   $GLOBALS[ 'input' ][ 'attachments' ];
            foreach ( $attachments as $attachment ) {
                $attachment = Attachment::create( $attachment );
                MapExpensesAttachment::create( [ 'expenses_id' => $expenses->id, 'attachment_id' => $attachment->id ] );
            }
        }

        $accountId = $this->account[ 'Accounts Payable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses[ 'total' ] + $orgExpenses[ 'total' ] ] );

        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses[ 'total' ] + $orgExpenses[ 'total' ] ] );

        Supplier::find( $expenses[ 'payee_id' ] )->update( [ 'balance' => Supplier::find( $expenses[ 'payee_id' ] )->balance - $expenses[ 'total' ] + $orgExpenses[ 'total' ] ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $billPayment[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Edited ',
                'target_name' => 'Bill Payment',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'open_balance' => $expenses[ 'balance' ],
                'message' => $billPayment[ 'note' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage('Update Bill Payment', $expenses, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $expenses = Expenses::find( $id );
        $expenses->update( [ 'is_trash' => 1 ] );

        $billPayment = BillPayment::where( 'expenses_id', $expenses[ 'id' ] )->first();

        $accountId = $this->account[ 'Accounts Payable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $expenses->total ] );

        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $expenses->total ] );

        Supplier::find( $expenses[ 'payee_id' ] )->update( [ 'balance' => Supplier::find( $expenses[ 'payee_id' ] )->balance + $expenses->total ] );

        $mapBillBillPayment = MapBillBillPayment::join( 'bill_payment', 'map_bill_bill_payment.bill_payment_id', '=', 'bill_payment.id' )
            ->join( 'expenses', 'bill_payment.expenses_id', '=', 'expenses.id' )
            ->where( 'expenses.id', $expenses->id )->get();

        foreach ( $mapBillBillPayment as $map ) {
            $expenses = MapBillBillPayment::join( 'bill', 'map_bill_bill_payment.bill_id', '=', 'bill.id' )
                ->join( 'expenses', 'bill.expenses_id', '=', 'expenses.id' )
                ->where( 'bill_id', $map->bill_id )->first();
            $balance = Expenses::find( $expenses->id )->balance + $map->payment;
            $status = $balance == $expenses->total ? 'Unpaid' : 'Partial';

            Expenses::find( $expenses->id )->update( [ 'balance' => $balance, 'status' => $this->statuses[ 'Bill' ][ $status ] ] );
        }

        $mapSupplierCreditBillPayment = MapSupplierCreditBillPayment::join( 'supplier_credit', 'map_supplier_credit_bill_payment.supplier_credit_id', '=', 'supplier_credit.id' )
            ->join( 'expenses', 'supplier_credit.expenses_id', '=', 'expenses.id' )
            ->where( 'expenses.id', $expenses->id )->get();

        foreach ( $mapSupplierCreditBillPayment as $map ) {
            $expenses = MapSupplierCreditBillPayment::join( 'supplier_credit', 'map_supplier_credit_bill_payment.supplier_credit_id', '=', 'supplier_credit.id' )
                ->join( 'expenses', 'supplier_credit.expenses_id', '=', 'expenses.id' )
                ->where( 'supplier_credit_id', $map->supplier_credit_id )->first();

            $balance = Expenses::find( $expenses->id )->balance + $map->payment;
            $status = $balance == $expenses->total ? 'Unapplied' : 'Partial';

            Expenses::find( $expenses->id )->update( [ 'balance' => $balance, 'status' => $this->statuses[ 'Supplier Credit' ][ $status ] ] );
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $billPayment[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Deleted ',
                'target_name' => 'Bill Payment',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'open_balance' => $expenses[ 'balance' ],
                'message' => $billPayment[ 'note' ]
            ] 
        );
    }

    public function recoverDelete( Request $request, $id ) {
        $expenses = Expenses::find( $id );
        $expenses->update( [ 'is_trash' => 0 ] );

        $billPayment = BillPayment::where( 'expenses_id', $expenses[ 'id' ] )->first();

        $accountId = $this->account[ 'Accounts Payable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses->total ] );

        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses->total ] );

        Supplier::find( $expenses[ 'payee_id' ] )->update( [ 'balance' => Supplier::find( $expenses[ 'payee_id' ] )->balance - $expenses->total ] );

        $mapBillBillPayment = MapBillBillPayment::join( 'bill_payment', 'map_bill_bill_payment.bill_payment_id', '=', 'bill_payment.id' )->join( 'expenses', 'bill_payment.expenses_id', '=', 'expenses.id' )
            ->where( 'expenses.id', $expenses->id )->get();

        foreach ( $mapBillBillPayment as $map ) {
            $expenses = MapBillBillPayment::join( 'bill', 'map_bill_bill_payment.bill_id', '=', 'bill.id' )->join( 'expenses', 'bill.expenses_id', '=', 'expenses.id' )
                ->where( 'bill_id', $map->bill_id )->first();

            $balance = Expenses::find( $expenses->id )->balance - $map->payment;
            $status = $balance == $expenses->total ? 'Unpaid' : ( $balance == 0 ? 'Paid' : 'Partial' );

            Expenses::find( $expenses->id )->update( [ 'balance' => $balance, 'status' => $this->statuses[ 'Bill' ][ $status ] ] );
        }

        $mapSupplierCreditBillPayment = MapSupplierCreditBillPayment::join( 'supplier_credit', 'map_supplier_credit_bill_payment.supplier_credit_id', '=', 'supplier_credit.id' )
            ->join( 'expenses', 'supplier_credit.expenses_id', '=', 'expenses.id' )
            ->where( 'expenses.id', $expenses->id )->get();

        foreach ( $mapSupplierCreditBillPayment as $map ) {
            $expenses = MapSupplierCreditBillPayment::join( 'supplier_credit', 'map_supplier_credit_bill_payment.supplier_credit_id', '=', 'supplier_credit.id' )
                ->join( 'expenses', 'supplier_credit.expenses_id', '=', 'expenses.id' )
                ->where( 'supplier_credit_id', $map->supplier_credit_id )->first();

            $balance = Expenses::find( $expenses->id )->balance - $map->payment;
            $status = $balance == $expenses->total ? 'Unapplied' : ( $balance == 0 ? 'Closed' : 'Partial' );

            Expenses::find( $expenses->id )->update( [ 'balance' => $balance, 'status' => $this->statuses[ 'Supplier Credit' ][ $status ] ] );
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $billPayment[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Recovered ',
                'target_name' => 'Bill Payment',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'open_balance' => $expenses[ 'balance' ],
                'message' => $billPayment[ 'note' ]
            ] 
        );
    }
}
