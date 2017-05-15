<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    protected $table = 'expenses';

    protected $fillable = [
    	'date', 'transaction_type', 'payee_id', 'payee_type', 'account_id', 'due_date', 'total', 'balance', 'status', 'is_trash'
    ];
}
