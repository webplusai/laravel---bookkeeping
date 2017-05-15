<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPayment extends Model
{
    protected $table = 'bill_payment';

    protected $fillable = [
    	'expenses_id', 'account_id', 'note'
    ];
}
