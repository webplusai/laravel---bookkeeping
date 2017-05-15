<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $table = 'bill' ;

    protected $fillable = [
    	'expenses_id', 'statement_memo'
    ];
}
