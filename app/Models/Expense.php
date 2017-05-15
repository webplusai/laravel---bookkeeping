<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'expense';

    protected $fillable = [
    	'expenses_id', 'statement_memo'
    ];
}
