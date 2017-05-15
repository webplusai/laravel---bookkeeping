<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'account';

    protected $fillable = [
    	'name', 'description', 'account_category_type_id', 'account_detail_type_id', 'account_number', 'balance', 'is_trash'
    ];
}
