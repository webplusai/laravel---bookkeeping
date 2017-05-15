<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountDetailType extends Model
{
    protected $table = 'account_detail_type';

    protected $fillable = [
    	'account_category_type_id', 'name', 'description', 'is_trash'
    ];
}
