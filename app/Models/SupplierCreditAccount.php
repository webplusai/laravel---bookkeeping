<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierCreditAccount extends Model
{
    protected $table = 'supplier_credit_account';

    protected $fillable = [
    	'supplier_credit_id', 'rank', 'account_id', 'description', 'amount'
    ];
}
