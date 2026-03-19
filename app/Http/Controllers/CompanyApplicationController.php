<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\ApplicantProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CompanyApplicationController extends Controller
{
    // 求人ごとの応募者一覧
    public function index(int $jobId): Response
    {
        $user = Auth::user();

        $job = JobPost::where('id', $jobId)
            ->where('company_id', $user->id)
            ->firstOrFail();

        $applications = JobApplication::where('job_post_id', $jobId)
            ->orderBy('applied_at', 'desc')
            ->get()
            ->map(function ($app) {
                $applicantUser    = User::find($app->applicant_id);
                $applicantProfile = ApplicantProfile::where('user_id', $app->applicant_id)->first();

                return [
                    'id'                   => $app->id,
                    'status'               => $app->status,
                    'applied_at'           => $app->applied_at,
                    'company_notes'        => $app->company_notes,
                    'applicant_name'       => $applicantUser?->name ?? '-',
                    'applicant_email'      => $applicantUser?->email ?? '-',
                    'member_id'            => $applicantProfile?->member_id ?? '-',
                    'hri_score'            => $applicantProfile?->hri_score,
                    'certification_status' => $applicantProfile?->certification_status ?? '-',
                    'phone_number'         => $applicantProfile?->phone_number ?? '-',
                    'profile_photo'        => $applicantProfile?->profile_photo,
                    'snapshot'             => $app->applicant_snapshot,
                ];
            });

        return Inertia::render('Company/Applications/Index', [
            'job'          => $job,
            'applications' => $applications,
        ]);
    }

    // 応募者詳細
    public function show(int $appId): Response
    {
        $user = Auth::user();
        
        $application = JobApplication::findOrFail($appId);
        
        // 自社の求人への応募のみ閲覧可能
        $job = JobPost::where('id', $application->job_post_id)
            ->where('company_id', $user->id)
            ->firstOrFail();
            
        $applicantUser    = \App\Models\User::find($application->applicant_id);
        $applicantProfile = ApplicantProfile::where('user_id', $application->applicant_id)->first();

        // 学歴・職歴・資格
        $education = \App\Models\EducationHistory::where('user_id', $application->applicant_id)
            ->orderBy('graduation_date', 'desc')->get();
        $work = \App\Models\WorkHistory::where('user_id', $application->applicant_id)
            ->orderBy('start_date', 'desc')->get();
        $certifications = \App\Models\Certification::where('user_id', $application->applicant_id)
            ->orderBy('issued_date', 'desc')->get();
        
        return Inertia::render('Company/Applications/Show', [
            'application'    => $application,
            'job'            => $job,
            'applicantUser'  => $applicantUser,
            'profile'        => $applicantProfile,
            'education'      => $education,
            'work'           => $work,
            'certifications' => $certifications,
        ]);
    }

    // ステータス変更（job_applicationsのみ・applicant_profilesは触らない）
    public function updateStatus(Request $request, int $appId): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'status' => 'required|in:pending,shortlisted,interviewing,hired,rejected',
            'company_notes' => 'nullable|string|max:1000',
        ]);

        $application = JobApplication::findOrFail($appId);

        // 自社の求人への応募のみ変更可能
        $job = JobPost::where('id', $application->job_post_id)
            ->where('company_id', $user->id)
            ->firstOrFail();

        $application->update([
            'status'        => $request->status,
            'company_notes' => $request->company_notes,
        ]);

        return back()->with('success', 'Status lamaran berhasil diperbarui.');
    }
}