<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRecord extends Model
{
    protected $fillable = [
        'salary_calculation_id',
        'staff_user_id',
        'payment_month',
        'bank_account_name',
        'bank_name',
        'bank_account_no',
        'paid_amount',
        'payment_method',
        'payment_status',
        'payment_reference_no',
        'processed_at',
        'confirmed_at',
        'processed_by_user_id',
    ];

    protected $casts = [
        'paid_amount'   => 'decimal:2',
        'processed_at'  => 'datetime',
        'confirmed_at'  => 'datetime',
    ];

    // ステータス定数
    const STATUS_SCHEDULED  = 'SCHEDULED';
    const STATUS_PROCESSED  = 'PROCESSED';
    const STATUS_CONFIRMED  = 'CONFIRMED';
    const STATUS_FAILED     = 'FAILED';

    // 支払い方法定数
    const METHOD_BANK_TRANSFER = 'BANK_TRANSFER';
    const METHOD_CASH          = 'CASH';
    const METHOD_QRIS          = 'QRIS';
    const METHOD_OTHER         = 'OTHER';

    // ---- リレーション ----

    public function salaryCalculation(): BelongsTo
    {
        return $this->belongsTo(SalaryCalculation::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}