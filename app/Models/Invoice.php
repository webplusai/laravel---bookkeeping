<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoice';

    protected $fillable = [
    	'sales_id', 'message', 'statement_memo', 'discount_type_id', 'discount_amount', 'sub_total', 'shipping', 'deposit'
    ];
}
