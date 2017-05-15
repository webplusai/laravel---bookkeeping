<?php

namespace App\Http\Controllers\Misc;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Base\BaseController;

use DB;
use PDO;
use App;
use App\User;
use App\Models\Bill;
use App\Models\Sales;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Expenses;
use App\Models\AuditLog;
use App\Models\AuditLogItem;
use App\Models\BillPayment;
use App\Models\UserProfile;
use App\Models\ProductService;
use App\Models\AccountCategoryType;
use App\Models\AccountDetailType;
use App\Models\MapBillBillPayment;

use App\Helper\RestInputValidators;
use App\Helper\RestResponseMessages;
use App\Helper\StringConversionFunctions;

class MiscController extends BaseController
{
	public function __construct( Request $request ) {
		parent::__construct();

		$this->middleware( function( $request, $next ) {

			$validator = RestInputValidators::endPointIdValidator( $GLOBALS[ 'input' ] );

			if ( $validator->fails() ) {
				return RestResponseMessages::formValidationErrorMessage( $validator->errors()->all() );
			}

			$GLOBALS[ 'vltrName' ]     =   StringConversionFunctions::endPointIdToValidatorName( $GLOBALS[ 'input' ][ 'endPointId' ] );
			$GLOBALS[ 'validator' ]    =   RestInputValidators::$GLOBALS[ 'vltrName' ]( $GLOBALS[ 'input' ] );

			if ( $GLOBALS[ 'validator' ]->fails() ) {
				return RestResponseMessages::formValidationErrorMessage( $GLOBALS[ 'validator' ]->errors()->all() );
			}

			return $next( $request );
		} );
	}

	public function getInvoiceById( Request $request ) {
        $invoice = Sales::where( 'invoice_receipt_no', $request->input( 'invoiceId' ) )->first();
        $customerId = $invoice->customer_id;
        $customerInvoices = Sales::where( 'transaction_type', $this->transactionTypes[ 'Invoice' ] )
                                    ->where( 'customer_id', $customerId )->where( 'status', '!=', $this->statuses[ 'Invoice' ][ 'Paid' ] )
                                    ->where( 'is_trash', '!=', 1 )
                                    ->get();

        $creditNotes = Sales::where( 'transaction_type', $this->transactionTypes[ 'Credit Note' ] )
                                    ->where( 'customer_id', $customerId )->where( 'status', '!=', $this->statuses[ 'Credit Note' ][ 'Closed' ] )
                                    ->where( 'is_trash', '!=', 1 )
                                    ->get();

        foreach ( $customerInvoices as $customerInvoice ) {
            if ( $customerInvoice->invoice_receipt_no == $request->input( 'invoiceId' ) ) {
                $customerInvoice->checked = true;
                $customerInvoice->amount = $customerInvoice->balance;
                $invoice->amount_received = $customerInvoice->balance;
            }
        }
        foreach ( $creditNotes as $creditNote ) {
            $creditNote->amount = $creditNote->balance;
        }
        $invoice->customerInvoices = $customerInvoices;
        $invoice->creditNotes = $creditNotes;
        
		return RestResponseMessages::MiscSuccessMessage( 'Get Invoice By Id', $invoice, 200 );
	}

    public function getBillByExpenseId( Request $request ) {
        $expenses = Expenses::find( $request->input( 'expenseId' ) );
        $supplierId = $expenses->payee_id;
        $supplierBills = Expenses::where( 'transaction_type', $this->transactionTypes[ 'Bill' ] )
                                    ->where( 'payee_id', $supplierId )
                                    ->where( 'payee_type', $this->personTypes[ 'Supplier' ] )
                                    ->where( 'status', '!=', $this->statuses[ 'Bill' ][ 'Paid' ] )
                                    ->where( 'is_trash', '!=', 1 )
                                    ->get();

        $supplierCredits = Expenses::where( 'transaction_type', $this->transactionTypes[ 'Supplier Credit' ] )
                        ->where( 'payee_id', $supplierId )
                        ->where( 'payee_type', $this->personTypes[ 'Supplier' ] )
                        ->where( 'status', '!=', $this->statuses[ 'Supplier Credit' ][ 'Closed' ] )
                        ->where( 'is_trash', '!=', 1 )
                        ->get();

        foreach( $supplierBills as $supplierBill ) {
            if ( $supplierBill->id == $request->input( 'expenseId' ) ) {
                $supplierBill->checked = true;
                $supplierBill->amount = $supplierBill->balance;
                $expenses->total = $supplierBill->balance;
            }
        }
        $expenses->supplierBills = $supplierBills;
        $expenses->supplierCredits = $supplierCredits;
        return RestResponseMessages::MiscSuccessMessage( 'Get Bill By Expense Id', $expenses, 200 );
    }

    public function getInvoiceNumber( Request $request ) {
        if ( $request->input( 'salesId' ) != 'undefined' ) {
            return RestResponseMessages::MiscSuccessMessage( 'Invoice Id Retrieve', Sales::find( $request->input( 'salesId' ) )->invoice_receipt_no, 200 );
        } else {
            return RestResponseMessages::MiscSuccessMessage( 'Invoice Id Retrieve', Sales::max( 'invoice_receipt_no' ) + 1, 200 );
        }
    }

	public function getUserName( Request $request ) {
		return RestResponseMessages::MiscSuccessMessage( 'Get User Name', \Auth::user()->name, 200 );
	}

    public function massRetrieve( Request $request ) {

        $tableNames = json_decode( $request->input( 'tableNames' ) );
        $result = [];

        foreach( $tableNames as $tableName ) {
            $result = array_merge( $result, [ lcfirst( implode( '', array_map( 'ucfirst', explode( '_', $tableName ) ) ) ) => DB::table( $tableName )->where( 'is_trash', '!=', '1' )->get() ] );
        }

        return RestResponseMessages::MiscSuccessMessage( 'Mass Retrieve', $result, 200 );
    }

    public function massUpdate( Request $request ) {

        foreach ( $request->input( 'data' ) as $record ) {
            Account::find( $record[ 'id' ] )->update( $record );
        }

        return RestResponseMessages::MiscSuccessMessage( 'Account Mass Update', Account::all(), 200 );
    }

	public function retrieveAccountDetailTypeNames( Request $request ) {
		$accountCategoryTypeId = $this->account_category_type[ $request->input( 'accountCategoryType' ) ]->id;
		return RestResponseMessages::MiscSuccessMessage( 'Account Detail Type Retrieval', AccountDetailType::where( 'account_category_type_id', $accountCategoryTypeId )->pluck( 'name' ), 200 );
	}

    public function retrieveAccountNamesByCategoryType( Request $request ) {
        $accountCategoryTypes = AccountCategoryType::select( 'id', 'name' )->get();
        $result = [];
        foreach ( $accountCategoryTypes as $accountCategoryType ) {
            array_push( $result, [ 'value' => 0, 'label' => $accountCategoryType[ 'name' ] ] );
            $result = array_merge( $result, Account::where( 'account_category_type_id', $accountCategoryType->id )->select( DB::raw( 'name as value, name as label' ) )->get()->toArray() );
        }
        return RestResponseMessages::MiscSuccessMessage( 'Account Name By Category Type Retrieval', $result, 200 );
    }

    public function retrieveAuditLog( Request $request ) {
    
        $draw = $request->input( 'draw' );
        $start = $request->input( 'start' );
        $length = $request->input( 'length' );

        $collection = AuditLog::offset( $start )->limit( $length );
        if ( $request->input( 'tableId' ) )
            $collection = $collection->where( 'table_id', $request->input( 'tableId' ) );
        if ( $request->input( 'recordId' ) )
            $collection = $collection->where( 'record_id', $request->input( 'recordId' ) );

        $collection = $collection->get();

        $records = [];

        foreach ( $collection as $item ) {
           array_push( $records, 
                [ 
                    $item->table_id,
                    $item->record_id,
                    $item->trxn_id,
                    $item->date_changed, 
                    $item->id,
                    \Auth::user()->email,
                    $item->event_text,
                    $item->target_name,
                    $item->person_id,
                    $item->person_type,
                    $item->date,
                    $item->amount 
                ] 
            );
        }

        $totalRecordCount = count( AuditLog::all() );

        return response()->json( 
            [
                'draw' => $draw,
                'aaData' => $records,
                'iTotalRecords' => $totalRecordCount,
                'iTotalDisplayRecords' => $totalRecordCount
            ]
        );
    }

    public function retrieveAuditHistory( Request $request ) {

        $auditHistoryList = AuditLog::where( 'table_id', $request->input( 'trxnType' ) )
                                    ->where( 'record_id', $request->input( 'trxnId' ) )
                                    ->get();

        foreach ( $auditHistoryList as $auditHistory ) {
            $auditHistory[ 'auditLogItems' ] = AuditLogItem::where( 'audit_log_id', $auditHistory->id )->get();
        }

        return RestResponseMessages::MiscSuccessMessage( 'Audit History Retrieve', $auditHistoryList, 200 );
    }

    public function retrieveCustomerInvoicesAndCreditNotes( Request $request ) {

        $customerName = $request->input( 'customerName' );

        $customerInvoices   = Sales::where( 'transaction_type', $this->transactionTypes[ 'Invoice' ] )
                                    ->where( 'customer_id', $this->customer[ $customerName ]->id )
                                    ->where( 'status', '!=', $this->statuses[ 'Invoice' ][ 'Paid' ] )
                                    ->where( 'is_trash', '!=', 1 )
                                    ->get();

        $creditNotes        = Sales::where( 'transaction_type', $this->transactionTypes[ 'Credit Note' ] )
                                    ->where( 'customer_id', $this->customer[ $customerName ]->id )
                                    ->where( 'status', '!=', $this->statuses[ 'Credit Note' ][ 'Closed' ] )
                                    ->where( 'is_trash', '!=', 1 )
                                    ->get();
                                    
        return RestResponseMessages::MiscSuccessMessage( 'Customer Invoices Retrieve', [ 'customerInvoices' => $customerInvoices, 'creditNotes' => $creditNotes ], 200 );
    }

    public function retrieveSupplierBillsAndSupplierCredits( Request $request ) {

        $supplierName = $request->input( 'supplierName' );

        $supplierBills = Expenses::where( 'transaction_type', $this->transactionTypes[ 'Bill' ] )
                        ->where( 'payee_id', $this->supplier[ $supplierName ]->id )
                        ->where( 'payee_type', $this->personTypes[ 'Supplier' ] )
                        ->where( 'status', '!=', $this->statuses[ 'Bill' ][ 'Paid' ] )
                        ->where( 'is_trash', '!=',  1 )
                        ->get();

        $supplierCredits = Expenses::where( 'transaction_type', $this->transactionTypes[ 'Supplier Credit' ] )
                        ->where( 'payee_id', $this->supplier[ $supplierName ]->id )
                        ->where( 'payee_type', $this->personTypes[ 'Supplier' ] )
                        ->where( 'status', '!=', $this->statuses[ 'Supplier Credit' ][ 'Closed' ] )
                        ->where( 'is_trash', '!=', 1 )
                        ->get();

        return RestResponseMessages::MiscSuccessMessage( 'Supplier Bills Retrieve', [ 'supplierBills' => $supplierBills, 'supplierCredits' => $supplierCredits ], 200 );
    }

    public function setUserProfile( Request $request ) {

    	$bPasswordReset = false;

    	$input = $request->all();
    	UserProfile::find( 1 )->update( $input );

    	if ( isset( $input[ 'new_password' ] ) ) {
    		if ( !Hash::check( $input[ 'current_password' ], UserProfile::find( 1 )->password ) )
    			return RestResponseMessages::formValidationErrorMessage( [ 'Current password incorrect' ] );

    		DB::table( 'user_profile' )->where( 'id', 1 )->update( [ 'password' => Hash::make( $input[ 'new_password' ] ) ] );
    		$input[ 'password' ] = Hash::make( $input[ 'new_password' ] );
    		$bPasswordReset = true;
        }

    	configureDBConnectionByName( env( 'DB_CONNECTION', 'mysql' ) );
        App::make( 'config' )->set( 'database.default', env( 'DB_CONNECTION', 'mysql' ) );

        User::find( \Auth::user()->id )->update( [ 'name' => $input[ 'name' ], 'email' => $input[ 'email' ] ] );

        if ( $bPasswordReset )
        	User::find( \Auth::user()->id )->update( [ 'password' => $input[ 'password' ] ] );

        configureDBConnectionByName( $this->dbName );
        App::make( 'config' )->set( 'database.default', $this->dbName );

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'User Profile' ], 
                'record_id' => 1,
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Edited ',
                'target_name' => 'User Profile'
            ] 
        );

    	return RestResponseMessages::MiscSuccessMessage( 'Set User Profile', $input, 200 );
    }

    public function activateCustomer( $id, Request $request ) {

        $customer = Customer::find( $id );

        $unPaidOrPartialInvoiceBalance = Sales::where( 'customer_id', $id )
                                        ->where( 'transaction_type', $this->transactionTypes[ 'Invoice' ] )
                                        ->where( 'is_trash', '!=', 1 )
                                        ->where( function( $query ) {
                                            $query->where( 'status', $this->statuses[ 'Invoice' ][ 'Unpaid' ] )->orWhere( 'status', $this->statuses[ 'Invoice' ][ 'Partial' ] );
                                        } )
                                        ->sum( 'balance' );

        $unAppliedOrPartialPaymentBalance = Sales::where( 'customer_id', $id )
                                        ->where( 'transaction_type', $this->transactionTypes[ 'Payment' ] )
                                        ->where( 'is_trash', '!=', 1 )
                                        ->where( function( $query ) {
                                            $query->where( 'status', $this->statuses[ 'Payment' ][ 'Unapplied' ] )->orWhere( 'status', $this->statuses[ 'Payment' ][ 'Partial' ] );
                                        } )
                                        ->sum( 'balance' );

        $ch = curl_init();

        $header = array();
        $header[] = 'Authorization: ' . $request->header( 'Authorization' );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );

        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        if ( $unAppliedOrPartialPaymentBalance > 0 ) {

            $transaction = [
                'transaction_type' => $this->transactionTypes[ 'Invoice' ],
                'customer' => $customer->name,
                'total' => $unAppliedOrPartialPaymentBalance,
            ];
            $transaction[ 'date' ] = $transaction[ 'due_date' ] = date( 'Y-m-d' );

            $invoice = [
                'statement_memo' => 'Created by Sejllat to adjust balance for deletion',
                'sub_total' => $unAppliedOrPartialPaymentBalance,
                'discount_type_id' => $this->discountTypes[ 'No discount' ]
            ];
            $invoice[ 'discount_amount' ] = $invoice[ 'shipping' ] = $invoice[ 'deposit' ] = 0;

            $invoiceItems = [
                [
                    'product_service' => ProductService::all()[0]->name,
                    'description' => 'Created by Sejllat to adjust balane for deletion',
                    'amount' => $unAppliedOrPartialPaymentBalance,
                ]
            ];
            $invoiceItems[0][ 'rank' ] = $invoiceItems[0] [ 'item_type' ] = 1;
            $invoiceItems[0][ 'qty' ] = $invoiceItems[0][ 'rate' ] = 0;

            $trxn = [ 
                'transaction' => $transaction,
                'invoice' => $invoice,
                'invoiceItems' => $invoiceItems,
                'endPointId' => 'invoice'
            ];

            curl_setopt( $ch, CURLOPT_URL, 'http://localhost/sejllat/public/saudisms/whm/api/trxn/invoice' );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $trxn ) );
            $result = curl_exec ( $ch );
            curl_close ($ch);
        } 

        if ( $unPaidOrPartialInvoiceBalance > 0 ) {

            $transaction = [
                'transaction_type' => $this->transactionTypes[ 'Credit Note' ],
                'customer' => $customer->name,
                'total' => $unPaidOrPartialInvoiceBalance
            ];
            $transaction[ 'date' ] = $transaction[ 'due_date' ] = date( 'Y-m-d' );

            $creditNote = [
                'statement_memo' => 'Created by Sejllat to adjust balance for deletion',
                'sub_total' => $unPaidOrPartialInvoiceBalance,
                'discount_type_id' => $this->discountTypes[ 'No discount' ]
            ];
            $creditNote[ 'discount_amount' ] = $creditNote[ 'shipping' ] = $creditNote[ 'deposit' ] = 0;

            $creditNoteItems = [
                [
                    'product_service' => ProductService::all()[0]->name,
                    'description' => 'Created by Sejllat to adjust balane for deletion',
                    'amount' => $unPaidOrPartialInvoiceBalance,
                ]
            ];
            $creditNoteItems[0][ 'rank' ] = $creditNoteItems[0] [ 'item_type' ] = 1;
            $creditNoteItems[0][ 'qty' ] = $creditNoteItems[0][ 'rate' ] = 0;

            $trxn = [ 
                'transaction' => $transaction,
                'creditNote' => $creditNote,
                'creditNoteItems' => $creditNoteItems,
                'endPointId' => 'credit_note'
            ];

            curl_setopt( $ch, CURLOPT_URL, 'http://localhost/sejllat/public/saudisms/whm/api/trxn/credit_note' );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $trxn ) );
            $result = curl_exec ( $ch );
            curl_close ($ch);
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Customer' ], 
                'record_id' => $id,
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Edited ',
                'target_name' => 'Customer'
            ] 
        );

        return RestResponseMessages::MiscSuccessMessage( 'Customer Deactive', Customer::all(), 200 );
        
    }

    public function activateSupplier( $id, Request $request ) {

        $payee = Supplier::find( $id );

        $unPaidOrPartialBillBalance = Expenses::where( 'payee_id', $id )
                                                ->where( 'transaction_type', $this->transactionTypes[ 'Bill' ] )
                                                ->where( 'is_trash', '!=', 1 )
                                                ->where( 'status', '!=', $this->statuses[ 'Bill' ][ 'Paid' ] )
                                                ->sum( 'balance');

        $unAppliedOrPartialBillPaymentBalance = Expenses::where( 'payee_id', $id )
                                                        ->where( 'transaction_type', $this->transactionTypes[ 'Bill Payment' ] )
                                                        ->where( 'is_trash', '!=', 1 )
                                                        ->where( 'status', '!=', $this->statuses[ 'Bill Payment' ][ 'Closed' ] )
                                                        ->sum( 'balance' );
        
        $ch = curl_init();

        $header = array();
        $header[] = 'Authorization: ' . $request->header( 'Authorization' );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );

        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        if ( $unAppliedOrPartialBillPaymentBalance > 0 ) {
            $transaction = [
                'transaction_type' => $this->transactionTypes[ 'Bill' ],
                'supplier' => $payee->name,
                'total' => $unAppliedOrPartialBillPaymentBalance,
            ];
            $transaction[ 'date' ] = $transaction[ 'due_date' ] = date( 'Y-m-d' );

            $bill = [
                'statement_memo' => 'Created by Sejllat to adjust balance for deletion',
            ];

            $billItems = [
                [
                    'rank' => 1,
                    'product_service' => '',
                ]
            ];

            $billAccounts = [
                [
                    'rank' => 1,
                    'account' => Account::all()[0]->name,
                    'description' => 'Created by QB Online to adjust balance for deletion',
                    'amount' => $unAppliedOrPartialBillPaymentBalance
                ]
            ];

            $trxn = [ 
                'transaction' => $transaction,
                'bill' => $bill,
                'billItems' => $billItems,
                'billAccounts' => $billAccounts,
                'endPointId' => 'bill'
            ];

            curl_setopt( $ch, CURLOPT_URL, 'http://localhost/sejllat/public/saudisms/whm/api/trxn/bill' );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $trxn ) );
            $result = json_decode( curl_exec ( $ch ) );
            curl_close ($ch);

            $bill = (array) $result->content;
            $unAppliedOrPartialBillPaymentIds = Expenses::where( 'payee_id', $id )
                                                        ->where( 'transaction_type', $this->transactionTypes[ 'Bill Payment' ] )
                                                        ->where( 'is_trash', '!=', 1 )
                                                        ->where( 'status', '!=', $this->statuses[ 'Bill Payment' ][ 'Closed' ] )
                                                        ->pluck( 'id' );

            Expenses::find( $bill[ 'id' ] )->update( [ 'status' => $this->statuses[ 'Bill' ][ 'Paid' ], 'balance' => 0 ] );
            foreach ( $unAppliedOrPartialBillPaymentIds as $billPaymentId ) {
                Expenses::find( $billPaymentId )->update( [ 'status' => $this->statuses[ 'Bill Payment' ][ 'Closed' ], 'balance' => 0 ] );
                MapBillBillPayment::create(
                    [
                        'bill_id' => Bill::where( 'expenses_id', $bill[ 'id' ] )->first()->id,
                        'bill_payment_id' => BillPayment::where( 'expenses_id', $billPaymentId )->first()->id,
                        'payment' => $unAppliedOrPartialBillPaymentBalance
                    ]
                );
            }
        }

        if ( $unPaidOrPartialBillBalance > 0 ) {
            $transaction = [
                'transaction_type' => $this->transactionTypes[ 'Supplier Credit' ],
                'supplier' => $payee->name,
                'total' => $unPaidOrPartialBillBalance,
                'status' => $this->statuses[ 'Supplier Credit' ][ 'Unapplied' ]
            ];
            $transaction[ 'date' ] = $transaction[ 'due_date' ] = date( 'Y-m-d' );

            $supplierCredit = [
                'statement_memo' => 'Created by Sejllat to adjust balance for deletion',
                'sub_total' => $unPaidOrPartialBillBalance,
            ];

            $supplierCreditAccounts = [
                [
                    'rank' => 1,
                    'account' => Account::all()[0]->name,
                    'description' => 'Created by Sejllat to adjust balance for deletion',
                    'amount' => $unPaidOrPartialBillBalance,
                ]
            ];
            $creditNoteItems[0][ 'rank' ] = $creditNoteItems[0] [ 'item_type' ] = 1;
            $creditNoteItems[0][ 'qty' ] = $creditNoteItems[0][ 'rate' ] = 0;

            $supplierCreditItems = [
                [
                    'rank' => 1,
                    'product_service' => ''
                ]
            ];

            $trxn = [ 
                'transaction' => $transaction,
                'supplierCredit' => $supplierCredit,
                'supplierCreditItems' => $supplierCreditItems,
                'supplierCreditAccounts' => $supplierCreditAccounts,
                'endPointId' => 'supplier_credit'
            ];

            curl_setopt( $ch, CURLOPT_URL, 'http://localhost/sejllat/public/saudisms/whm/api/trxn/supplier_credit' );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $trxn ) );
            $result = (array)( json_decode( curl_exec ( $ch ) ) ) ;
            curl_close ($ch);



            $ch = curl_init();

            $header = array();
            $header[] = 'Authorization: ' . $request->header( 'Authorization' );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );

            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

            $transaction = [
                'transaction_type' => $this->transactionTypes[ 'Bill Payment' ],
                'supplier' => $payee->name,
                'total' => 0
            ];
            $transaction[ 'date' ] = $transaction[ 'due_date' ] = date( 'Y-m-d' );

            $billPayment = [
                'account' => Account::all()[0]->name,
                'note' => 'Created by Sejllat to link supplier credits to bills.'
            ];

            $unPaidOrPartialBills = Expenses::where( 'payee_id', $id )
                                                    ->where( 'transaction_type', $this->transactionTypes[ 'Bill' ] )
                                                    ->where( 'is_trash', '!=', 1 )
                                                    ->where( 'status', '!=', $this->statuses[ 'Bill'][ 'Paid' ] )
                                                    ->selectRaw( 'id as id, total as amount, balance as balance, balance as payment' )
                                                    ->get();
            $transaction[ 'supplierBills' ] = [];
            foreach ( $unPaidOrPartialBills as $bill ) {
                array_push( $transaction[ 'supplierBills' ], [ 'checked' => true, 'id' => $bill->id, 'amount' => $bill->amount, 'balance' => $bill->balance, 'payment' => $bill->payment ] );
            }

            $supplierCredit = (array) $result[ 'content' ];
            $supplierCredit[ 'checked' ] = true;
            $supplierCredit[ 'amount' ] = $supplierCredit[ 'balance' ] = $supplierCredit[ 'payment' ] = $supplierCredit[ 'total' ];
            $transaction[ 'supplierCredits' ] = [ $supplierCredit ];

            $trxn = [ 
                'transaction' => $transaction,
                'billPayment' => $billPayment,
                'endPointId' => 'bill_payment'
            ];

            curl_setopt( $ch, CURLOPT_URL, 'http://localhost/sejllat/public/saudisms/whm/api/trxn/bill_payment' );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $trxn ) );
            $result = curl_exec ( $ch );
            curl_close ($ch);
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Supplier' ], 
                'record_id' => $id,
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Edited ',
                'target_name' => 'Supplier'
            ] 
        );

        return RestResponseMessages::MiscSuccessMessage( 'Supplier Deactive', Supplier::all(), 200 );
    }
}
