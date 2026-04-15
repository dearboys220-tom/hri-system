<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\JobPost;

class CompanyLandingController extends Controller
{
    public function index()
    {
        // 公開中の求人数（企業向けに表示）
        $publishedJobCount = JobPost::where('status', 'published')->count();

        return Inertia::render('CompanyLanding', [
            'publishedJobCount' => $publishedJobCount,
        ]);
    }
}