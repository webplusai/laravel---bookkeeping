<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    protected $table = 'bill_item';

    protected $fillable = [
    	'bill_id', 'rank', 'product_service_id', 'description', 'qty', 'rate' ,'amount'
    ];
}
