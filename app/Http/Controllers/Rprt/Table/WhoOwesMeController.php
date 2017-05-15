<?php

namespace App\Http\Controllers\Rprt\Table;

use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;

use DB;
use App\Models\Sales;
use App\Helper\RestResponseMessages;

class WhoOwesMeController extends BaseController
{
    public function index() {
        
    	$content = Sales::where( 'transaction_type', $this->transactionTypes[ 'Invoice' ] )
                        ->where( 'status', '!=', $this->statuses[ 'Invoice' ][ 'Paid' ] )
    					->select( DB::raw( 'customer_id as payee_id' ), DB::raw( 'sum(balance) as amount' ) )
                        ->where( 'is_trash', '!=', 1 )
    					->groupBy( DB::raw( 'customer_id' ) )
    					->get();

    	return RestResponseMessages::reportRetrieveSuccessMessage( 'Who Owes Me', $content );
    }
}
