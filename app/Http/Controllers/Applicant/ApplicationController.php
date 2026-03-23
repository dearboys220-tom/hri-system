<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $applications = JobApplication::with(['jobPost.company'])
            ->where('applicant_id', $user->id)
            ->orderBy('applied_at', 'desc')
            ->get()
            ->map(function ($app) {
                $job = $app->jobPost;

                return [
                    'id'            => $app->id,
                    'status'        => $app->status,
                    'applied_at'    => $app->applied_at,
                    'company_notes' => $app->company_notes,
                    'job_deleted'   => $app->job_deleted,
                    'job'           => $job ? [
                        'id'                  => $job->id,
                        'title'               => $job->title,
                        'location'            => $job->location,
                        'employment_type'     => $job->employment_type,
                        'application_deadline'=> $job->application_deadline,
                        'status'              => $job->status,
                        'company_name'        => optional($job->company)->name,
                    ] : null,
                ];
            });

        return Inertia::render('Applicant/Applications', [
            'applications' => $applications,
        ]);
    }
}