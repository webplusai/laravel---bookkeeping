<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $table = 'sales';

    protected $fillable = [
    	'date', 'transaction_type', 'invoice_receipt_no', 'customer_id', 'due_date', 'total', 'balance', 'status', 'is_trash'
    ];
}
