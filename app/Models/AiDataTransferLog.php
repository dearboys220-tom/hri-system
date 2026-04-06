<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDataTransferLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'certification_request_id',
        'transfer_purpose',
        'ai_model',
        'ai_provider',
        'data_categories_sent',
        'is_anonymized',
        'legal_basis',
        'consent_record_id',
        'transferred_at',
    ];

    protected $casts = [
        'data_categories_sent' => 'array',
        'is_anonymized'        => 'boolean',
        'transferred_at'       => 'datetime',
    ];

    // transfer_purpose 定数
    const PURPOSE_SCORING           = 'scoring';
    const PURPOSE_PRIORITY_ANALYSIS = 'priority_analysis';
    const PURPOSE_CHAT_SUPPORT      = 'chat_support';
    const PURPOSE_EM_ACTION         = 'em_action';

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function certificationRequest(): BelongsTo
    {
        return $this->belongsTo(CertificationRequest::class);
    }

    public function consentRecord(): BelongsTo
    {
        return $this->belongsTo(ConsentRecord::class);
    }

    // -------------------------------------------------------
    // ファクトリ（送信前に必ず is_anonymized を確認）
    // -------------------------------------------------------
    public static function record(array $data): self
    {
        if (! ($data['is_anonymized'] ?? false)) {
            throw new \RuntimeException(
                'AiDataTransferLog: is_anonymized が false のままAIへの送信ログを記録しようとしました。送信前に匿名化を確認してください。'
            );
        }

        return static::create(array_merge([
            'ai_provider'    => 'Anthropic',
            'legal_basis'    => 'consent',
            'transferred_at' => now(),
        ], $data));
    }
}
