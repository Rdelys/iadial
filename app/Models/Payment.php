<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'plan', 'plan_label', 'client_name', 'company_name', 'sector',
        'address', 'city', 'email', 'phone',
        'amount_eur', 'amount_mga', 'exchange_rate', 'currency',
        'reference', 'notification_token', 'payment_link',
        'payment_method', 'status', 'raw_notification',
    ];

    protected $casts = [
        'raw_notification' => 'array',
        'amount_eur' => 'decimal:2',
        'amount_mga' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}