<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
	protected $table = 'supplier';

    protected $fillable = [
    	'name', 'company', 'email', 'country', 'city', 'phone', 'address1', 'address2', 'note', 'balance', 'is_active', 'is_trash'
    ];
}
