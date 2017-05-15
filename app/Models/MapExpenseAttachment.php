<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapExpenseAttachment extends Model
{
    protected $table = 'map_expense_attachment';

    protected $fillable = [
    	'expense_id', 'attachment_id'
    ];
}
