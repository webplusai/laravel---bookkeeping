<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapBillBillPayment extends Model
{
    protected $table = 'map_bill_bill_payment';

    protected $fillable = [
    	'bill_id', 'bill_payment_id', 'payment'
    ];
}
