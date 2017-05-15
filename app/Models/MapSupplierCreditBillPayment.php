<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapSupplierCreditBillPayment extends Model
{
    protected $table = 'map_supplier_credit_bill_payment';

    protected $fillable = [
    	'supplier_credit_id', 'bill_payment_id', 'payment'
    ];
}
