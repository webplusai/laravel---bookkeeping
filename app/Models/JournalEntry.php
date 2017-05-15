<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $table = 'journal_entry';

    protected $fillable = [
    	'date', 'statement_memo', 'is_trash'
    ];
}
