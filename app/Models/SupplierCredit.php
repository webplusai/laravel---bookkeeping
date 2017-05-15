<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierCredit extends Model
{
    protected $table = 'supplier_credit';

    protected $fillable = [
    	'expenses_id', 'statement_memo'
    ];
}
