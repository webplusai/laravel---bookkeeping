<?php

namespace App\Http\Controllers\TRXN;

use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Expenses;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\ExpenseItem;
use App\Models\ExpenseAccount;
use App\Models\ProductService;
use App\Models\AccountCategoryType;
use App\Models\MapExpenseAttachment;

use App\Helper\RestResponseMessages;

class ExpenseController extends TRXNController
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
        $additionalTables = [ 'expense_account', 'expense_item', 'attachment' ];

        $expenses            =  $GLOBALS[ 'input' ][ 'transaction' ];
        $expense             =  $GLOBALS[ 'input' ][ 'expense' ];
        $expenseItems        =  $GLOBALS[ 'input' ][ 'expenseItems' ];
        $expenseAccounts     =  $GLOBALS[ 'input' ][ 'expenseAccounts' ];

        $expenses[ 'payee_id' ] = $this->payee[ $expenses[ 'customer' ] ]->id;
        $expenses[ 'payee_type' ] = $this->payee[ $expenses[ 'customer' ] ]->type;

        $accountCount = 0;
        foreach ( $expenseAccounts as $expenseAccount ) {
            if ( isset( $expenseAccount[ 'account' ] ) )
                $accountCount ++;
        }

        $itemCount = 0;
        foreach ( $expenseItems as $expenseItem ) {
            if ( isset( $expenseItem[ 'product_service' ] ) )
                $itemCount ++;
        }

        if ( $accountCount + $itemCount > 1 )
            $expenses[ 'account_id' ] = 0;
        else {
            if ( $accountCount == 1 )
                $expenses[ 'account_id' ] = $this->account[ $expenseAccounts[0][ 'account' ] ]->id;
            else {
                if ( $this->product_service[ $expenseItems[0][ 'product_service' ] ]->is_inventoriable )
                    $expenses[ 'account_id' ] = $this->account[ 'Merchandise Inventory' ]->id;
                else
                    $expenses[ 'account_id' ] = $this->account[ 'Purchases' ]->id;
            }

        }

        $expenses = Expenses::create( $expenses );

        $expense[ 'expenses_id' ] = $expenses->id;
        $expense = Expense::create( $expense );

        $totalPurchases = 0;
        $totalMerchandiseInventory = 0;
        foreach ( $expenseItems as $expenseItem ) {
            if ( isset( $expenseItem[ 'product_service' ] ) ) {
                $productService = $this->product_service[ $expenseItem[ 'product_service' ] ];
                $expenseItem[ 'expense_id' ] = $expense->id;
                $expenseItem[ 'product_service_id' ] = $productService->id;
                ExpenseItem::create( $expenseItem );

                if ( $productService->is_inventoriable )
                    $totalMerchandiseInventory +=  ProductService::find( $expenseItem[ 'product_service_id' ] )->purchase_price * $expenseItem[ 'qty' ];
                else
                    $totalPurchases += ProductService::find( $expenseItem[ 'product_service_id' ] )->purchase_price * $expenseItem[ 'qty' ];;
            }
        }

        foreach ( $expenseAccounts as $expenseAccount ) {
            if ( isset( $expenseAccount[ 'account' ] ) ) {
                $expenseAccount[ 'expense_id' ] = $expense->id;
                $expenseAccount[ 'account_id' ] = $this->account[ $expenseAccount[ 'account' ] ]->id;
                ExpenseAccount::create( $expenseAccount );

                $account = Account::find( $expenseAccount[ 'account_id' ] );
                $accountCategoryName = AccountCategoryType::find( $account->account_category_type_id )->name;

                $increaseAccounts = [ 'Current Asset', 'Non-Current Asset', 'Operating Expense', 'Non-Operating Expenses and Loss', 'Cost of Sales/Services' ];
                $decreaseAccounts = [ 'Current Liability', 'Non-Current Liability', 'Operating Revenue', 'Non-Operating Revenues and Gains', 'Owner\'s Equity' ];

                if ( in_array( $accountCategoryName, $increaseAccounts ) ) {
                    $account->update( [ 'balance' => $account->balance + $expenseAccount[ 'amount' ] ] );
                } else if ( in_array( $accountCategoryName, $decreaseAccounts ) ) {
                    $account->update( [ 'balance' => $account->balance - $expenseAccount[ 'amount' ] ] );
                }
            }   
        }

        if ( isset( $GLOBALS[ 'input' ][ 'attachments' ]) ) {
            $attachments = $GLOBALS[ 'input' ][ 'attachments' ];
            foreach ( $attachments as $attachment ) {
                $attachment = Attachment::create( $attachment );
                MapExpenseAttachment::create( [ 'expense' => $expense->id, 'attachment_id' => $attachment->id ] );
            }
        }

        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses[ 'total' ] ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalMerchandiseInventory ] );

        $accountId = $this->account[ 'Purchases' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalPurchases ] );

        $items = array_merge( $expenseItems, $expenseAccounts );
        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $expense[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Added ',
                'target_name' => 'Expense',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'memo' => $expense[ 'statement_memo' ],
                'items' => $items
            ] 
        );

        return RestResponseMessages::MiscSuccessMessage( 'Create Expense', $expenses, 201 );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( $id )
    {
        $expenses           =   Expenses::find( $id );
        $expense            =   Expense::where( 'expenses_id', $expenses->id )->first();
        $expenseItems       =   ExpenseItem::where( 'expense_id', $expense->id )->get();
        $expenseAccounts    =   ExpenseAccount::where( 'expense_id', $expense->id )->get();

        if ( $expenses->payee_type == 1 ) {
            $expenses->customer = Customer::find( $expenses->payee_id )->name;
        } else if ( $expenses->payee_type == 2 ) {
            $expenses->customer = Supplier::find( $expenses->payee_id )->name;
        }

        foreach ( $expenseItems as $expenseItem ) {
            $expenseItem->product_service = ProductService::find( $expenseItem->product_service_id )->name;
        }

        foreach ( $expenseAccounts as $expenseAccount ) {
            $expenseAccount->account = Account::find( $expenseAccount->account_id )->name;
        }

        return RestResponseMessages::TRXNSuccessMessage( 'Expense Retrieval', [ 'transaction' => $expenses, 'expense'=> $expense, 'expenseItems' => $expenseItems, 'expenseAccounts' => $expenseAccounts ], 200 );
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

        $additionalTables = [ 'expense_account', 'expense_item', 'attachment' ];

        $expenses           = $GLOBALS[ 'input' ][ 'transaction' ];
        $expense            = $GLOBALS[ 'input' ][ 'expense' ];
        $expenseItems       = $GLOBALS[ 'input' ][ 'expenseItems' ];
        $expenseAccounts    = $GLOBALS[ 'input' ][ 'expenseAccounts' ];

        $expenses[ 'payee_id' ] = $this->payee[ $expenses[ 'customer' ] ]->id;
        $expenses[ 'payee_type' ] = $this->payee[ $expenses[ 'customer' ] ]->type;

        $accountCount = 0;
        foreach ( $expenseAccounts as $expenseAccount ) {
            if ( isset( $expenseAccount[ 'account' ] ) )
                $accountCount ++;
        }

        $itemCount = 0;
        foreach ( $expenseItems as $expenseItem ) {
            if ( isset( $expenseItem[ 'product_service' ] ) )
                $itemCount ++;
        }

        if ( $accountCount + $itemCount > 1 )
            $expenses[ 'account_id' ] = 0;
        else {
            if ( $accountCount == 1 )
                $expenses[ 'account_id' ] = $this->account[ $expenseAccounts[0][ 'account' ] ]->id;
            else {
                if ( $this->product_service[ $expenseItems[0][ 'product_service' ] ]->is_inventoriable )
                    $expenses[ 'account_id' ] = $this->account[ 'Merchandise Inventory' ]->id;
                else
                    $expenses[ 'account_id' ] = $this->account[ 'Purchases' ]->id;
            }

        }
        
        $expenses[ 'account_id' ] = $accountCount == 1 ? $this->account[ $expenseAccounts[0][ 'account' ] ]->id : 0;

        $orgExpenses = Expenses::find( $expenses[ 'id' ] );
        Expenses::find( $expenses[ 'id' ] )->update( $expenses );

        Expense::find( $expense[ 'id' ] )->update( $expense );

        $totalPurchases = 0;
        $totalOrgPurchases = 0;
        $totalMerchandiseInventory = 0;
        $totalOrgMerchandiseInventory = 0;
        foreach ( $expenseItems as $expenseItem ) {
            if ( isset( $expenseItem[ 'product_service' ] ) ) {
                $productService = $this->product_service[ $expenseItem[ 'product_service' ] ];
                $expenseItem[ 'expense_id' ] = $expense[ 'id' ];
                $expenseItem[ 'product_service_id' ] = $productService->id;

                if ( $productService->is_inventoriable )
                    $totalMerchandiseInventory +=  ProductService::find( $expenseItem[ 'product_service_id' ] )->purchase_price * $expenseItem[ 'qty' ];
                else
                    $totalPurchases += ProductService::find( $expenseItem[ 'product_service_id' ] )->purchase_price * $expenseItem[ 'qty' ];

                if ( isset( $expenseItem[ 'id' ] ) ) {
                    if ( $productService->is_inventoriable )
                        $totalOrgMerchandiseInventory += ExpenseItem::find( $expenseItem[ 'id' ] )->amount;
                    else
                        $totalOrgPurchases += ExpenseItem::find( $expenseItem[ 'id' ] )->amount;
                    ExpenseItem::find( $expenseItem[ 'id' ] )->update( $expenseItem );
                }
                else {
                    ExpenseItem::create( $expenseItem );
                }
            }
        }

        foreach ( $expenseAccounts as $expenseAccount ) {
            if ( isset( $expenseAccount[ 'account' ] ) ) {
                $expenseAccount[ 'expense_id' ] = $expense[ 'id' ];
                $expenseAccount[ 'account_id' ] = $this->account[ $expenseAccount[ 'account' ] ]->id;

                if ( isset( $expenseAccount[ 'id' ] ) ) {
                    $orgExpenseAccountAmount = ExpenseAccount::find( $expenseAccount[ 'id' ] )->amount;
                    ExpenseAccount::find( $expenseAccount[ 'id' ] )->update( $expenseAccount );
                }
                else {
                    ExpenseAccount::create( $expenseAccount );
                    $orgExpenseAccountAmount = 0;
                }

                $account = Account::find( $expenseAccount[ 'account_id' ] );
                $accountCategoryName = AccountCategoryType::find( $account->account_category_type_id )->name;

                $increaseAccounts = [ 'Current Asset', 'Non-Current Asset', 'Operating Expense', 'Non-Operating Expenses and Loss', 'Cost of Sales/Services' ];
                $decreaseAccounts = [ 'Current Liability', 'Non-Current Liability', 'Operating Revenue', 'Non-Operating Revenues and Gains', 'Owner\'s Equity' ];

                if ( in_array( $accountCategoryName, $increaseAccounts ) ) {
                    $account->update( [ 'balance' => $account->balance + $expenseAccount[ 'amount' ] - $orgExpenseAccountAmount ] );
                } else if ( in_array( $accountCategoryName, $decreaseAccounts ) ) {
                    $account->update( [ 'balance' => $account->balance - $expenseAccount[ 'amount' ] + $orgExpenseAccountAmount ] );
                }
            }   
        }

        if ( isset( $GLOBALS[ 'input' ][ 'attachments' ]) ) {
            $attachments = $GLOBALS[ 'input' ][ 'attachments' ];
            foreach ( $attachments as $attachment ) {
                $attachment = Attachment::create( $attachment );
                MapExpenseAttachment::create( [ 'expense' => $expense->id, 'attachment_id' => $attachment->id ] );
            }
        }

        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses[ 'total' ] + $orgExpenses->total ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalMerchandiseInventory - $totalOrgMerchandiseInventory ] );

        $accountId = $this->account[ 'Purchases' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalPurchases - $totalOrgPurchases ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $expense[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Edited ',
                'target_name' => 'Expense',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'memo' => $expense[ 'statement_memo' ],
                'items' => array_merge( $expenseItems, $expenseAccounts )
            ] 
        );

        return RestResponseMessages::MiscSuccessMessage( 'Update Expense', $expenses, 201 );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( Request $request, $id )
    {
        $expenses = Expenses::find( $id );
        $expenses->update( [ 'is_trash'=> 1 ] );

        $expense = Expense::where( 'expenses_id', $expenses->id )->first();
        $expenseItems = ExpenseItem::where( 'expense_id', $expense->id )->get();
        $expenseAccounts = ExpenseAccount::where( 'expense_id', $expense->id )->get();

        $totalPurchases = 0;
        $totalMerchandiseInventory = 0;
        foreach ( $expenseItems as $expenseItem ) {
            $productService = ProductService::find( $expenseItem[ 'product_service_id' ] );
            if ( $productService->is_inventoriable)
                $totalMerchandiseInventory +=  $productService->purchase_price * $expenseItem[ 'qty' ];
            else
                $totalPurchases += $productService->purchase_price * $expenseItem[ 'qty' ];
        }

        foreach ( $expenseAccounts as $expenseAccount ) {
            $account = Account::find( $expenseAccount[ 'account_id' ] );
            $accountCategoryName = AccountCategoryType::find( $account->account_category_type_id )->name;

            $increaseAccounts = [ 'Current Asset', 'Non-Current Asset', 'Operating Expense', 'Non-Operating Expenses and Loss', 'Cost of Sales/Services' ];
            $decreaseAccounts = [ 'Current Liability', 'Non-Current Liability', 'Operating Revenue', 'Non-Operating Revenues and Gains', 'Owner\'s Equity' ];

            if ( in_array( $accountCategoryName, $increaseAccounts ) ) {
                $account->update( [ 'balance' => $account->balance - $expenseAccount[ 'amount' ] ] );
            } else if ( in_array( $accountCategoryName, $decreaseAccounts ) ) {
                $account->update( [ 'balance' => $account->balance + $expenseAccount[ 'amount' ] ] );
            }
        }

        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $expenses->total ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $totalMerchandiseInventory ] );

        $accountId = $this->account[ 'Purchases' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $totalPurchases ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $expense[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Deleted ',
                'target_name' => 'Expense',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'memo' => $expense[ 'statement_memo' ]
            ] 
        );
    }

    public function recoverDelete( Request $request, $id ) {
        $expenses = Expenses::find( $id );
        $expenses->update( [ 'is_trash'=> 0 ] );

        $expense = Expense::where( 'expenses_id', $expenses->id )->first();
        $expenseItems = ExpenseItem::where( 'expense_id', $expense->id )->get();
        $expenseAccounts = ExpenseAccount::where( 'expense_id', $expense->id )->get();

        $totalPurchases = 0;
        $totalMerchandiseInventory = 0;
        foreach ( $expenseItems as $expenseItem ) {
            $productService = ProductService::find( $expenseItem[ 'product_service_id' ] );
            if ( $productService->is_inventoriable )
                $totalMerchandiseInventory +=  $productService->purchase_price * $expenseItem[ 'qty' ];
            else
                $totalPurchases += $productService->purchase_price * $expenseItem[ 'qty' ];
        }

        foreach ( $expenseAccounts as $expenseAccount ) {
            $account = Account::find( $expenseAccount[ 'account_id' ] );
            $accountCategoryName = AccountCategoryType::find( $account->account_category_type_id )->name;

            $increaseAccounts = [ 'Current Asset', 'Non-Current Asset', 'Operating Expense', 'Non-Operating Expenses and Loss', 'Cost of Sales/Services' ];
            $decreaseAccounts = [ 'Current Liability', 'Non-Current Liability', 'Operating Revenue', 'Non-Operating Revenues and Gains', 'Owner\'s Equity' ];

            if ( in_array( $accountCategoryName, $increaseAccounts ) ) {
                $account->update( [ 'balance' => $account->balance + $expenseAccount[ 'amount' ] ] );
            } else if ( in_array( $accountCategoryName, $decreaseAccounts ) ) {
                $account->update( [ 'balance' => $account->balance - $expenseAccount[ 'amount' ] ] );
            }
        }

        $accountId = $this->account[ 'Cash' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance - $expenses->total ] );

        $accountId = $this->account[ 'Merchandise Inventory' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalMerchandiseInventory ] );

        $accountId = $this->account[ 'Purchases' ]->id;
        Account::find( $accountId )->update( [ 'balance' => Account::find( $accountId )->balance + $totalPurchases ] );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Expenses' ], 
                'record_id' => $expenses[ 'id' ],
                'trxn_id' => $expense[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Recovered ',
                'target_name' => 'Expense',
                'person_id' => $expenses[ 'payee_id' ],
                'person_type' => $expenses[ 'payee_type' ],
                'date' => $expenses[ 'date' ],
                'amount' => $expenses[ 'total' ],
                'memo' => $expense[ 'statement_memo' ]
            ] 
        );
    }

}
