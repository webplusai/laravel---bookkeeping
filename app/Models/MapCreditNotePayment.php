<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapCreditNotePayment extends Model
{
    protected $table = 'map_credit_note_payment';

    protected $fillable = [
    	'credit_note_id', 'payment_id', 'payment'
    ];
}
