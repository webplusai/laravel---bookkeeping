<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profile';

    protected $fillable = [
    	'name', 'email', 'email_verified', 'mobile', 'mobile_verified', 'address', 'city', 'country', 'is_trash'
    ];
}
