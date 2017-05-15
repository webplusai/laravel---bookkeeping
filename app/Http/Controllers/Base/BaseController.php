<?php

namespace App\Http\Controllers\Base;

use DB;
use App;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\AuditLog;
use App\Models\AuditLogItem;
use App\Models\ProductService;

class BaseController extends Controller
{
    public $tableNames = [ 'account', 'customer', 'supplier', 'product_service', 'product_category', 'account_detail_type', 'account_category_type' ];
    public $tableIdsForAudit = [ 'User' => 1, 'Customer' => 2, 'Supplier' => 3, 'Company Profile' => 4, 'Account' => 5, 'Product Service' => 6, 'Journal Entry' => 7, 'Product Category' => 8, 'User Profile' => 9, 'Sales' => 10, 'Expenses' => 11, 'Attachment' => 12 ];

    public $personTypes         =   [   'Customer'          =>      1,      'Supplier'          =>      2   ];
    public $discountTypes       =   [   'Discount percent'  =>      1,      'Discount value'    =>      2,      'No discount'       =>      3 ];
    public $transactionTypes    =   [   
                                        'Invoice'           =>      1,      'Payment'           =>      2,      'Sales Receipt'     =>      3,      'Credit Note'       =>      4,
                                        'Expense'           =>      101,    'Bill'              =>      102,    'Bill Payment'      =>      103,    'Supplier Credit'   =>      104,
                                        'Journal Entry'     =>      201 
                                    ];
    public $statuses = 
        [ 
            'Invoice'           =>  [   'Unpaid'            =>      1,      'Partial'           =>      2,      'Paid'              =>      3   ],
            'Payment'           =>  [   'Unapplied'         =>      1,      'Partial'           =>      2,      'Closed'            =>      3   ],
            'Credit Note'       =>  [   'Unapplied'         =>      1,      'Partial'           =>      2,      'Closed'            =>      3   ],
            'Sales Receipt'     =>  [   'Paid'              =>      1   ],
            'Expense'           =>  [   'Paid'              =>      1   ],
            'Bill'              =>  [   'Unpaid'            =>      1,      'Partial'           =>      2,      'Paid'              =>      3   ],
            'Bill Payment'      =>  [   'Unapplied'         =>      1,      'Partial'           =>      2,      'Closed'            =>      3   ],
            'Supplier Credit'   =>  [   'Unapplied'         =>      1,      'Partial'           =>      2,      'Closed'            =>      3   ],
        ];

    public function __construct() {
    	$this->middleware( function( $request, $next ) {

            $this->dbName = \Auth::user()->db_name;

            configureDBConnectionByName( $this->dbName );
            App::make( 'config' )->set( 'database.default', $this->dbName );

            $this->payee = DB::table( 'customer' )
                ->select( DB::raw( '1 as type' ), 'id', 'name' )
                ->union( DB::table( 'supplier' )->select( DB::raw( '2 as type' ), 'id', 'name' ) )
                ->get()->keyBy( 'name' );
                
            foreach ( $this->tableNames as $name ) {
                if ( $name == 'product_service' )
                    $this->$name = DB::table( $name )->get( [ 'id', 'name', 'is_inventoriable' ] )->keyBy( 'name' );
                else
                    $this->$name = DB::table( $name )->get( [ 'id', 'name' ] )->keyBy( 'name' );
            }

            $GLOBALS[ 'input' ]                         =       $request->all();
            $GLOBALS[ 'input' ][ 'REQUEST_METHOD' ]     =       $request->method();

            if ( isset( $GLOBALS[ 'input' ][ 'foreign_keys' ] ) ) {
                $foreign_keys = $GLOBALS[ 'input' ][ 'foreign_keys' ];
                foreach ( $foreign_keys as $key ) {
                    $list = $this->$key;
                    $GLOBALS[ 'input' ][ $key . '_id' ] = $list[ $GLOBALS[ 'input' ][ $key ] ]->id;
                    if ( $key == 'payee' ) {
                        $GLOBALS[ 'input' ][ $key . '_type' ] = $list[ $GLOBALS[ 'input' ][ $key ] ]->type;
                    }
                }
            }

    		return $next( $request );
    	} );
    }

    public function createAuditLog( $data ) {
        $auditLog = AuditLog::create( $data );

        $itemNo = 0;

        if ( $data[ 'target_name' ] == 'Invoice' ) {
            AuditLogItem::create( [ 'audit_log_id' => $auditLog->id, 'no' => $itemNo, 'customer_id' => $data[ 'person_id' ], 'account_id' => $this->account[ 'Accounts Receivable' ]->id, 'amount' => $data[ 'amount' ], 'open_balance' => $data[ 'open_balance' ] ] );
        }
        else if ( $data[ 'target_name' ] == 'Payment' ) {
            AuditLogItem::create( [ 'audit_log_id' => $auditLog->id, 'no' => $itemNo, 'customer_id' => $data[ 'person_id' ], 'account_id' => $this->account[ 'Cash' ]->id, 'amount' => $data[ 'amount' ], 'open_balance' => $data[ 'open_balance' ] ] );
        }
        else if ( $data[ 'target_name' ] == 'Sales Receipt' ) {
            AuditLogItem::create( [ 'audit_log_id' => $auditLog->id, 'no' => $itemNo, 'customer_id' => $data[ 'person_id' ], 'account_id' => $this->account[ 'Cash' ]->id, 'amount' => $data[ 'amount' ] ] );
        }
        else if ( $data[ 'target_name' ] == 'Credit Note' ) {
            AuditLogItem::create( [ 'audit_log_id' => $auditLog->id, 'no' => $itemNo, 'customer_id' => $data[ 'person_id' ], 'account_id' => $this->account[ 'Accounts Receivable' ]->id, 'amount' => -$data[ 'amount' ], 'open_balance' => -$data[ 'open_balance' ] ] );
        }
        else if ( $data[ 'target_name' ] == 'Expense' ) {
            $auditLogItem = [ 'audit_log_id' => $auditLog->id, 'no' => $itemNo, 'account_id' => $this->account[ 'Cash' ]->id, 'amount' => -$data[ 'amount' ] ];
            if ( $data[ 'person_type' ] == $this->personTypes[ 'Customer' ] )
                $auditLogItem[ 'customer_id' ] = $data[ 'person_id' ];
            else
                $auditLogItem[ 'supplier_id' ] = $data[ 'person_id' ];
            AuditLogItem::create( [ 'audit_log_id' => $auditLog->id, 'no' => $itemNo, 'account_id' => $this->account[ 'Cash' ]->id, 'amount' => -$data[ 'amount' ] ] );
        }
        else if ( $data[ 'target_name' ] == 'Bill' ) {
            AuditLogItem::create( [ 'audit_log_id' => $auditLog->id, 'no' => $itemNo, 'supplier_id' => $data[ 'person_id' ], 'account_id' => $this->account[ 'Accounts Payable' ]->id, 'amount' => $data[ 'amount' ], 'open_balance' => $data[ 'open_balance' ] ] );
        }
        else if ( $data[ 'target_name' ] == 'Bill Payment' ) {
            AuditLogItem::create( [ 'audit_log_id' => $auditLog->id, 'no' => $itemNo, 'supplier_id' => $data[ 'person_id' ], 'account_id' => $this->account[ 'Cash' ]->id, 'amount' => -$data[ 'amount' ], 'open_balance' => -$data[ 'open_balance' ] ] );
        }
        else if ( $data[ 'target_name' ] == 'Supplier Credit' ) {
            AuditLogItem::create( [ 'audit_log_id' => $auditLog->id, 'no' => $itemNo, 'supplier_id' => $data[ 'person_id' ], 'account_id' => $this->account[ 'Accounts Payable' ]->id, 'amount' => -$data[ 'amount' ], 'open_balance' => -$data[ 'open_balance' ] ] );
        }

        $itemNo ++;
        if ( isset( $data[ 'items' ] ) ) {
            $items = $data[ 'items' ];
            foreach( $items as $item ) {
                if ( !isset( $item[ 'product_service' ] ) && !isset( $item[ 'account' ] ) )
                    continue;
                $item[ 'audit_log_id' ] = $auditLog->id;
                $item[ 'no' ] = $itemNo ++;
                if ( $data[ 'person_type' ] == $this->personTypes[ 'Customer' ] )
                    $item[ 'customer_id' ] = $data[ 'person_id' ];
                else
                    $item[ 'supplier_id' ] = $data[ 'person_id' ];

                if ( isset( $item[ 'product_service' ] ) ) {
                    $item[ 'product_service_id' ] = $this->product_service[ $item[ 'product_service' ] ]->id;
                    if ( $data[ 'target_name' ] == 'Invoice' || $data[ 'target_name' ] == 'Sales Receipt' || $data[ 'target_name' ] == 'Credit Note' ) {
                        $item[ 'account_id' ] = $this->account[ 'Sales Revenues' ]->id;
                    }
                    else if ( $data[ 'target_name' ] == 'Expense' || $data[ 'target_name' ] == 'Bill' || $data[ 'target_name' ] == 'Supplier Credit' ) {
                        $productService = ProductService::find( $this->product_service[ $item[ 'product_service' ] ]->id );
                        if ( $productService->is_inventoriable )
                            $item[ 'account_id' ] = $this->account[ 'Merchandise Inventory' ]->id;
                        else
                            $item[ 'account_id' ] = $this->account[ 'Purchases' ]->id;
                    }

                } else if ( isset( $item[ 'account' ] ) ) {
                    $item[ 'account_id' ] = $this->account[ $item[ 'account' ] ]->id;
                }
                
                if ( isset( $item[ 'item_type' ] ) && $item[ 'item_type' ] == 2 ) {
                    $item[ 'description' ] = 'SubTotal';
                }

                AuditLogItem::create( $item );
            }
        }
    }
}
