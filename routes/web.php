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
use App\Http\Controllers\StaffEducationController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\Sales\EstimateController;
use App\Http\Controllers\Sales\OrderController;
use App\Http\Controllers\Sales\InvoiceController;
use App\Http\Controllers\Accounting\AccountingController;

// ================================================================
// 公開ルート
// ================================================================

Route::get('/', [App\Http\Controllers\WelcomeController::class, 'index'])->name('home');

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
    // ★ v2.9 教育ポータル（全スタッフ共通）
    // ★ education middleware 適用前にアクセスできる必要があるため
    // ★ 単独の auth グループに配置する
    // ================================================================

    Route::get('/staff/education',
        [StaffEducationController::class, 'index'])->name('staff.education.index');

    Route::get('/staff/education/{moduleCode}',
        [StaffEducationController::class, 'show'])->name('staff.education.show');

    Route::post('/staff/education/{moduleCode}/complete',
        [StaffEducationController::class, 'complete'])->name('staff.education.complete');

    // ================================================================
    // 社内スタッフ共通マイページ（全ロール共通・本人データのみ表示）
    // ================================================================

    Route::get('/staff/mypage',
        [App\Http\Controllers\StaffDashboardController::class, 'index']
    )->name('staff.mypage')->middleware(['auth', 'education:company_rules']);

    // ================================================================
    // スタッフ共通ルート（全社内ロール）
    // ★ company_rules 完了必須
    // ================================================================

    Route::middleware('education:company_rules')->group(function () {

        // 欠勤申請（スタッフ側）
        Route::get('/staff/absence/create', [AbsenceRequestController::class, 'create'])->name('staff.absence.create');
        Route::post('/staff/absence',       [AbsenceRequestController::class, 'store'])->name('staff.absence.store');

        // 業務指示（スタッフ側）
        Route::get('/staff/tasks',             [TaskOrderController::class, 'staffIndex'])->name('staff.tasks.index');
        Route::post('/staff/tasks/{id}/start', [TaskOrderController::class, 'startTask'])->name('staff.tasks.start');

        // 業務報告（スタッフ側）
        // ★ privacy_and_data_handling も必要（Section 31.5）
        Route::middleware('education:privacy_and_data_handling')->group(function () {
            Route::post('/staff/reports', [EmployeeReportController::class, 'store'])->name('staff.reports.store');
        });
    });
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

                Route::post('/{id}/ai-review',           [App\Http\Controllers\AdminController::class, 'runAiSubpromptReview'])->name('ai.review');

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
// ★ v2.9: education:company_rules Middleware を全体に適用
// ★ 給与・支払いは education:payment_and_approval_rules も必要なため独立
// ================================================================

Route::prefix('manager')
    ->name('manager.')
    ->middleware([
        'auth',
        App\Http\Middleware\EnsureIsManager::class,
        'education:company_rules', // ★ v2.9追加
    ])
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
        Route::post('/task-orders/{id}/approve',
            [TaskOrderController::class, 'approve'])->name('task-orders.approve');

        // 報告管理（マネージャー側）
        Route::get('/reports',
            [EmployeeReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/{id}/flag',
            [EmployeeReportController::class, 'flagInconsistency'])->name('reports.flag');

        // 査定管理（マネージャー側）v2.8
        Route::get('/evaluations',
            [StaffEvaluationController::class, 'index'])->name('evaluations.index');
        Route::post('/evaluations/generate',
            [StaffEvaluationController::class, 'generate'])->name('evaluations.generate');
        Route::post('/evaluations/{evaluation}/approve',
            [StaffEvaluationController::class, 'approve'])->name('evaluations.approve');

        // ★ v2.9: 給与・支払いは payment_and_approval_rules も必要
        Route::middleware('education:payment_and_approval_rules')->group(function () {

            // 給与計算管理 v2.8
            Route::get('/salary',
                [SalaryCalculationController::class, 'index'])->name('salary.index');
            Route::post('/salary/generate',
                [SalaryCalculationController::class, 'generate'])->name('salary.generate');
            Route::post('/salary/{calculation}/approve',
                [SalaryCalculationController::class, 'approve'])->name('salary.approve');

            // 支払い記録管理 v2.8
            Route::get('/payroll',
                [PayrollRecordController::class, 'index'])->name('payroll.index');
            Route::post('/payroll',
                [PayrollRecordController::class, 'store'])->name('payroll.store');
            Route::post('/payroll/{payroll}/processed',
                [PayrollRecordController::class, 'markProcessed'])->name('payroll.processed');
            Route::post('/payroll/{payroll}/confirmed',
                [PayrollRecordController::class, 'markConfirmed'])->name('payroll.confirmed');
            Route::post('/payroll/{payroll}/failed',
                [PayrollRecordController::class, 'markFailed'])->name('payroll.failed');
        });

        Route::prefix('subprompt')
            ->name('subprompt.')
            ->middleware(['auth'])
            ->group(function () {
                Route::post('/a1', [\App\Http\Controllers\SubpromptController::class, 'runA1'])->name('a1');
                Route::post('/a2', [\App\Http\Controllers\SubpromptController::class, 'runA2'])->name('a2');
                Route::post('/d1', [\App\Http\Controllers\SubpromptController::class, 'runD1'])->name('d1');
                Route::post('/d3', [\App\Http\Controllers\SubpromptController::class, 'runD3'])->name('d3');
                Route::post('/g1', [\App\Http\Controllers\SubpromptController::class, 'runG1'])->name('g1');
                Route::post('/i3', [\App\Http\Controllers\SubpromptController::class, 'runI3'])->name('i3');
                Route::post('/k2', [\App\Http\Controllers\SubpromptController::class, 'runK2'])->name('k2');
                Route::post('/k3', [\App\Http\Controllers\SubpromptController::class, 'runK3'])->name('k3');
            });
    });

// ================================================================
// president ルート
// ★ v2.9: education:company_rules + education:payment_and_approval_rules 適用
// ================================================================

Route::prefix('president')
    ->name('president.')
    ->middleware([
        'auth',
        App\Http\Middleware\EnsureIsPresident::class,
        'education:company_rules',
        'education:payment_and_approval_rules',
    ])
    ->group(function () {

        Route::get('/dashboard',
            [App\Http\Controllers\President\PresidentDashboardController::class, 'index']
        )->name('dashboard');

        Route::post('/salary/{calculation}/approve',
            [App\Http\Controllers\President\PresidentDashboardController::class, 'approveSalary']
        )->name('salary.approve');

        // ★ v2.9追加: AIチャット
        Route::post('/chat/send',
            [App\Http\Controllers\President\PresidentChatController::class, 'send']
        )->name('chat.send');

        Route::get('/chat/history',
            [App\Http\Controllers\President\PresidentChatController::class, 'history']
        )->name('chat.history');
    });

// ================================================================
// em_staff ルート
// ★ v2.9: education:company_rules 適用
// ================================================================

Route::prefix('em')
    ->name('em.')
    ->middleware(['auth', 'education:company_rules'])
    ->group(function () {

        Route::get('/dashboard',
            [App\Http\Controllers\StaffDashboardController::class, 'index']
        )->name('dashboard');
    });

// ================================================================
// 新3部署ルート
// ★ v2.9: education:company_rules 適用
// ================================================================

Route::middleware(['auth', 'education:company_rules'])->group(function () {

    // ── 戦略マネジメント部 ──
    Route::prefix('strategy')->name('strategy.')->group(function () {
        Route::get('/dashboard',
            [\App\Http\Controllers\Strategy\StrategyCaseController::class, 'index']
        )->name('dashboard');
        Route::post('/cases',
            [\App\Http\Controllers\Strategy\StrategyCaseController::class, 'store']
        )->name('cases.store');
        Route::post('/cases/{id}/status',
            [\App\Http\Controllers\Strategy\StrategyCaseController::class, 'updateStatus']
        )->name('cases.status');
        Route::post('/cases/{id}/ai-summary',
            [\App\Http\Controllers\Strategy\StrategyCaseController::class, 'generateAiSummary']
        )->name('cases.ai-summary');
    });

    // ── AI開発部 ──
    Route::prefix('ai-dev')->name('ai-dev.')->group(function () {
        Route::get('/dashboard',
            [\App\Http\Controllers\AiDev\AiDevProjectController::class, 'index']
        )->name('dashboard');
        Route::post('/projects',
            [\App\Http\Controllers\AiDev\AiDevProjectController::class, 'store']
        )->name('projects.store');
        Route::post('/projects/{id}/status',
            [\App\Http\Controllers\AiDev\AiDevProjectController::class, 'updateStatus']
        )->name('projects.status');
    });

    // ── マーケティング部 ──
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/dashboard',
            [\App\Http\Controllers\Marketing\MarketingCampaignController::class, 'index']
        )->name('dashboard');
        Route::post('/campaigns',
            [\App\Http\Controllers\Marketing\MarketingCampaignController::class, 'store']
        )->name('campaigns.store');
        Route::post('/campaigns/{id}/status',
            [\App\Http\Controllers\Marketing\MarketingCampaignController::class, 'updateStatus']
        )->name('campaigns.status');
    });
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
// 問い合わせ管理（Section 30.3）
// ================================================================

// 【一般会員・企業会員】問い合わせ送信
Route::middleware(['auth'])->group(function () {
    Route::get('/inquiry/create',  [InquiryController::class, 'create'])->name('inquiry.create');
    Route::post('/inquiry',        [InquiryController::class, 'store'])->name('inquiry.store');
    Route::get('/inquiry/my-list', [InquiryController::class, 'myList'])->name('inquiry.my-list');
});

// 【管理者】問い合わせ管理
Route::middleware(['auth'])->prefix('admin/inquiries')->group(function () {
    Route::get('/',                        [InquiryController::class, 'adminIndex'])->name('admin.inquiry.index');
    Route::get('/{inquiry}',               [InquiryController::class, 'adminShow'])->name('admin.inquiry.show');
    Route::post('/{inquiry}/reply',        [InquiryController::class, 'reply'])->name('admin.inquiry.reply');
    Route::post('/{inquiry}/escalate',     [InquiryController::class, 'escalate'])->name('admin.inquiry.escalate');
    Route::post('/{inquiry}/close',        [InquiryController::class, 'close'])->name('admin.inquiry.close');
});

// ================================================================
// 見積・受注・請求管理（Section 30.4）
// ================================================================
Route::middleware(['auth'])->prefix('sales')->group(function () {

    // 見積
    Route::get('/estimates',                        [EstimateController::class, 'index'])->name('sales.estimates.index');
    Route::get('/estimates/create',                 [EstimateController::class, 'create'])->name('sales.estimates.create');
    Route::post('/estimates/generate-ai',           [EstimateController::class, 'generateAi'])->name('sales.estimates.generate-ai');
    Route::post('/estimates',                       [EstimateController::class, 'store'])->name('sales.estimates.store');
    Route::get('/estimates/{estimate}',             [EstimateController::class, 'show'])->name('sales.estimates.show');
    Route::post('/estimates/{estimate}/submit',     [EstimateController::class, 'submitForApproval'])->name('sales.estimates.submit');
    Route::post('/estimates/{estimate}/approve',    [EstimateController::class, 'approve'])->name('sales.estimates.approve');
    Route::post('/estimates/{estimate}/mark-sent',  [EstimateController::class, 'markSent'])->name('sales.estimates.mark-sent');
    Route::post('/estimates/{estimate}/accept',     [EstimateController::class, 'accept'])->name('sales.estimates.accept');

    // 受注
    Route::get('/orders',                               [OrderController::class, 'index'])->name('sales.orders.index');
    Route::get('/orders/{order}',                       [OrderController::class, 'show'])->name('sales.orders.show');
    Route::post('/orders/{order}/create-invoice',       [OrderController::class, 'createInvoice'])->name('sales.orders.create-invoice');
    Route::post('/orders/{order}/complete',             [OrderController::class, 'complete'])->name('sales.orders.complete');

    // 請求書
    Route::get('/invoices',                             [InvoiceController::class, 'index'])->name('sales.invoices.index');
    Route::get('/invoices/{invoice}',                   [InvoiceController::class, 'show'])->name('sales.invoices.show');
    Route::post('/invoices/{invoice}/submit',           [InvoiceController::class, 'submitForApproval'])->name('sales.invoices.submit');
    Route::post('/invoices/{invoice}/approve',          [InvoiceController::class, 'approve'])->name('sales.invoices.approve');
    Route::post('/invoices/{invoice}/mark-sent',        [InvoiceController::class, 'markSent'])->name('sales.invoices.mark-sent');
    Route::post('/invoices/{invoice}/mark-paid',        [InvoiceController::class, 'markPaid'])->name('sales.invoices.mark-paid');
});

// ================================================================
// 会計月次送付管理（Section 30.5）
// ================================================================
Route::middleware(['auth'])->prefix('accounting')->group(function () {
    Route::get('/',                                    [AccountingController::class, 'index'])->name('accounting.index');
    Route::get('/create',                              [AccountingController::class, 'create'])->name('accounting.create');
    Route::post('/fetch-month-data',                   [AccountingController::class, 'fetchMonthData'])->name('accounting.fetch-month-data');
    Route::post('/',                                   [AccountingController::class, 'store'])->name('accounting.store');
    Route::get('/{report}',                            [AccountingController::class, 'show'])->name('accounting.show');
    Route::post('/{report}/organize-ai',               [AccountingController::class, 'organizeWithAi'])->name('accounting.organize-ai');
    Route::post('/{report}/submit',                    [AccountingController::class, 'submitForApproval'])->name('accounting.submit');
    Route::post('/{report}/approve',                   [AccountingController::class, 'approve'])->name('accounting.approve');
    Route::post('/{report}/mark-sent',                 [AccountingController::class, 'markSent'])->name('accounting.mark-sent');
    Route::post('/{report}/acknowledge',               [AccountingController::class, 'acknowledge'])->name('accounting.acknowledge');
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