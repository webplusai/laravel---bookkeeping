<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReceipt extends Model
{
    protected $table = 'sales_receipt';

    protected $fillable = [
    	'sales_id', 'message', 'statement_memo', 'discount_type_id', 'discount_amount', 'sub_total', 'shipping', 'deposit'
    ];
}
