<?php

/**
 * ============================================================
 * 既存モデルへの追記内容まとめ（v2.5 / v2.6 対応）
 *
 * 以下の内容を各既存ファイルに追記・修正してください。
 * ファイルパス: app/Models/各モデル名.php
 * ============================================================
 */

// ============================================================
// 【1】 User.php への追記
// ============================================================
//
// $fillable に追加:
//   'status', 'last_login_at', 'last_login_ip',
//
// $casts に追加:
//   'last_login_at' => 'datetime',
//
// role_type 定数を追加（クラス内）:
//   const ROLE_APPLICANT         = 'applicant';
//   const ROLE_COMPANY           = 'company';
//   const ROLE_INVESTIGATOR      = 'investigator_user';
//   const ROLE_ADMIN             = 'admin_user';
//   const ROLE_SUPER_ADMIN       = 'super_admin';
//
// リレーションを追加:
//   public function consentRecords(): HasMany
//   {
//       return $this->hasMany(ConsentRecord::class);
//   }
//
//   public function deletionRequests(): HasMany
//   {
//       return $this->hasMany(DataDeletionRequest::class);
//   }
//
//   public function staffActivityLogs(): HasMany
//   {
//       return $this->hasMany(StaffActivityLog::class);
//   }
//
// ヘルパーメソッドを追加:
//   public function isSuperAdmin(): bool
//   {
//       return $this->role_type === self::ROLE_SUPER_ADMIN;
//   }
//
//   public function isInvestigator(): bool
//   {
//       return $this->role_type === self::ROLE_INVESTIGATOR;
//   }
//
//   public function isAdmin(): bool
//   {
//       return $this->role_type === self::ROLE_ADMIN;
//   }
//
//   /** 最終ログイン情報を更新（ログイン成功時に呼び出す） */
//   public function updateLastLogin(): void
//   {
//       $this->update([
//           'last_login_at' => now(),
//           'last_login_ip' => request()->ip(),
//       ]);
//   }
//
//   /** 有効な同意を持つか確認 */
//   public function hasActiveConsent(string $consentType): bool
//   {
//       return ConsentRecord::hasActiveConsent($this->id, $consentType);
//   }

// ============================================================
// 【2】 ApplicantProfile.php への追記
// ============================================================
//
// $fillable に追加:
//   'data_retention_expires_at',
//
// $casts に追加:
//   'data_retention_expires_at' => 'date',

// ============================================================
// 【3】 CertificationRequest.php への追記
// ============================================================
//
// $fillable に追加:
//   'case_no',
//   'current_status',
//   'external_status',
//   'latest_return_id',
//   'internal_return_required',
//   'deliverable_vr_status',
//   'deliverable_ir_status',
//   'deliverable_rn_status',
//   'approved_by_user_id',
//   'approved_at',
//
// $casts に追加:
//   'internal_return_required' => 'boolean',
//   'approved_at'              => 'datetime',
//
// current_status 定数を追加:
//   const STATUS_DRAFT              = 'draft';
//   const STATUS_UNDER_INVESTIGATION = 'under_investigation';
//   const STATUS_AI_REVIEW_PENDING  = 'ai_review_pending';
//   const STATUS_RETURNED_INTERNAL  = 'returned_internal';
//   const STATUS_HUMAN_REVIEW       = 'human_review_required';
//   const STATUS_CONDITIONALLY_VERIFIED = 'conditionally_verified';
//   const STATUS_VERIFIED           = 'verified';
//   const STATUS_REJECTED           = 'rejected';
//
// external_status 定数を追加:
//   const EXT_UNDER_REVIEW               = 'under_review';
//   const EXT_ADDITIONAL_CHECK           = 'additional_check_in_progress';
//   const EXT_CONDITIONALLY_VERIFIED     = 'conditionally_verified';
//   const EXT_VERIFIED                   = 'verified';
//   const EXT_REJECTED                   = 'rejected';
//
// リレーションを追加:
//   public function latestReturn(): BelongsTo
//   {
//       return $this->belongsTo(CaseReturn::class, 'latest_return_id');
//   }
//
//   public function approvedBy(): BelongsTo
//   {
//       return $this->belongsTo(User::class, 'approved_by_user_id');
//   }
//
//   public function deliverables(): HasMany
//   {
//       return $this->hasMany(CaseDeliverable::class);
//   }
//
//   public function caseReturns(): HasMany
//   {
//       return $this->hasMany(CaseReturn::class);
//   }
//
//   public function priorityReport(): HasOne
//   {
//       return $this->hasOne(InvestigationPriorityReport::class);
//   }
//
//   public function consentRecords(): HasMany
//   {
//       return $this->hasMany(ConsentRecord::class, 'case_no', 'case_no');
//   }
//
//   public function auditLogs(): HasMany
//   {
//       return $this->hasMany(AuditLog::class, 'case_no', 'case_no');
//   }

// ============================================================
// 【4】 CaseReview.php への追記
// ============================================================
//
// $fillable に追加:
//   'case_no',
//   'input_hash',
//   'confidence_level',
//   'ai_proposed_decision',
//   'human_override_decision',
//   'human_override_reason',
//   'effective_decision',
//   'approved_by_user_id',
//   'approved_at',
//
// $casts に追加:
//   'approved_at' => 'datetime',
//
// decision 定数を追加:
//   const DECISION_APPROVE               = 'APPROVE';
//   const DECISION_CONDITIONAL_APPROVE   = 'CONDITIONAL_APPROVE';
//   const DECISION_REJECT                = 'REJECT';
//   const DECISION_ESCALATE_TO_HUMAN     = 'ESCALATE_TO_HUMAN';
//   const DECISION_RETURN_TO_INVESTIGATION = 'RETURN_TO_INVESTIGATION';
//
// リレーションを追加:
//   public function approvedBy(): BelongsTo
//   {
//       return $this->belongsTo(User::class, 'approved_by_user_id');
//   }
//
// ヘルパーを追加:
//   /** effective_decision を確定する（AI提案通りの場合） */
//   public function confirmByHuman(int $staffUserId): void
//   {
//       $this->update([
//           'human_override_decision' => null,
//           'effective_decision'      => $this->ai_proposed_decision,
//           'approved_by_user_id'     => $staffUserId,
//           'approved_at'             => now(),
//       ]);
//   }
//
//   /** 人間が判定を上書きする */
//   public function overrideByHuman(
//       int    $staffUserId,
//       string $decision,
//       string $reason
//   ): void {
//       $this->update([
//           'human_override_decision' => $decision,
//           'human_override_reason'   => $reason,
//           'effective_decision'      => $decision,
//           'approved_by_user_id'     => $staffUserId,
//           'approved_at'             => now(),
//       ]);
//   }
