<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'company', 'email', 'country', 'city', 'phone', 'address1', 'address2', 'note', 'balance', 'is_active', 'is_trash'
    ];

}
