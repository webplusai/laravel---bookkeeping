<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $table = 'invoice_item';

    protected $fillable = [
    	'invoice_id', 'rank', 'item_type', 'product_service_id', 'description', 'qty', 'rate', 'amount'
    ];
}
