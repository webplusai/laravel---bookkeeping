<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierCreditItem extends Model
{
    protected $table = 'supplier_credit_item';

    protected $fillable = [
    	'supplier_credit_id', 'rank', 'product_service_id', 'description', 'qty', 'rate', 'amount'
    ];
}
