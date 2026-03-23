<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\CompanyProfile;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use App\Data\JobCategories;

class JobController extends Controller
{
    // 求人投稿フォーム表示
    public function create(): Response
    {
        $user    = Auth::user();
        $profile = CompanyProfile::where('user_id', $user->id)->first();

        $isFreePostAvailable = false;
        $freePostDaysLeft    = 0;
        if ($profile && !$profile->free_job_post_used && $profile->free_job_post_expires_at) {
            $expires = Carbon::parse($profile->free_job_post_expires_at);
            if ($expires->isFuture()) {
                $isFreePostAvailable = true;
                $freePostDaysLeft    = (int) now()->diffInDays($expires);
            }
        }

        return Inertia::render('Company/Jobs/Create', [
            'isFreePostAvailable' => $isFreePostAvailable,
            'freePostDaysLeft'    => $freePostDaysLeft,
            'isVerified'          => $profile?->company_verification_status === 'verified',
            'categories'          => JobCategories::all(),
        ]);
    }

    // 求人投稿保存
    public function store(Request $request): RedirectResponse
    {
        $user    = Auth::user();
        $profile = CompanyProfile::where('user_id', $user->id)->first();

        if (!$profile || $profile->company_verification_status !== 'verified') {
            return back()->withErrors(['error' => 'Akun perusahaan belum diverifikasi.']);
        }

        $request->validate([
            'title'                 => 'required|string|max:255',
            'category'              => 'nullable|string|max:100',
            'subcategory'           => 'nullable|string|max:100',
            'employment_type'       => 'required|string|max:50',
            'education_requirement' => 'nullable|string|max:100',
            'experience_level'      => 'nullable|string|max:100',
            'working_days'          => 'nullable|array',
            'working_hours'         => 'nullable|string|max:100',
            'language_requirements' => 'nullable|array',
            'gender'                => 'nullable|string|max:50',
            'age_min'               => 'nullable|integer|min:15|max:99',
            'age_max'               => 'nullable|integer|min:15|max:99',
            'marital_status'        => 'nullable|string|max:50',
            'salary_min'            => 'nullable|integer|min:0',
            'salary_max'            => 'nullable|integer|min:0',
            'location'              => 'required|string|max:255',
            'job_description'       => 'required|string',
            'required_skills'       => 'nullable|string',
            'preferred_skills'      => 'nullable|string',
            'application_deadline'  => 'required|date|after:today',
            'start_date'            => 'nullable|date',
            'special_requirements'  => 'nullable|string',
            'workplace_photo'       => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $workplacePhotoPath = null;
        if ($request->hasFile('workplace_photo')) {
            $workplacePhotoPath = $request->file('workplace_photo')
                ->store('workplace_photos', 'public');
        }

        // 無料チェック
        $isFreePost = false;
        if (!$profile->free_job_post_used && $profile->free_job_post_expires_at) {
            $expires = Carbon::parse($profile->free_job_post_expires_at);
            if ($expires->isFuture()) {
                $isFreePost = true;
            }
        }

        $jobPost = JobPost::create([
            'company_id'            => $user->id,
            'title'                 => $request->title,
            'workplace_photo'       => $workplacePhotoPath,
            'category'              => $request->category,
            'subcategory'           => $request->subcategory,
            'employment_type'       => $request->employment_type,
            'education_requirement' => $request->education_requirement,
            'experience_level'      => $request->experience_level,
            'working_days'          => $request->working_days ?? [],
            'working_hours'         => $request->working_hours,
            'language_requirements' => $request->language_requirements ?? [],
            'gender'                => $request->gender,
            'age_min'               => $request->age_min,
            'age_max'               => $request->age_max,
            'marital_status'        => $request->marital_status,
            'salary_min'            => $request->salary_min,
            'salary_max'            => $request->salary_max,
            'location'              => $request->location,
            'job_description'       => $request->job_description,
            'required_skills'       => $request->required_skills,
            'preferred_skills'      => $request->preferred_skills,
            'application_deadline'  => $request->application_deadline,
            'start_date'            => $request->start_date,
            'special_requirements'  => $request->special_requirements,
            'status'                => $isFreePost ? 'active' : 'draft',
            'is_free_post'          => $isFreePost,
        ]);

        if ($isFreePost) {
            // 無料投稿：即座にactive
            Payment::create([
                'user_id'             => $user->id,
                'payment_type'        => 'job_post',
                'amount'              => 0,
                'is_free'             => true,
                'payment_status'      => 'free',
                'related_job_post_id' => $jobPost->id,
            ]);
            $profile->update(['free_job_post_used' => true]);

            return redirect()->route('company.jobs.index')
                ->with('success', 'Lowongan berhasil diposting!');
        }

        // 有料投稿：draft状態で保存して詳細ページへ（支払い促す）
        return redirect()->route('company.jobs.show', $jobPost->id)
            ->with('needsPayment', true);
    }

    // 求人一覧
    public function index(): Response
    {
        $user = Auth::user();
        $jobs = JobPost::where('company_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Company/Jobs/Index', [
            'jobs' => $jobs,
        ]);
    }

    // 求人詳細
    public function show(int $id): Response
    {
        $user = Auth::user();
        $job  = JobPost::where('id', $id)
            ->where('company_id', $user->id)
            ->firstOrFail();

        $categories      = JobCategories::all();
        $categoryName    = $job->category
            ? ($categories[$job->category]['name'] ?? $job->category)
            : '-';
        $subcategoryName = ($job->category && $job->subcategory)
            ? ($categories[$job->category]['subcategories'][$job->subcategory] ?? $job->subcategory)
            : '-';

        // 支払い待ちか確認
        $needsPayment = session('needsPayment', false);

        return Inertia::render('Company/Jobs/Show', [
            'job'             => $job,
            'categoryName'    => $categoryName,
            'subcategoryName' => $subcategoryName,
            'needsPayment'    => $needsPayment,
        ]);
    }

    // 求人編集フォーム
    public function edit(int $id): Response
    {
        $user = Auth::user();
        $job  = JobPost::where('id', $id)
            ->where('company_id', $user->id)
            ->firstOrFail();

        return Inertia::render('Company/Jobs/Edit', [
            'job'        => $job,
            'categories' => JobCategories::all(),
        ]);
    }

    // 求人更新
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $job  = JobPost::where('id', $id)
            ->where('company_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'title'                 => 'required|string|max:255',
            'category'              => 'nullable|string|max:100',
            'subcategory'           => 'nullable|string|max:100',
            'employment_type'       => 'required|string|max:50',
            'education_requirement' => 'nullable|string|max:100',
            'experience_level'      => 'nullable|string|max:100',
            'working_days'          => 'nullable|array',
            'working_hours'         => 'nullable|string|max:100',
            'language_requirements' => 'nullable|array',
            'gender'                => 'nullable|string|max:50',
            'age_min'               => 'nullable|integer|min:15|max:99',
            'age_max'               => 'nullable|integer|min:15|max:99',
            'marital_status'        => 'nullable|string|max:50',
            'salary_min'            => 'nullable|integer|min:0',
            'salary_max'            => 'nullable|integer|min:0',
            'location'              => 'required|string|max:255',
            'job_description'       => 'required|string',
            'required_skills'       => 'nullable|string',
            'preferred_skills'      => 'nullable|string',
            'application_deadline'  => 'required|date',
            'start_date'            => 'nullable|date',
            'special_requirements'  => 'nullable|string',
            'workplace_photo'       => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'status'                => 'required|in:active,closed',
        ]);

        $workplacePhotoPath = $job->workplace_photo;
        if ($request->hasFile('workplace_photo')) {
            if ($workplacePhotoPath) {
                Storage::disk('public')->delete($workplacePhotoPath);
            }
            $workplacePhotoPath = $request->file('workplace_photo')
                ->store('workplace_photos', 'public');
        }

        $job->update([
            'title'                 => $request->title,
            'workplace_photo'       => $workplacePhotoPath,
            'category'              => $request->category,
            'subcategory'           => $request->subcategory,
            'employment_type'       => $request->employment_type,
            'education_requirement' => $request->education_requirement,
            'experience_level'      => $request->experience_level,
            'working_days'          => $request->working_days ?? [],
            'working_hours'         => $request->working_hours,
            'language_requirements' => $request->language_requirements ?? [],
            'gender'                => $request->gender,
            'age_min'               => $request->age_min,
            'age_max'               => $request->age_max,
            'marital_status'        => $request->marital_status,
            'salary_min'            => $request->salary_min,
            'salary_max'            => $request->salary_max,
            'location'              => $request->location,
            'job_description'       => $request->job_description,
            'required_skills'       => $request->required_skills,
            'preferred_skills'      => $request->preferred_skills,
            'application_deadline'  => $request->application_deadline,
            'start_date'            => $request->start_date,
            'special_requirements'  => $request->special_requirements,
            'status'                => $request->status,
        ]);

        return redirect()->route('company.jobs.index')
            ->with('success', 'Lowongan berhasil diperbarui!');
    }

    // 求人削除（soft delete → status = deleted）
    public function destroy(int $id): RedirectResponse
    {
        $user = Auth::user();
        $job  = JobPost::where('id', $id)
            ->where('company_id', $user->id)
            ->firstOrFail();

        $job->update(['status' => 'deleted']);

        return redirect()->route('company.jobs.index')
            ->with('success', 'Lowongan berhasil dihapus.');
    }
}