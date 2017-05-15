<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapInvoicePayment extends Model
{
    protected $table = 'map_invoice_payment';

    protected $fillable = [
    	'invoice_id', 'payment_id', 'payment'
    ];
}
