<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillAccount extends Model
{
    protected $table = 'bill_account';

    protected $fillable = [
    	'bill_id', 'rank', 'account_id', 'description', 'amount'
    ];
}
