<?php

namespace App\Http\Controllers\TRXN;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Account;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillAccount;
use App\Models\Expenses;
use App\Models\Supplier;
use App\Models\SupplierCredit;
use App\Models\ProductService;
use App\Models\SupplierCreditAccount;
use App\Models\SupplierCreditItem;
use App\Models\MapBillBillPayment;
use App\Models\MapSupplierCreditBillPayment;

use App\Helper\RestResponseMessages;

class SupplierCreditController extends TRXNController
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
        $expenses                   =   $GLOBALS[ 'input' ][ 'transaction' ];
        $supplierCredit             =   $GLOBALS[ 'input' ][ 'supplierCredit' ];
        $supplierCreditAccounts     =   array_filter( $GLOBALS[ 'input' ][ 'supplierCreditAccounts' ] );
        $supplierCreditItems        =   array_filter( $GLOBALS[ 'input' ][ 'supplierCreditItems' ] );

        $expenses[ 'payee_id' ]     =   $this->payee[ $expenses[ 'supplier' ] ]->id;
        $expenses[ 'payee_type' ]   =   $this->payee[ $expenses[ 'supplier' ] ]->type;

        $expenses = Expenses::create( $expenses );

        $supplierCredit[ 'expenses_id' ] = $expenses->id;
        $supplierCredit = SupplierCredit::create( $supplierCredit );

        foreach ( $supplierCreditAccounts as $supplierCreditAccount ) {
            if ( isset( $supplierCreditAccount[ 'account' ] ) ) {
                $supplierCreditAccount[ 'supplier_credit_id' ] = $supplierCredit->id;
                $supplierCreditAccount[ 'account_id' ] = $this->account[ $supplierCreditAccount[ 'account' ] ]->id;
                SupplierCreditAccount::create( $supplierCreditAccount );
            }
        }

        foreach ( $supplierCreditItems as $supplierCreditItem ) {
            if ( isset( $supplierCreditItem[ 'product_service' ] ) ) {
                $supplierCreditItem[ 'supplier_credit_id' ] = $supplierCredit->id;
                $supplierCreditItem[ 'product_service_id' ] = $this->product_service[ $supplierCreditItem[ 'product_service' ] ]->id;
                SupplierCreditItem::create( $supplierCreditItem );
            }
        }

        Supplier::find( $expenses[ 'payee_id' ] )->update( [ 'balance' => Supplier::find( $expenses[ 'payee_id' ] )->balance - $expenses->total ] );

        $accountId = $this->account[ 'Accounts Payable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses[ 'total' ] ] );

        $accountId = $this->account[ 'Rent Expense' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses[ 'total' ] ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $supplierCredit[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Added ',
                'target_name' => 'Supplier Credit',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'open_balance' => $expenses[ 'balance' ],
                'memo' => $supplierCredit[ 'statement_memo' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage( 'Create Bill', $expenses, 200 );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $expenses = Expenses::find( $id );
        $supplierCredit = SupplierCredit::where( 'expenses_id', $expenses->id )->first();
        $supplierCreditItems = SupplierCreditItem::where( 'supplier_credit_id', $supplierCredit->id )->get();
        $supplierCreditAccounts = SupplierCreditAccount::where( 'supplier_credit_id', $supplierCredit->id )->get();

        $expenses->supplier = Supplier::find( $expenses->payee_id )->name;

        foreach ( $supplierCreditItems as $supplierCreditItem ) {
            $supplierCreditItem->product_service = ProductService::find( $supplierCreditItem->product_service_id )->name;
        }

        foreach ( $supplierCreditAccounts as $supplierCreditAccount ) {
            $supplierCreditAccount->account = Account::find( $supplierCreditAccount->account_id )->name;
        }

        return RestResponseMessages::TRXNSuccessMessage( 'Retrieve Bill', [ 'transaction' => $expenses, 'supplierCredit' => $supplierCredit, 'supplierCreditItems' => $supplierCreditItems, 'supplierCreditAccounts' => $supplierCreditAccounts ], 200 );
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
        $expenses                   =   $GLOBALS[ 'input' ][ 'transaction' ];
        $supplierCredit             =   $GLOBALS[ 'input' ][ 'supplierCredit' ];
        $supplierCreditAccounts     =   array_filter( $GLOBALS[ 'input' ][ 'supplierCreditAccounts' ] );
        $supplierCreditItems        =   array_filter( $GLOBALS[ 'input' ][ 'supplierCreditItems' ] );

        $expenses[ 'payee_id' ]     =   $this->payee[ $expenses[ 'supplier' ] ]->id;
        $expenses[ 'payee_type' ]   =   $this->payee[ $expenses[ 'supplier' ] ]->type;

        $orgExpenses = Expenses::find( $expenses[ 'id' ] );
        $difference = $expenses[ 'total' ] - $orgExpenses->total;

        $expenses[ 'balance' ] = max( $orgExpenses[ 'balance' ] + $difference, 0 );
        $expenses[ 'status' ] = $this->statuses[ 'Supplier Credit' ][ $expenses[ 'balance' ] == $expenses[ 'total' ] ? 'Unapplied' : ( $expenses[ 'balance' ] == 0 ? 'Closed' : 'Partial' ) ];
        Expenses::find( $expenses[ 'id' ] )->update( $expenses );

        $supplierCredit[ 'expenses_id' ] = $expenses[ 'id' ];
        SupplierCredit::find( $supplierCredit[ 'id' ] )->update( $supplierCredit );

        if ( $difference < -$orgExpenses[ 'balance' ] ) {
            $difference += $orgExpenses[ 'balance' ];
            $billIds = Expenses::where( 'expenses.id', $expenses[ 'id' ] )
                                ->join( 'supplier_credit', 'supplier_credit.expenses_id', '=', 'expenses.id' )
                                ->join( 'map_supplier_credit_bill_payment', 'map_supplier_credit_bill_payment.supplier_credit_id', '=', 'supplier_credit.id' )
                                ->join( 'bill_payment', 'bill_payment.id', '=', 'map_supplier_credit_bill_payment.bill_payment_id' )
                                ->join( 'map_bill_bill_payment', 'map_bill_bill_payment.bill_payment_id', '=', 'bill_payment.id' )
                                ->join( 'bill', 'map_bill_bill_payment.bill_id', '=', 'bill.id' )
                                ->join( 'expenses as expenses2', 'expenses2.id', '=', 'bill.expenses_id' )
                                ->select( 'expenses2.id' )
                                ->pluck( 'id' );

            for ( $i = 0; $i < count( $billIds ); $i++ ) {
                $billId = $billIds[ $i ];
                $orgBillExpense = Expenses::find( $billId );
                if ( $difference < 0 ) {
                    $balance = min( $orgBillExpense->balance - $difference, $orgBillExpense->total );
                    $status = $balance == $orgBillExpense->total ? 'Unpaid' : 'Partial';

                    if ( Expenses::find( $billId )->balance != $balance ) {
                        $this->createAuditLog(
                            [
                                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                                'record_id' => $billId,
                                'trxn_id' => Bill::where( 'expenses_id', $billId )->first()->id,
                                'date_changed' => date( 'Y-m-d H:i:s' ), 
                                'user_email' => \Auth::user()->email, 
                                'event_text' => 'Edited ',
                                'target_name' => 'Bill',
                                'person_id' => $expenses[ 'payee_id' ],
                                'person_type' => $expenses[ 'payee_type' ],
                                'date' => $expenses[ 'date' ],
                                'amount' => Expenses::find( $billId )->total,
                                'open_balance' => Expenses::find( $billId )->balance,
                                'items' => BillItem::where( 'bill_id', Bill::where( 'expenses_id', $billId )->first()->id ),
                                'is_indirect' => 1
                            ] 
                        );
                    }

                    Expenses::find( $billId )->update( [ 'balance' => $balance, 'status' => $this->statuses[ 'Supplier Credit' ][ $status ] ] );
                    $difference += $orgBillExpense->total - $orgBillExpense->balance;

                    $billPayment = $orgBillExpense->total - $balance;
                    $mapBillBillPayments = MapBillBillPayment::where( 'bill_id', Bill::where( 'expenses_id', $billId )->first()->id )->get();
                    for ( $i = 0; $i < count( $mapBillBillPayments ); $i++ ) {
                        $map = $mapBillBillPayments[ $i ];
                        if ( $billPayment >= 0 ) {
                            $orgPayment = $map->payment;
                            $balance = min( $orgPayment, $billPayment );
                            if ( $balance == 0 )
                                $map->delete();
                            else
                                $map->update( [ 'payment' => $balance ] );
                            $billPayment -= $balance;
                        }
                    }
                }
            }


            $supplierCreditPayment = $expenses[ 'total' ] - $expenses[ 'balance' ];
            $mapSupplierCreditBillPayments = MapSupplierCreditBillPayment::where( 'supplier_credit_id', $supplierCredit[ 'id' ] )->get();
            for ( $i = 0; $i < count( $mapSupplierCreditBillPayments); $i++ ) {
                $map = $mapSupplierCreditBillPayments[ $i ];
                if ( $supplierCreditPayment >= 0) {
                    $orgPayment = $map->payment;
                    $balance = min( $orgPayment, $supplierCreditPayment );
                    if ( $balance == 0 )
                        $map->delete();
                    else
                        $map->update( [ 'payment' => $balance ] );
                    $supplierCreditPayment -= $balance;
                }
            }
        }

        foreach ( $supplierCreditAccounts as $supplierCreditAccount ) {
            if ( isset( $supplierCreditAccount[ 'account' ] ) ) {
                $supplierCreditAccount[ 'supplier_credit_id' ] = $supplierCredit[ 'id' ];
                $supplierCreditAccount[ 'account_id' ] = $this->account[ $supplierCreditAccount[ 'account' ] ]->id;

                if ( isset( $supplierCreditAccount[ 'id' ] ) ) {
                    SupplierCreditAccount::find( $supplierCreditAccount[ 'id' ] )->update( $supplierCreditAccount );
                } else {
                    SupplierCreditAccount::create( $supplierCreditAccount );
                }
            }
        }

        foreach ( $supplierCreditItems as $supplierCreditItem ) {
            if ( isset( $supplierCreditItem[ 'product_service' ] ) ) {
                $supplierCreditItem[ 'supplier_credit_id' ] = $supplierCredit[ 'id' ];
                $supplierCreditItem[ 'product_service_id' ] = $this->product_service[ $supplierCreditItem[ 'product_service' ] ]->id;

                if ( isset( $supplierCreditItem[ 'id' ] ) ) {
                    SupplierCreditItem::find( $supplierCreditItem[ 'id' ] )->update( $supplierCreditItem );
                } else {
                    SupplierCreditItem::create( $supplierCreditItem );
                }
            }
        }

        Supplier::find( $expenses[ 'payee_id' ] )->update( [ 'balance' => Supplier::find( $expenses[ 'payee_id' ] )->balance - $expenses[ 'total' ] + $orgExpenses->total ] );

        $accountId = $this->account[ 'Accounts Payable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses[ 'total' ] + $orgExpenses->total ] );

        $accountId = $this->account[ 'Rent Expense' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses[ 'total' ] + $orgExpenses->total ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $supplierCredit[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Edited ',
                'target_name' => 'Supplier Credit',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'open_balance' => $expenses[ 'balance' ],
                'memo' => $supplierCredit[ 'statement_memo' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage( 'Update Bill', $expenses, 200 );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id )
    {
        $expenses = Expenses::find( $id );
        $expenses->update( [ 'is_trash' => 1 ] );

        $supplierCredit = SupplierCredit::where( 'expenses_id', $expenses[ 'id' ] )->first();

        Supplier::find( $expenses[ 'payee_id' ] )->update( [ 'balance' => Supplier::find( $expenses[ 'payee_id' ] )->balance + $expenses->total ] );

        $accountId = $this->account[ 'Accounts Payable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $expenses->total ] );

        $accountId = $this->account[ 'Rent Expense' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $expenses->total ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $supplierCredit[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Deleted ',
                'target_name' => 'Supplier Credit',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'open_balance' => $expenses[ 'balance' ],
                'memo' => $supplierCredit[ 'statement_memo' ]
            ] 
        );
    }

    public function recoverDelete( $id ) 
    {
        $expenses = Expenses::find( $id );
        $expenses->update( [ 'is_trash' => 0 ] );
        
        $supplierCredit = SupplierCredit::where( 'expenses_id', $expenses[ 'id' ] )->first();

        Supplier::find( $expenses[ 'payee_id' ] )->update( [ 'balance' => Supplier::find( $expenses[ 'payee_id' ] )->balance - $expenses->total ] );

        $accountId = $this->account[ 'Accounts Payable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses->total ] );

        $accountId = $this->account[ 'Rent Expense' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses->total ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $supplierCredit[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Recovered ',
                'target_name' => 'Supplier Credit',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'open_balance' => $expenses[ 'balance' ],
                'memo' => $supplierCredit[ 'statement_memo' ]
            ] 
        );
    }
}
