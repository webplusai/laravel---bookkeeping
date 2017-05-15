<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLogItem extends Model
{
    protected $table = 'audit_log_item';

    protected $fillable = [
    	'audit_log_id', 'no', 'customer_id', 'supplier_id', 'product_service_id', 'description', 'qty', 'rate', 'account_id', 'amount', 'open_balance'
    ];
}
