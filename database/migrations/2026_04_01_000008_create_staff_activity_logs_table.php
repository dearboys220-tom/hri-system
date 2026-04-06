<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.5: staff_activity_logs テーブル新設
     *
     * スタッフ（investigator_user / admin_user / super_admin）の
     * 全操作を記録する監査ログテーブル。
     *
     * v2.6との役割分担:
     *   staff_activity_logs : スタッフ操作の詳細（before/after値の詳細記録）
     *   audit_logs          : 業務全体の監査証跡（AI処理・結果物発行含む広範囲）
     *   → 両方を並行して維持する
     *
     * action 例:
     *   approve / reject / update / create / delete / view_sensitive
     *   assign_investigator / change_status / export_data 等
     */
    public function up(): void
    {
        Schema::create('staff_activity_logs', function (Blueprint $table) {
            $table->id();

            // v2.6: 関連案件番号
            $table->string('case_no')->nullable()
                  ->comment('関連案件番号（case_no軸で横断検索可能）');

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->comment('操作したスタッフID');

            $table->string('role_type')
                  ->comment('操作時のロール（investigator_user / admin_user / super_admin）');

            $table->string('action')
                  ->comment('approve / reject / update / create / delete / view_sensitive 等');

            $table->string('target_type')
                  ->comment('CertificationRequest / InvestigationItem / CompanyProfile 等');

            $table->unsignedBigInteger('target_id')->nullable()
                  ->comment('対象レコードID');

            $table->string('description')->nullable()
                  ->comment('操作の簡易説明（例：案件CR-0001を承認）');

            $table->json('before_value')->nullable()
                  ->comment('変更前の値（JSONオブジェクト）');

            $table->json('after_value')->nullable()
                  ->comment('変更後の値（JSONオブジェクト）');

            $table->string('export_reason')->nullable()
                  ->comment('エクスポート操作時の理由（action = export の場合）');

            $table->string('ip_address')->nullable()
                  ->comment('操作時のIPアドレス');

            $table->text('user_agent')->nullable()
                  ->comment('ブラウザ情報');

            $table->timestamp('created_at')->useCurrent()
                  ->comment('操作日時');

            // インデックス
            $table->index('case_no');
            $table->index('user_id');
            $table->index('action');
            $table->index(['target_type', 'target_id'], 'idx_sal_target');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_activity_logs');
    }
};
