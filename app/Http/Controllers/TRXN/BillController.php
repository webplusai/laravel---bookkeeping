<?php

namespace App\Http\Controllers\TRXN;

use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;

use App\Models\Bill;
use App\Models\Account;
use App\Models\Supplier;
use App\Models\Expenses;
use App\Models\BillItem;
use App\Models\Attachment;
use App\Models\BillAccount;
use App\Models\BillPayment;
use App\Models\SupplierCredit;
use App\Models\SupplierCreditItem;
use App\Models\SupplierCreditAccount;
use App\Models\ProductService;
use App\Models\AccountCategoryType;
use App\Models\MapBillBillPayment;
use App\Models\MapExpensesAttachment;
use App\Models\MapSupplierCreditBillPayment;

use App\Helper\RestResponseMessages;
use App\Helper\StringConversionFunctions;


class BillController extends TRXNController
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
        $expenses           =   $GLOBALS[ 'input' ][ 'transaction' ];
        $bill               =   $GLOBALS[ 'input' ][ 'bill' ];
        $billAccounts       =   array_filter( $GLOBALS[ 'input' ][ 'billAccounts' ] );
        $billItems          =   array_filter( $GLOBALS[ 'input' ][ 'billItems' ] );

        $expenses[ 'payee_id' ] = $this->supplier[ $expenses[ 'supplier' ] ]->id;
        $expenses[ 'payee_type' ] = $this->personTypes[ 'Supplier' ];
        $expenses[ 'balance' ] = $expenses[ 'total' ];
        $expenses[ 'status' ] = $this->statuses[ 'Bill' ][ 'Unpaid' ];
        $expenses = Expenses::create( $expenses );

        $bill[ 'expenses_id' ] = $expenses->id;
        $bill = Bill::create( $bill );

        $totalPurchases = 0;
        $totalMerchandiseInventory = 0;
        foreach ( $billItems as $billItem ) {
            if ( isset( $billItem[ 'product_service' ] ) ) {
                $productService = $this->product_service[ $billItem[ 'product_service' ] ];
                $billItem[ 'bill_id' ] = $bill->id;
                $billItem[ 'product_service_id' ] = $productService->id;
                BillItem::create( $billItem );

                if ( $productService->is_inventoriable )
                    $totalMerchandiseInventory += $billItem[ 'amount' ];
                else
                    $totalPurchases += $billItem[ 'amount' ];
            }
        }

        foreach ( $billAccounts as $billAccount ) {
            if ( isset( $billAccount[ 'account' ] ) ) {
                $billAccount[ 'bill_id' ] = $bill->id;
                $billAccount[ 'account_id' ] = $this->account[ $billAccount[ 'account' ] ]->id;
                BillAccount::create( $billAccount );

                $account = Account::find( $billAccount[ 'account_id' ] );
                $accountCategoryName = AccountCategoryType::find( $account->account_category_type_id )->name;

                $increaseAccounts = [ 'Current Asset', 'Non-Current Asset', 'Operating Expense', 'Non-Operating Expenses and Loss', 'Cost of Sales/Services' ];
                $decreaseAccounts = [ 'Current Liability', 'Non-Current Liability', 'Operating Revenue', 'Non-Operating Revenues and Gains', 'Owner\'s Equity' ];

                if ( in_array( $accountCategoryName, $increaseAccounts ) ) {
                    $account->update( [ 'balance' => $account->balance + $billAccount[ 'amount' ] ] );
                } else if ( in_array( $accountCategoryName, $decreaseAccounts ) ) {
                    $account->update( [ 'balance' => $account->balance - $billAccount[ 'amount' ] ] );
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
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $expenses[ 'total' ] ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalMerchandiseInventory ] );

        $accountId = $this->account[ 'Purchases' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalPurchases ] );

        Supplier::find( $expenses[ 'payee_id' ] )->update( [ 'balance' => Supplier::find( $expenses[ 'payee_id' ] )->balance + $expenses[ 'total' ] ] );

        $items = array_merge( $billItems, $billAccounts );
        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $bill[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Added ',
                'target_name' => 'Bill',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'open_balance' => $expenses[ 'balance' ],
                'memo' => $bill[ 'statement_memo' ],
                'items' => $items                
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
        $bill = Bill::where( 'expenses_id', $expenses->id )->first();
        $billItems = BillItem::where( 'bill_id', $bill->id )->get();
        $billAccounts = BillAccount::where( 'bill_id', $bill->id )->get();

        return RestResponseMessages::TRXNSuccessMessage( 'Retrieve Bill', [ 'transaction' => $expenses, 'bill' => $bill, 'billItems' => $billItems, 'billAccounts' => $billAccounts ], 200 );
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
        $expenses           =   $GLOBALS[ 'input' ][ 'transaction' ];
        $bill               =   $GLOBALS[ 'input' ][ 'bill' ];
        $billAccounts       =   array_filter( $GLOBALS[ 'input' ][ 'billAccounts' ] );
        $billItems          =   array_filter( $GLOBALS[ 'input' ][ 'billItems' ] );

        $expenses[ 'payee_id' ] = $this->supplier[ $expenses[ 'supplier' ] ]->id;
        
        $orgExpenses = Expenses::find( $expenses[ 'id' ] );
        $difference = $expenses[ 'total' ] - $orgExpenses->total;

        $expenses[ 'balance' ] = max( $orgExpenses[ 'balance' ] + $difference, 0 );
        $expenses[ 'status' ] = $this->statuses[ 'Bill' ][ $expenses[ 'balance' ] == $expenses[ 'total' ] ? 'Unpaid' : ( $expenses[ 'balance' ] == 0 ? 'Paid' : 'Partial' ) ];
        Expenses::find( $expenses[ 'id' ] )->update( $expenses );

        $bill[ 'expenses_id' ] = $expenses[ 'id' ];
        Bill::find( $bill[ 'id' ] )->update( $bill );

        if ( $difference < -$orgExpenses[ 'balance' ] ) {
            $difference += $orgExpenses[ 'balance' ];
            $supplierCreditIds = Expenses::where( 'expenses.id', $expenses[ 'id' ] )
                                        ->join( 'bill', 'bill.expenses_id', '=', 'expenses.id' )
                                        ->join( 'map_bill_bill_payment', 'map_bill_bill_payment.bill_id', '=', 'bill.id' )
                                        ->join( 'bill_payment', 'bill_payment.id', '=', 'map_bill_bill_payment.bill_payment_id' )
                                        ->join( 'map_supplier_credit_bill_payment', 'map_supplier_credit_bill_payment.bill_payment_id', '=', 'bill_payment.id' )
                                        ->join( 'supplier_credit', 'map_supplier_credit_bill_payment.supplier_credit_id', '=', 'supplier_credit.id' )
                                        ->join( 'expenses as expenses2', 'expenses2.id', '=', 'supplier_credit.expenses_id' )
                                        ->select( 'expenses2.id' )
                                        ->pluck( 'id' );


            for ( $i = 0; $i < count( $supplierCreditIds ); $i++ ) {
                $supplierCreditId = $supplierCreditIds[ $i ];
                $orgSupplierCreditExpense = Expenses::find( $supplierCreditId );
                if ( $difference < 0 ) {
                    $balance = min( $orgSupplierCreditExpense->balance - $difference, $orgSupplierCreditExpense->total );
                    $status = $balance == $orgSupplierCreditExpense->total ? 'Unapplied' : 'Partial';
                    Expenses::find( $supplierCreditId )->update( [ 'balance' => $balance, 'status' => $this->statuses[ 'Supplier Credit' ][ $status ] ] );

                    $itemDetails    = SupplierCreditItem::where( 'supplier_credit_id', SupplierCredit::where( 'expenses_id', $supplierCreditId )->first()->id )->get()->toArray();
                    $accountDetails = SupplierCreditAccount::where( 'supplier_credit_id', SupplierCredit::where( 'expenses_id', $supplierCreditId )->first()->id )->get()->toArray();
                    $items = array_merge( $itemDetails, $accountDetails );
                    $this->createAuditLog(
                        [
                            'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                            'record_id' => $supplierCreditId,
                            'trxn_id' => SupplierCredit::where( 'expenses_id', $supplierCreditId )->first()->id,
                            'date_changed' => date( 'Y-m-d H:i:s' ), 
                            'user_email' => \Auth::user()->email, 
                            'event_text' => 'Edited ',
                            'target_name' => 'Supplier Credit',
                            'person_id' => $expenses[ 'payee_id' ],
                            'person_type' => $expenses[ 'payee_type' ],
                            'date' => $expenses[ 'date' ],
                            'amount' => Expenses::find( $supplierCreditId )->total,
                            'open_balance' => $balance,
                            'items' => $items,
                            'is_indirect' => 1
                        ] 
                    );

                    $difference += $orgSupplierCreditExpense->total - $orgSupplierCreditExpense->balance;

                    $supplierCreditPayment = $orgSupplierCreditExpense->total - $balance;
                    $mapSupplierCreditBillPayments = MapSupplierCreditBillPayment::where( 'supplier_credit_id', SupplierCredit::where( 'expenses_id', $supplierCreditId )->first()->id )->get();
                    for ( $i = 0; $i < count( $mapSupplierCreditBillPayments ); $i++ ) {
                        $map = $mapSupplierCreditBillPayments[ $i ];
                        if ( $supplierCreditPayment >= 0 ) {
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
            }

            $billPayment = $expenses[ 'total' ] - $expenses[ 'balance' ];
            $mapBillBillPayments = MapBillBillPayment::where( 'bill_id', $bill[ 'id' ] )->get();
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

            if ( $difference < 0 ) {
                $billPaymentIds = Expenses::where( 'expenses.id', $expenses[ 'id' ] )
                                            ->join( 'bill', 'bill.expenses_id', '=', 'expenses.id' )
                                            ->join( 'map_bill_bill_payment', 'map_bill_bill_payment.bill_id', '=', 'bill.id' )
                                            ->join( 'bill_payment', 'bill_payment.id', '=', 'map_bill_bill_payment.bill_payment_id' )
                                            ->join( 'expenses as expenses2', 'expenses2.id', '=', 'bill_payment.expenses_id' )
                                            ->select( 'expenses2.id' )
                                            ->pluck( 'id' );
                for ( $i = 0; $i < count( $billPaymentIds ); $i++ ) {
                    $billPaymentId = $billPaymentIds[ $i ];
                    $orgBillPaymentExpense = Expenses::find( $billPaymentId );
                    if ( $difference < 0 ) {
                        $balance = min( $orgBillPaymentExpense->balance - $difference, $orgBillPaymentExpense->total );
                        $status = $balance == $orgBillPaymentExpense->total ? 'Unapplied' : 'Partial';
                        Expenses::find( $billPaymentId )->update( [ 'balance' => $balance, 'status' => $this->statuses[ 'Bill Payment' ][ $status ] ] );

                        $itemDetails    = BillPaymentItem::where( 'bill_payment_id', BillPayment::where( 'expenses_id', $billPaymentId )->id )->get()->toArray();
                        $accountDetails = BillPaymentAccount::where( 'bill_payment_id', BillPayment::where( 'expenses_id', $billPaymentId )->id )->get()->toArray();
                        $items = array_merge( $itemDetails, $accountDetails );
                        $this->createAuditLog(
                            [
                                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                                'record_id' => $billPaymentId,
                                'trxn_id' => BillPayment::where( 'expenses_id', $billPaymentId )->first()->id,
                                'date_changed' => date( 'Y-m-d H:i:s' ), 
                                'user_email' => \Auth::user()->email, 
                                'event_text' => 'Edited ',
                                'target_name' => 'Bill',
                                'person_id' => $expenses[ 'payee_id' ],
                                'person_type' => $expenses[ 'payee_type' ],
                                'date' => $expenses[ 'date' ],
                                'amount' => Expenses::find( $billPaymentId )->total,
                                'open_balance' => $balance,
                                'items' => $items,
                                'is_indirect' => 1
                            ] 
                        );

                        $difference += $orgBillPaymentExpense->total - $orgBillPaymentExpense->balance;
                    }
                }
            }
        }

        $totalPurchases = 0;
        $totalOrgPurchases = 0;
        $totalMerchandiseInventory = 0;
        $totalOrgMerchandiseInventory = 0;
        foreach ( $billItems as $billItem ) {
            if ( isset( $billItem[ 'product_service' ] ) ) {
                $productService = $this->product_service[ $billItem[ 'product_service' ] ];
                $billItem[ 'bill_id' ] = $bill[ 'id' ];
                $billItem[ 'product_service_id'] = $productService->id;

                if ( $productService->is_inventoriable )
                    $totalMerchandiseInventory +=  $billItem[ 'amount' ];
                else
                    $totalPurchases += $billItem[ 'amount' ];

                if ( isset( $billItem[ 'id' ] ) ) {
                    if ( $productService->is_inventoriable )
                        $totalOrgMerchandiseInventory += BillItem::find( $billItem[ 'id' ] )->amount;
                    else
                        $totalOrgPurchases += BillItem::find( $billItem[ 'id' ] )->amount;
                    BillItem::find( $billItem[ 'id' ] )->update( $billItem );
                }
                else
                    BillItem::create( $billItem );
            }
        }

        foreach ( $billAccounts as $billAccount ) {
            if ( isset( $billAccount[ 'account' ] ) ) {
                $billAccount[ 'bill_id' ] = $bill[ 'id' ];
                $billAccount[ 'account_id' ] = $this->account[ $billAccount[ 'account' ] ]->id;

                $orgBillAccountBalance = 0;
                if ( isset( $billAccount[ 'id' ] ) ) {
                    $orgBillAccountBalance = BillAccount::find( $billAccount[ 'id' ] )->amount;
                    BillAccount::find( $billAccount[ 'id' ] )->update( $billAccount );
                }
                else {
                    BillAccount::create( $billAccount );
                    $orgBillAccountBalance = 0;
                }

                $account = Account::find( $billAccount[ 'account_id' ] );
                $accountCategoryName = AccountCategoryType::find( $account->account_category_type_id )->name;

                $increaseAccounts = [ 'Current Asset', 'Non-Current Asset', 'Operating Expense', 'Non-Operating Expenses and Loss', 'Cost of Sales/Services' ];
                $decreaseAccounts = [ 'Current Liability', 'Non-Current Liability', 'Operating Revenue', 'Non-Operating Revenues and Gains', 'Owner\'s Equity' ];

                if ( in_array( $accountCategoryName, $increaseAccounts ) ) {
                    $account->update( [ 'balance' => $account->balance + $billAccount[ 'amount' ] - $orgBillAccountBalance ] );
                } else if ( in_array( $accountCategoryName, $decreaseAccounts ) ) {
                    $account->update( [ 'balance' => $account->balance - $billAccount[ 'amount' ] + $orgBillAccountBalance ] );
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
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $expenses[ 'total' ] - $orgExpenses->total ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalMerchandiseInventory - $totalOrgMerchandiseInventory ] );

        $accountId = $this->account[ 'Purchases' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalPurchases - $totalOrgPurchases ] );

        Supplier::find( $expenses[ 'payee_id' ] )->update( [ 'balance' => Supplier::find( $expenses[ 'payee_id' ] )->balance + $expenses[ 'total' ] - $orgExpenses->total ] );

        $items = array_merge( $billItems, $billAccounts );
        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $bill[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Edited ',
                'target_name' => 'Bill',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'open_balance' => $expenses[ 'balance' ],
                'memo' => $bill[ 'statement_memo' ],
                'items' => $items
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
        $expenses->update( [ 'is_trash'=> 1 ] );

        $bill = Bill::where( 'expenses_id', $expenses->id )->first();
        $billItems = BillItem::where( 'bill_id', $bill->id )->get();
        $billAccounts = BillAccount::where( 'bill_id', $bill->id )->get();

        $totalPurchases = 0;
        $totalMerchandiseInventory = 0;
        foreach ( $billItems as $billItem ) {
            $productService = ProductService::find( $billItem[ 'product_service_id' ] );
            if ( $productService->is_inventoriable )
                $totalMerchandiseInventory += $billItem[ 'amount' ];
            else
                $totalPurchases += $billItem[ 'amount' ];
        }
        
        $accountId = $this->account[ 'Accounts Payable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses->total ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $totalMerchandiseInventory ] );

        $accountId = $this->account[ 'Purchases' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $totalPurchases ] );

        Supplier::find( $expenses[ 'payee_id' ] )->update( [ 'balance' => Supplier::find( $expenses[ 'payee_id' ] )->balance - $expenses->total ] );

        foreach ( $billAccounts as $billAccount ) {

            $account = Account::find( $billAccount[ 'account_id' ] );
            $accountCategoryName = AccountCategoryType::find( $account->account_category_type_id )->name;

            $increaseAccounts = [ 'Current Asset', 'Non-Current Asset', 'Operating Expense', 'Non-Operating Expenses and Loss', 'Cost of Sales/Services' ];
            $decreaseAccounts = [ 'Current Liability', 'Non-Current Liability', 'Operating Revenue', 'Non-Operating Revenues and Gains', 'Owner\'s Equity' ];

            if ( in_array( $accountCategoryName, $increaseAccounts ) ) {
                $account->update( [ 'balance' => $account->balance - $billAccount[ 'amount' ] ] );
            } else if ( in_array( $accountCategoryName, $decreaseAccounts ) ) {
                $account->update( [ 'balance' => $account->balance + $billAccount[ 'amount' ] ] );
            }
        }

        $mapBillBillPayment = MapBillBillPayment::join( 'bill', 'map_bill_bill_payment.bill_id', '=', 'bill.id' )->join( 'expenses', 'bill.expenses_id', '=', 'expenses.id' )
            ->where( 'expenses.id', $expenses->id )->get();

        foreach ( $mapBillBillPayment as $map ) {
            $expenses = MapBillBillPayment::join( 'bill_payment', 'map_bill_bill_payment.bill_payment_id', '=', 'bill_payment.id' )->join( 'expenses', 'bill_payment.expenses_id', '=', 'expenses.id' )
                ->where( 'bill_payment_id', $map->bill_payment_id )->first();
            $balance = Expenses::find( $expenses->id )->balance + $map->payment;

            $mapSupplierCreditBillPayments = MapSupplierCreditBillPayment::where( 'bill_payment_id', $map->bill_payment_id )->get();
            foreach ( $mapSupplierCreditBillPayments as $map ) {
                $balance -= $map->payment;
                $supplierCredit = Expenses::find( SupplierCredit::find( $map->supplier_credit_id )->expenses_id );
                $supplierCredit->balance += $map->payment;
                $supplierCredit->status = $supplierCredit->balance == $supplierCredit->total ? $this->statuses[ 'Supplier Credit' ][ 'Unapplied' ] : $this->statuses[ 'Supplier Credit' ][ 'Partial' ];
                $supplierCredit->update( [ 'balance' => $supplierCredit->balance, 'status' => $supplierCredit->status ] );
            }

            $status = $balance == $expenses->total ? $this->statuses[ 'Bill Payment' ][ 'Unapplied' ] : $this->statuses[ 'Bill Payment' ][ 'Partial' ];
            Expenses::find( $expenses->id )->update( [ 'balance' => $balance, 'status' => $status ] );
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $bill[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Deleted ',
                'target_name' => 'Bill',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'open_balance' => $expenses[ 'balance' ],
                'memo' => $bill[ 'statement_memo' ]
            ] 
        );
    }

    public function recoverDelete( Request $request, $id ) {
        $expenses = Expenses::find( $id );
        $expenses->update( [ 'is_trash'=> 0 ] );

        $bill = Bill::where( 'expenses_id', $expenses->id )->first();
        $billItems = BillItem::where( 'bill_id', $bill->id )->get();
        $billAccounts = BillAccount::where( 'bill_id', $bill->id )->get();

        $totalPurchases = 0;
        $totalMerchandiseInventory = 0;
        foreach ( $billItems as $billItem ) {
            $productService = ProductService::find( $billItem[ 'product_service_id' ] );
            if ( $productService->is_inventoriable )
                $totalMerchandiseInventory += $billItem[ 'amount' ];
            else
                $totalPurchases += $billItem[ 'amount' ];
        }
        
        $accountId = $this->account[ 'Accounts Payable' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $expenses->total ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalMerchandiseInventory ] );

        $accountId = $this->account[ 'Purchases' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalPurchases ] );

        Supplier::find( $expenses[ 'payee_id' ] )->update( [ 'balance' => Supplier::find( $expenses[ 'payee_id' ] )->balance + $expenses->total ] );

        foreach ( $billAccounts as $billAccount ) {

            $account = Account::find( $billAccount[ 'account_id' ] );
            $accountCategoryName = AccountCategoryType::find( $account->account_category_type_id )->name;

            $increaseAccounts = [ 'Current Asset', 'Non-Current Asset', 'Operating Expense', 'Non-Operating Expenses and Loss', 'Cost of Sales/Services' ];
            $decreaseAccounts = [ 'Current Liability', 'Non-Current Liability', 'Operating Revenue', 'Non-Operating Revenues and Gains', 'Owner\'s Equity' ];

            if ( in_array( $accountCategoryName, $increaseAccounts ) ) {
                $account->update( [ 'balance' => $account->balance + $billAccount[ 'amount' ] ] );
            } else if ( in_array( $accountCategoryName, $decreaseAccounts ) ) {
                $account->update( [ 'balance' => $account->balance - $billAccount[ 'amount' ] ] );
            }
        }

        $mapBillBillPayment = MapBillBillPayment::join( 'bill', 'map_bill_bill_payment.bill_id', '=', 'bill.id' )->join( 'expenses', 'bill.expenses_id', '=', 'expenses.id' )
            ->where( 'expenses.id', $expenses->id )->get();

        foreach ( $mapBillBillPayment as $map ) {
            $expenses = MapBillBillPayment::join( 'bill_payment', 'map_bill_bill_payment.bill_payment_id', '=', 'bill_payment.id' )->join( 'expenses', 'bill_payment.expenses_id', '=', 'expenses.id' )
                ->where( 'bill_payment_id', $map->bill_payment_id )->first();
            $balance = Expenses::find( $expenses->id )->balance - $map->payment;

            $mapSupplierCreditBillPayments = MapSupplierCreditBillPayment::where( 'bill_payment_id', $map->bill_payment_id )->get();
            foreach ( $mapSupplierCreditBillPayments as $map ) {
                $balance += $map->payment;
                $supplierCredit = Expenses::find( SupplierCredit::find( $map->supplier_credit_id )->expenses_id );
                $supplierCredit->balance -= $map->payment;
                $supplierCredit->status = $supplierCredit->balance == $supplierCredit->total ? $this->statuses[ 'Supplier Credit' ][ 'Unapplied' ] : ( $supplierCredit->balance == 0 ? $this->statuses[ 'Supplier Credit' ][ 'Closed' ] : $this->statuses[ 'Supplier Credit' ][ 'Partial' ] );
                $supplierCredit->update( [ 'balance' => $supplierCredit->balance, 'status' => $supplierCredit->status ] );
            }

            $status = $balance == $expenses->total ? $this->statuses[ 'Bill Payment' ][ 'Unapplied' ] :  ( $balance == 0 ? $this->statuses[ 'Bill Payment' ][ 'Closed' ] : $this->statuses[ 'Bill Payment' ][ 'Partial' ] );

            Expenses::find( $expenses->id )->update( [ 'balance' => $balance, 'status' => $status ] );
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $bill[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Recovered ',
                'target_name' => 'Bill',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'open_balance' => $expenses[ 'balance' ],
                'memo' => $bill[ 'statement_memo' ]
            ] 
        );
    }
}
