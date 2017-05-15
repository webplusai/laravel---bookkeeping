<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntryItem extends Model
{
    protected $table = 'journal_entry_item';

    protected $fillable = [
    	'journal_entry_id', 'rank', 'account_id', 'debits', 'credits', 'description', 'person_id', 'person_type'
    ];
}
