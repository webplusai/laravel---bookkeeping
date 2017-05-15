<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseAccount extends Model
{
    protected $table = 'expense_account';

    protected $fillable = [
    	'expense_id', 'rank', 'account_id', 'description', 'amount'
    ];
}
