<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BookmarkController extends Controller
{
    // ブックマーク一覧
    public function index(): Response
    {
        $user = Auth::user();

        $bookmarks = Bookmark::with(['jobPost.company'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($bookmark) {
                $job = $bookmark->jobPost;
                if (!$job) return null;

                return [
                    'bookmark_id'  => $bookmark->id,
                    'job' => [
                        'id'                   => $job->id,
                        'title'                => $job->title,
                        'location'             => $job->location,
                        'employment_type'      => $job->employment_type,
                        'salary_min'           => $job->salary_min,
                        'salary_max'           => $job->salary_max,
                        'application_deadline' => $job->application_deadline,
                        'status'               => $job->status,
                        'company_name'         => optional($job->company)->name,
                    ],
                ];
            })
            ->filter()
            ->values();

        return Inertia::render('Applicant/Bookmarks', [
            'bookmarks' => $bookmarks,
        ]);
    }

    // ブックマーク追加・削除（トグル）
    public function toggle(Request $request, int $jobPostId)
    {
        $user = Auth::user();

        $existing = Bookmark::where('user_id', $user->id)
            ->where('job_post_id', $jobPostId)
            ->first();

        if ($existing) {
            $existing->delete();
            $bookmarked = false;
        } else {
            Bookmark::create([
                'user_id'     => $user->id,
                'job_post_id' => $jobPostId,
            ]);
            $bookmarked = true;
        }

        return response()->json(['bookmarked' => $bookmarked]);
    }
}