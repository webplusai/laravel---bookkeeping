<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapJournalEntryAttachment extends Model
{
    protected $table = 'map_journal_entry_attachment';

    protected $fillable = [
    	'journal_enntry_id', 'attachment_id'
    ];
}
