<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseItem extends Model
{
    protected $table = 'expense_item';

    protected $fillable = [
    	'expense_id', 'rank', 'product_service_id', 'description', 'qty', 'rate', 'amount'
    ];
}
