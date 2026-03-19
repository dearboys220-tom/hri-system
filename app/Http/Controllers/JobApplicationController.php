<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\JobApplication;
use App\Models\ApplicantProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class JobApplicationController extends Controller
{
    public function store(int $jobId): RedirectResponse
    {
        $user = Auth::user();

        // 個人会員のみ応募可能
        if ($user->role_type !== 'applicant') {
            return back()->withErrors(['error' => 'Hanya anggota individu yang bisa melamar.']);
        }

        $job = JobPost::where('id', $jobId)
            ->where('status', 'active')
            ->firstOrFail();

        // 重複応募チェック
        $already = JobApplication::where('job_post_id', $jobId)
            ->where('applicant_id', $user->id)
            ->exists();

        if ($already) {
            return back()->with('error', 'Kamu sudah melamar lowongan ini.');
        }

        // 応募者プロフィールのスナップショット
        $profile = ApplicantProfile::where('user_id', $user->id)->first();
        $snapshot = $profile ? [
            'name'                 => $user->name,
            'member_id'            => $profile->member_id,
            'certification_status' => $profile->certification_status,
            'hri_score'            => $profile->hri_score,
            'phone_number'         => $profile->phone_number,
            'current_address'      => $profile->current_address,
        ] : ['name' => $user->name];

        JobApplication::create([
            'job_post_id'        => $jobId,
            'applicant_id'       => $user->id,
            'company_id'         => $job->company_id,
            'status'             => 'pending',
            'applied_at'         => now(),
            'applicant_snapshot' => $snapshot,
            'job_deleted'        => false,
        ]);

        // 応募数を +1
        $job->increment('application_count');

        return back()->with('success', 'Lamaran berhasil dikirim! Perusahaan akan menghubungi kamu.');
    }
}