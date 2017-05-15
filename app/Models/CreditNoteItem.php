<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNoteItem extends Model
{
    protected $table = 'credit_note_item';

    protected $fillable = [
    	'credit_note_id', 'rank', 'item_type', 'product_service_id', 'description', 'qty', 'rate', 'amount'
    ];
}
