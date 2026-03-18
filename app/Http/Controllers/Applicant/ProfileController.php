<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\ApplicantProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show()
    {
        $profile = ApplicantProfile::where('user_id', Auth::id())->first();

        return Inertia::render('Applicant/Profile', [
            'profile' => $profile,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'desired_position' => ['nullable', 'string', 'max:255'],
            'desired_salary'   => ['nullable', 'integer', 'min:0'],
            'facebook_url'     => ['nullable', 'url', 'max:255'],
            'linkedin_url'     => ['nullable', 'url', 'max:255'],
            'instagram_url'    => ['nullable', 'url', 'max:255'],
            'self_pr'          => ['nullable', 'string'],
            'profile_photo'    => ['nullable', 'image', 'max:2048'],
        ]);

        $profile = ApplicantProfile::where('user_id', Auth::id())->first();

        $data = [
            'desired_position' => $request->desired_position,
            'desired_salary'   => $request->desired_salary,
            'facebook_url'     => $request->facebook_url,
            'linkedin_url'     => $request->linkedin_url,
            'instagram_url'    => $request->instagram_url,
            'self_pr'          => $request->self_pr,
        ];

        // 顔写真アップロード
        if ($request->hasFile('profile_photo')) {
            if ($profile->profile_photo) {
                Storage::disk('public')->delete($profile->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $data['profile_photo'] = $path;
        }

        $profile->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}