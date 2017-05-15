<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    protected $table = 'credit_note';

    protected $fillable = [
    	'sales_id', 'message', 'statement_memo', 'discount_type_id', 'discount_amount', 'sub_total', 'shipping', 'deposit'
    ];
}
