<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\CompanyProfile;
use App\Models\JobApplication;
use App\Models\Bookmark;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicJobController extends Controller
{
    public function index(Request $request): Response
    {
        $query    = $request->input('q', '');
        $category = $request->input('category', '');
        $location = $request->input('location', '');
        $type     = $request->input('type', '');

        $jobs = JobPost::where('status', 'active')
            ->when($query, fn($q) =>
                $q->where(function ($q2) use ($query) {
                    $q2->where('title', 'like', "%{$query}%")
                       ->orWhere('job_description', 'like', "%{$query}%");
                })
            )
            ->when($category, fn($q) => $q->where('job_category_id', $category))
            ->when($location, fn($q) => $q->where('location', 'like', "%{$location}%"))
            ->when($type,     fn($q) => $q->where('employment_type', $type))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // 企業情報・カテゴリ名を付加
        $jobs->getCollection()->transform(function ($job) {
            $company           = CompanyProfile::where('user_id', $job->company_id)->first();
            $job->company_name = $company?->company_name ?? '-';
            $job->company_logo = $company?->company_logo;

            if ($job->job_category_id) {
                $cat               = DB::table('job_categories')->find($job->job_category_id);
                $job->category_name = $cat?->name ?? '';
            } else {
                $job->category_name = $job->category ?? '';
            }
            return $job;
        });

        // 親カテゴリ＋子カテゴリ階層
        $parentCategories   = DB::table('job_categories')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        $categoriesGrouped = $parentCategories->map(function ($parent) {
            $children = DB::table('job_categories')
                ->where('parent_id', $parent->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name']);
            return [
                'id'       => $parent->id,
                'name'     => $parent->name,
                'children' => $children,
            ];
        });

        return Inertia::render('Jobs/Index', [
            'jobs'              => $jobs,
            'filters'           => compact('query', 'category', 'location', 'type'),
            'categoriesGrouped' => $categoriesGrouped,
        ]);
    }

    public function show(int $id): Response
    {
        $job = JobPost::where('id', $id)
            ->where('status', 'active')
            ->firstOrFail();

        $company = CompanyProfile::where('user_id', $job->company_id)->first();

        // カテゴリ名を job_categories テーブルから取得
        $categoryName    = null;
        $subcategoryName = null;

        if ($job->job_category_id) {
            $cat = DB::table('job_categories')->find($job->job_category_id);
            if ($cat) {
                if ($cat->parent_id) {
                    // サブカテゴリの場合：親名＋子名
                    $parent          = DB::table('job_categories')->find($cat->parent_id);
                    $categoryName    = $parent?->name ?? $cat->name;
                    $subcategoryName = $cat->name;
                } else {
                    // 親カテゴリのみ
                    $categoryName = $cat->name;
                }
            }
        } else {
            // 旧データ（job_category_id なし）は category カラムをそのまま使用
            $categoryName = $job->category ?? null;
        }

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
}