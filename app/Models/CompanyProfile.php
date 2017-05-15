<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $table = 'company_profile';

    protected $fillable = [
    	'company_name', 'business_id_no', 'industry', 'company_email', 'company_phone', 'company_website', 'address', 'city', 'country', 'company_logo', 'is_trash'
    ];
}
