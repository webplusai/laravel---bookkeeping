<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapSalesAttachment extends Model
{
    protected $table = 'map_sales_attachment';

    protected $fillable = [
    	'sales_id', 'attachment_id'
    ];
}
