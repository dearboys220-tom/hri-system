<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Applicant\DashboardController as ApplicantDashboardController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\Applicant\IdentityController;
use App\Http\Controllers\Applicant\ConfirmationController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminLogsController;
use App\Http\Controllers\SuperAdmin\SuperAdminUsersController;
use App\Http\Controllers\SuperAdmin\SuperAdminExportController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\Manager\StaffManagementController;
use App\Http\Controllers\AbsenceRequestController;
use App\Http\Controllers\TaskOrderController;
use App\Http\Controllers\EmployeeReportController;
use App\Http\Controllers\StaffEvaluationController;
use App\Http\Controllers\SalaryCalculationController;
use App\Http\Controllers\PayrollRecordController;

// ================================================================
// 公開ルート
// ================================================================

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('/register/company', [CompanyController::class, 'create'])->name('register.company');
Route::post('/register/company', [CompanyController::class, 'store'])->name('register.company.store');

// ================================================================
// 認証済みルート（共通）
// ================================================================

Route::middleware('auth')->group(function () {

    // ---- ダッシュボード（role_type 別に自動振り分け）----
    Route::get('/dashboard', function () {
        $user = Auth::user();
        return match($user->role_type ?? '') {
            'admin_user'        => redirect()->route('admin.admin.index'),
            'investigator_user' => redirect()->route('admin.investigator.index'),
            'reviewer_user'     => redirect()->route('admin.investigator.index'),
            'company'           => redirect()->route('company.dashboard'),
            'applicant'         => redirect()->route('applicant.dashboard'),
            'super_admin'       => redirect()->route('super-admin.dashboard'),
            'local_manager'     => redirect()->route('manager.dashboard'),
            'president'         => redirect()->route('president.dashboard'),
            'em_staff'          => redirect()->route('em.dashboard'),
            'strategy_user'     => redirect()->route('strategy.dashboard'),
            'ai_dev_user'       => redirect()->route('ai-dev.dashboard'),
            'marketing_user'    => redirect()->route('marketing.dashboard'),
            default             => Inertia::render('Dashboard'),
        };
    })->name('dashboard');

    // ================================================================
    // 企業会員
    // ================================================================

    Route::get('/company/dashboard', [CompanyController::class, 'dashboard'])->name('company.dashboard');
    Route::get('/company/profile',   [CompanyController::class, 'showProfile'])->name('company.profile');
    Route::post('/company/profile',  [CompanyController::class, 'updateProfile'])->name('company.profile.update');

    // 求人管理
    Route::get('/company/jobs',               [JobController::class, 'index'])->name('company.jobs.index');
    Route::get('/company/jobs/create',        [JobController::class, 'create'])->name('company.jobs.create');
    Route::post('/company/jobs',              [JobController::class, 'store'])->name('company.jobs.store');
    Route::get('/company/jobs/{id}',          [JobController::class, 'show'])->name('company.jobs.show');
    Route::get('/company/jobs/{id}/edit',     [JobController::class, 'edit'])->name('company.jobs.edit');
    Route::post('/company/jobs/{id}',         [JobController::class, 'update'])->name('company.jobs.update');
    Route::delete('/company/jobs/{id}',       [JobController::class, 'destroy'])->name('company.jobs.destroy');

    // 求人応募管理
    Route::get('/company/jobs/{jobId}/applications',
        [App\Http\Controllers\CompanyApplicationController::class, 'index'])->name('company.applications.index');
    Route::post('/company/applications/{appId}/status',
        [App\Http\Controllers\CompanyApplicationController::class, 'updateStatus'])->name('company.applications.status');
    Route::get('/company/applications/{appId}',
        [App\Http\Controllers\CompanyApplicationController::class, 'show'])->name('company.applications.show');

    // 求人投稿支払い
    Route::post('/company/jobs/{jobId}/payment',
        [App\Http\Controllers\JobPaymentController::class, 'createSnap'])->name('company.jobs.payment');
    Route::post('/company/jobs/payment/callback',
        [App\Http\Controllers\JobPaymentController::class, 'callback'])->name('company.jobs.payment.callback');
    Route::get('/company/jobs/payment/finish',
        [App\Http\Controllers\JobPaymentController::class, 'finish'])->name('company.jobs.payment.finish');

    // 候補者履歴書・スコア詳細
    Route::get('/company/applicant/{memberId}',
        [App\Http\Controllers\ScoreDetailController::class, 'resume'])->name('company.applicant.resume');
    Route::get('/company/score/finish',
        [App\Http\Controllers\ScoreDetailController::class, 'finish'])->name('company.score.finish');
    Route::post('/company/score/snap',
        [App\Http\Controllers\ScoreDetailController::class, 'createSnap'])->name('company.score.snap');
    Route::post('/company/score/callback',
        [App\Http\Controllers\ScoreDetailController::class, 'callback'])->name('company.score.callback');
    Route::get('/company/score/{memberId}/view',
        [App\Http\Controllers\ScoreDetailController::class, 'view'])->name('company.score.view');
    Route::get('/company/score/{memberId}',
        [App\Http\Controllers\ScoreDetailController::class, 'show'])->name('company.score.payment');

    // ================================================================
    // 個人会員
    // ================================================================

    Route::get('/applicant/dashboard', [ApplicantDashboardController::class, 'index'])->name('applicant.dashboard');

    // 同意
    Route::get('/applicant/consent',  [ConsentController::class, 'show'])->name('applicant.consent');
    Route::post('/applicant/consent', [ConsentController::class, 'store'])->name('applicant.consent.store');

    // CV入力
    Route::get('/applicant/cv',  [CvController::class, 'index'])->name('applicant.cv');
    Route::post('/applicant/cv/profile',              [CvController::class, 'updateProfile'])->name('applicant.cv.profile.update');
    Route::post('/applicant/cv/education',            [CvController::class, 'storeEducation'])->name('applicant.cv.education.store');
    Route::post('/applicant/cv/education/{id}',       [CvController::class, 'updateEducation'])->name('applicant.cv.education.update');
    Route::delete('/applicant/cv/education/{id}',     [CvController::class, 'destroyEducation'])->name('applicant.cv.education.destroy');
    Route::post('/applicant/cv/work',                 [CvController::class, 'storeWork'])->name('applicant.cv.work.store');
    Route::post('/applicant/cv/work/{id}',            [CvController::class, 'updateWork'])->name('applicant.cv.work.update');
    Route::delete('/applicant/cv/work/{id}',          [CvController::class, 'destroyWork'])->name('applicant.cv.work.destroy');
    Route::post('/applicant/cv/certification',        [CvController::class, 'storeCertification'])->name('applicant.cv.certification.store');
    Route::post('/applicant/cv/certification/{id}',   [CvController::class, 'updateCertification'])->name('applicant.cv.certification.update');
    Route::delete('/applicant/cv/certification/{id}', [CvController::class, 'destroyCertification'])->name('applicant.cv.certification.destroy');

    // 本人確認・申請
    Route::get('/applicant/identity',      [IdentityController::class, 'show'])->name('applicant.identity');
    Route::post('/applicant/identity',     [IdentityController::class, 'update'])->name('applicant.identity.update');
    Route::get('/applicant/confirmation',  [ConfirmationController::class, 'show'])->name('applicant.confirmation');
    Route::post('/applicant/confirmation', [ConfirmationController::class, 'store'])->name('applicant.confirmation.store');

    // 認証済み履歴書・応募履歴
    Route::get('/applicant/certified-resume',
        [App\Http\Controllers\Applicant\CertifiedResumeController::class, 'show'])->name('applicant.certified_resume');
    Route::get('/applicant/applications',
        [App\Http\Controllers\Applicant\ApplicationController::class, 'index'])->name('applicant.applications');

    // プロフィール
    Route::get('/applicant/profile',
        [App\Http\Controllers\Applicant\ProfileController::class, 'show'])->name('applicant.profile');
    Route::post('/applicant/profile',
        [App\Http\Controllers\Applicant\ProfileController::class, 'update'])->name('applicant.profile.update');

    // ブックマーク
    Route::get('/applicant/bookmarks',
        [App\Http\Controllers\Applicant\BookmarkController::class, 'index'])->name('applicant.bookmarks');
    Route::post('/applicant/bookmarks/{jobPostId}/toggle',
        [App\Http\Controllers\Applicant\BookmarkController::class, 'toggle'])->name('applicant.bookmarks.toggle');

    // 認証申請支払い
    Route::post('/applicant/certification/payment',
        [App\Http\Controllers\Applicant\CertificationPaymentController::class, 'createSnap'])->name('applicant.certification.payment');
    Route::post('/applicant/certification/payment/callback',
        [App\Http\Controllers\Applicant\CertificationPaymentController::class, 'callback'])->name('applicant.certification.payment.callback');
    Route::get('/applicant/certification/payment/finish',
        [App\Http\Controllers\Applicant\CertificationPaymentController::class, 'finish'])->name('applicant.certification.payment.finish');

    // ================================================================
    // 公開求人ページ
    // ================================================================

    Route::get('/jobs',             [App\Http\Controllers\PublicJobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{id}',        [App\Http\Controllers\PublicJobController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{id}/apply', [App\Http\Controllers\JobApplicationController::class, 'store'])->name('jobs.apply');

    // ================================================================
    // 共通プロフィール
    // ================================================================

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ================================================================
    // スタッフ共通ルート（全社内ロール）
    // ================================================================

    // 欠勤申請（スタッフ側）
    Route::get('/staff/absence/create', [AbsenceRequestController::class, 'create'])->name('staff.absence.create');
    Route::post('/staff/absence',       [AbsenceRequestController::class, 'store'])->name('staff.absence.store');

    // 業務指示（スタッフ側）
    Route::get('/staff/tasks',             [TaskOrderController::class, 'staffIndex'])->name('staff.tasks.index');
    Route::post('/staff/tasks/{id}/start', [TaskOrderController::class, 'startTask'])->name('staff.tasks.start');

    // 業務報告（スタッフ側）
    Route::post('/staff/reports', [EmployeeReportController::class, 'store'])->name('staff.reports.store');
});

// ================================================================
// 管理・スタッフルート
// ================================================================

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function () {

        // ---- 調査部 ----
        Route::prefix('investigator')
            ->name('investigator.')
            ->group(function () {
                Route::get('/',                 [App\Http\Controllers\InvestigatorController::class, 'index'])->name('index');
                Route::post('/{id}/save',       [App\Http\Controllers\InvestigatorController::class, 'save'])->name('save');
                Route::post('/{id}/complete',   [App\Http\Controllers\InvestigatorController::class, 'complete'])->name('complete');
                Route::post('/{id}/correction', [App\Http\Controllers\InvestigatorController::class, 'correction'])->name('correction');

                // AIチャット（調査部）
                Route::get('/ai-chat', [AiChatController::class, 'investigatorIndex'])->name('ai-chat');
            });

        // ---- 審査管理部 ----
        Route::prefix('admin')
            ->name('admin.')
            ->group(function () {
                Route::get('/',         [App\Http\Controllers\AdminController::class, 'dashboard'])->name('index');
                Route::get('/evaluate', [App\Http\Controllers\AdminController::class, 'index'])->name('evaluate');

                // 承認アクション
                Route::post('/{id}/approve',             [App\Http\Controllers\AdminController::class, 'approve'])->name('approve');
                Route::post('/{id}/conditional-approve', [App\Http\Controllers\AdminController::class, 'conditionalApprove'])->name('conditionalApprove');
                Route::post('/{id}/reject',              [App\Http\Controllers\AdminController::class, 'reject'])->name('reject');
                Route::post('/{id}/return',              [App\Http\Controllers\AdminController::class, 'returnToReviewer'])->name('return');
                Route::post('/{id}/escalate',            [App\Http\Controllers\AdminController::class, 'escalateToHuman'])->name('escalate');

                // 企業管理
                Route::get('/companies',              [App\Http\Controllers\AdminController::class, 'companies'])->name('companies');
                Route::post('/companies/{id}/status', [App\Http\Controllers\AdminController::class, 'updateCompanyStatus'])->name('companies.status');

                // AIチャット（審査管理部）
                Route::get('/ai-chat', [AiChatController::class, 'adminIndex'])->name('ai-chat');
            });

        // AIチャット送信（共通 API エンドポイント）
        Route::post('/ai-chat/send', [AiChatController::class, 'send'])->name('ai-chat.send');
    });

// ================================================================
// マネージャールート（local_manager / president）
// ================================================================

Route::prefix('manager')
    ->name('manager.')
    ->middleware(['auth', App\Http\Middleware\EnsureIsManager::class])
    ->group(function () {

        // ダッシュボード
        Route::get('/dashboard', function () {
            return Inertia::render('Manager/Dashboard');
        })->name('dashboard');

        // スタッフ管理
        Route::get('/staff',         [StaffManagementController::class, 'index'])->name('staff.index');
        Route::post('/staff',        [StaffManagementController::class, 'store'])->name('staff.store');
        Route::get('/staff/{id}',    [StaffManagementController::class, 'show'])->name('staff.show');
        Route::post('/staff/{id}',   [StaffManagementController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{id}', [StaffManagementController::class, 'destroy'])->name('staff.destroy');

        // 欠勤申請管理（マネージャー側）
        Route::get('/absence-requests',
            [AbsenceRequestController::class, 'index'])->name('absence.index');
        Route::post('/absence-requests/{id}/approve',
            [AbsenceRequestController::class, 'approve'])->name('absence.approve');
        Route::post('/absence-requests/{id}/reject',
            [AbsenceRequestController::class, 'reject'])->name('absence.reject');

        // 業務指示管理（マネージャー側）
        Route::get('/task-orders',
            [TaskOrderController::class, 'index'])->name('task-orders.index');
        Route::post('/task-orders',
            [TaskOrderController::class, 'store'])->name('task-orders.store');
        Route::post('/task-orders/{id}/cancel',
            [TaskOrderController::class, 'cancel'])->name('task-orders.cancel');

        // 報告管理（マネージャー側）
        Route::get('/reports',
            [EmployeeReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/{id}/flag',
            [EmployeeReportController::class, 'flagInconsistency'])->name('reports.flag');

        // ★ 査定管理（マネージャー側）v2.8追加
        Route::get('/evaluations',
            [StaffEvaluationController::class, 'index'])->name('evaluations.index');
        Route::post('/evaluations/generate',
            [StaffEvaluationController::class, 'generate'])->name('evaluations.generate');
        Route::post('/evaluations/{evaluation}/approve',
            [StaffEvaluationController::class, 'approve'])->name('evaluations.approve');

        // ★ 給与計算管理 v2.8追加
        Route::get('/salary', [SalaryCalculationController::class, 'index'])->name('salary.index');
        Route::post('/salary/generate', [SalaryCalculationController::class, 'generate'])->name('salary.generate');
        Route::post('/salary/{calculation}/approve', [SalaryCalculationController::class, 'approve'])->name('salary.approve');

        // ★ 支払い記録管理 v2.8追加
        Route::get('/payroll', [PayrollRecordController::class, 'index'])->name('payroll.index');
        Route::post('/payroll', [PayrollRecordController::class, 'store'])->name('payroll.store');
        Route::post('/payroll/{payroll}/processed', [PayrollRecordController::class, 'markProcessed'])->name('payroll.processed');
        Route::post('/payroll/{payroll}/confirmed', [PayrollRecordController::class, 'markConfirmed'])->name('payroll.confirmed');
        Route::post('/payroll/{payroll}/failed', [PayrollRecordController::class, 'markFailed'])->name('payroll.failed');
    });

// ================================================================
// president ルート
// ================================================================

Route::prefix('president')
    ->name('president.')
    ->middleware('auth')
    ->group(function () {

        Route::get('/dashboard', function () {
            return Inertia::render('Manager/Dashboard');
        })->name('dashboard');
    });

// ================================================================
// em_staff ルート（仮）
// ================================================================

Route::prefix('em')
    ->name('em.')
    ->middleware('auth')
    ->group(function () {

        Route::get('/dashboard', function () {
            return Inertia::render('Manager/Dashboard');
        })->name('dashboard');
    });

// ================================================================
// 新3部署ルート（仮 — 画面作成後に正式実装）
// ================================================================

Route::middleware('auth')->group(function () {

    // 戦略マネジメント部
    Route::get('/strategy/dashboard', function () {
        return Inertia::render('Manager/Dashboard');
    })->name('strategy.dashboard');

    // AI開発部
    Route::get('/ai-dev/dashboard', function () {
        return Inertia::render('Manager/Dashboard');
    })->name('ai-dev.dashboard');

    // マーケティング部
    Route::get('/marketing/dashboard', function () {
        return Inertia::render('Manager/Dashboard');
    })->name('marketing.dashboard');
});

// ================================================================
// スーパー管理者ルート
// ================================================================

Route::prefix('super-admin')
    ->name('super-admin.')
    ->middleware(['auth', 'super.admin'])
    ->group(function () {

        // ダッシュボード
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])
             ->name('dashboard');

        // ログ系
        Route::get('/ai-logs',          [SuperAdminLogsController::class, 'aiLogs'])->name('ai-logs');
        Route::get('/ai-chat-logs',     [SuperAdminLogsController::class, 'aiChatLogs'])->name('ai-chat-logs');
        Route::get('/staff-logs',       [SuperAdminLogsController::class, 'staffLogs'])->name('staff-logs');
        Route::get('/data-access-logs', [SuperAdminLogsController::class, 'dataAccessLogs'])->name('data-access-logs');
        Route::get('/ai-transfer-logs', [SuperAdminLogsController::class, 'aiTransferLogs'])->name('ai-transfer-logs');
        Route::get('/audit-logs',       [SuperAdminLogsController::class, 'auditLogs'])->name('audit-logs');

        // PDP法対応
        Route::get('/consent-records',   [SuperAdminLogsController::class, 'consentRecords'])->name('consent-records');
        Route::get('/deletion-requests', [SuperAdminUsersController::class, 'deletionRequests'])->name('deletion-requests');
        Route::patch('/deletion-requests/{id}/approve',
            [SuperAdminUsersController::class, 'approveDeletion'])->name('deletion-requests.approve');
        Route::patch('/deletion-requests/{id}/reject',
            [SuperAdminUsersController::class, 'rejectDeletion'])->name('deletion-requests.reject');

        // ユーザー管理
        Route::get('/users-all',
            [SuperAdminUsersController::class, 'index'])->name('users-all');
        Route::patch('/users-all/{id}/suspend',
            [SuperAdminUsersController::class, 'suspend'])->name('users-all.suspend');

        // エクスポート
        Route::get('/export',           [SuperAdminExportController::class, 'index'])->name('export');
        Route::post('/export/download', [SuperAdminExportController::class, 'download'])->name('export.download');
    });

// ================================================================
// 認証（スタッフ・Google OAuth）
// ================================================================

// スタッフログイン
Route::middleware('guest')->group(function () {
    Route::get('/staff/login',  [StaffAuthController::class, 'create'])->name('staff.login');
    Route::post('/staff/login', [StaffAuthController::class, 'store'])->name('staff.login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/staff/logout', [StaffAuthController::class, 'destroy'])->name('staff.logout');
});

// Google OAuth（個人会員のみ）
Route::middleware('guest')->group(function () {
    Route::get('/auth/google',          [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

require __DIR__.'/auth.php';