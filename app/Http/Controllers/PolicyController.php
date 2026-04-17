<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class PolicyController extends Controller
{
    // 有効なスラッグ一覧
    private array $validSlugs = [
        'terms',
        'privacy',
        'cookie',
        'resume-restrictions',
        'investigation',
        'acceptable-use',
        'data-responsibility',
        'liability',
        'dispute-resolution',
        'anti-discrimination',
        'language',
    ];

    public function index()
    {
        return Inertia::render('Policies/Index');
    }

    public function show(string $slug)
    {
        if (!in_array($slug, $this->validSlugs)) {
            abort(404);
        }

        return Inertia::render('Policies/Show', [
            'slug' => $slug,
        ]);
    }
}