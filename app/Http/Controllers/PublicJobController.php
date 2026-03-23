<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\CompanyProfile;
use App\Models\JobApplication;
use App\Models\Bookmark;
use App\Data\JobCategories;
use Inertia\Inertia;
use Inertia\Response;

class PublicJobController extends Controller
{
    public function show(int $id): Response
    {
        $job = JobPost::where('id', $id)
            ->where('status', 'active')
            ->firstOrFail();

        $company = CompanyProfile::where('user_id', $job->company_id)->first();

        $categories = JobCategories::all();
        $categoryName = $job->category
            ? ($categories[$job->category]['name'] ?? $job->category)
            : null;
        $subcategoryName = ($job->category && $job->subcategory)
            ? ($categories[$job->category]['subcategories'][$job->subcategory] ?? $job->subcategory)
            : null;

        $alreadyApplied = false;
        $isBookmarked   = false;

        if (auth()->check() && auth()->user()->role_type === 'applicant') {
            $alreadyApplied = JobApplication::where('job_post_id', $id)
                ->where('applicant_id', auth()->id())
                ->exists();

            $isBookmarked = Bookmark::where('job_post_id', $id)
                ->where('user_id', auth()->id())
                ->exists();
        }

        return Inertia::render('Jobs/Show', [
            'job'             => $job,
            'company'         => $company,
            'categoryName'    => $categoryName,
            'subcategoryName' => $subcategoryName,
            'alreadyApplied'  => $alreadyApplied,
            'isBookmarked'    => $isBookmarked,
        ]);
    }

    public function index(): Response
    {
        $jobs = JobPost::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($job) {
                $company = CompanyProfile::where('user_id', $job->company_id)->first();
                return array_merge($job->toArray(), [
                    'company_name' => $company?->company_name ?? '-',
                    'company_logo' => $company?->company_logo,
                ]);
            });

        return Inertia::render('Jobs/Index', [
            'jobs' => $jobs,
        ]);
    }
}