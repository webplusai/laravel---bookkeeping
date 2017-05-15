<?php

namespace App\Http\Controllers\Rprt\Table;

use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;

use DB;
use App\Models\Expenses;
use App\Helper\RestResponseMessages;

class WhomIOweController extends BaseController
{
    public function index() {

    	$content = Expenses::where( 'transaction_type', $this->transactionTypes[ 'Bill' ] )
                        ->where( 'status', '!=', $this->statuses[ 'Bill' ][ 'Paid' ] )
    					->select( DB::raw( 'payee_id as payee_id' ), DB::raw( 'payee_type as payee_type' ), DB::raw( 'sum(balance) as amount' ) )
                        ->where( 'is_trash', '!=', 1 )
    					->groupBy( DB::raw( 'payee_id' ) )
    					->get();

    	return RestResponseMessages::reportRetrieveSuccessMessage( 'Whom I Owe', $content );
    }
}
