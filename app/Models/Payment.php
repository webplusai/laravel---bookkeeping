<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment';

    protected $fillable = [
    	'sales_id', 'account_id', 'note'
    ];
}
