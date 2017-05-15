<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $table = 'attachment';

    protected $fillable = [
    	'name', 'is_trash'
    ];
}
