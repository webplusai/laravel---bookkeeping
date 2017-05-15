<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReceiptItem extends Model
{
    protected $table = 'sales_receipt_item';

    protected $fillable = [
    	'sales_receipt_id', 'rank', 'item_type', 'product_service_id', 'description', 'qty', 'rate', 'amount'
    ];
}
