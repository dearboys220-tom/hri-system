<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InvestigationItem extends Model
{
    protected $fillable = [
        'certification_request_id',
        'category',
        'item_name',
        'validity',
        'notes',
        'input_language',
        'notes_id',
        'checked_by',
        'checked_at',
        'ai_deduction',
    ];
    protected $casts = [
        'checked_at' => 'datetime',
    ];
    public function certificationRequest()
    {
        return $this->belongsTo(CertificationRequest::class);
    }
    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}