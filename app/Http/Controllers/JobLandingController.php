<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobLandingController extends Controller
{
    public function index(Request $request)
    {
        $query    = $request->input('q', '');
        $category = $request->input('category', '');
        $location = $request->input('location', '');

        $jobs = JobPost::where('status', 'published')
            ->when($query, fn($q) =>
                $q->where(function ($q2) use ($query) {
                    $q2->where('title', 'like', "%{$query}%")
                       ->orWhere('job_description', 'like', "%{$query}%");
                })
            )
            ->when($category, fn($q) => $q->where('job_category_id', $category))
            ->when($location, fn($q) => $q->where('location', 'like', "%{$location}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        // 親カテゴリ＋サブカテゴリを階層で取得
        $parentCategories = DB::table('job_categories')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug']);

        $categoriesGrouped = $parentCategories->map(function ($parent) {
            $children = DB::table('job_categories')
                ->where('parent_id', $parent->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug']);
            return [
                'id'       => $parent->id,
                'name'     => $parent->name,
                'slug'     => $parent->slug,
                'children' => $children,
            ];
        });

        $totalJobs = JobPost::where('status', 'published')->count();

        return Inertia::render('JobLanding', [
            'jobs'               => $jobs,
            'filters'            => compact('query', 'category', 'location'),
            'categoriesGrouped'  => $categoriesGrouped,
            'totalJobs'          => $totalJobs,
        ]);
    }
}