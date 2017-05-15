<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';

    protected $fillable = [
    	'table_id', 'record_id', 'trxn_id', 'date_changed', 'user_email', 'event_text', 'target_name', 'person_id', 'person_type', 'date', 'amount', 'open_balance', 'message', 'memo', 'is_indirect'
    ];
}
