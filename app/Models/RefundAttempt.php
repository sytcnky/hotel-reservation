<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefundAttempt extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_SUCCESS   = 'success';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const INITIATOR_ADMIN    = 'admin';
    public const INITIATOR_CUSTOMER = 'customer';

    protected $guarded = [];

    protected $casts = [
        'amount'       => 'float',
        'meta'         => 'array',
        'raw_request'  => 'array',
        'raw_response' => 'array',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class);
    }

    public function paymentAttempt()
    {
        return $this->belongsTo(\App\Models\PaymentAttempt::class);
    }

    public function initiatorUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'initiator_user_id');
    }
}
