<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountCategoryType extends Model
{
    protected $table = 'account_category_type';

    protected $fillable = [
    	'name', 'is_trash'
    ];

    public function detailTypes() {
    	return $this->hasMany('App\Models\AccountDetailType', 'account_category_type_id');
    }
}
