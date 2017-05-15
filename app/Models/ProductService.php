<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductService extends Model
{
    protected $table = 'product_service';

    protected $fillable = [
    	'name', 'sku', 'selling_price', 'product_category_id', 'purchase_price', 'item_type', 'is_inventoriable', 'is_active', 'is_trash'
    ];
}
