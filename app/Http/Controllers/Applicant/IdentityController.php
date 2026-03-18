<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\ApplicantProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class IdentityController extends Controller
{
    public function show()
    {
        $profile = ApplicantProfile::where('user_id', Auth::id())->first();

        return Inertia::render('Applicant/Identity', [
            'profile' => $profile,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'nik'         => ['required', 'digits:16'],
            'ktp_address' => ['required', 'string'],
            'ktp_card'    => ['nullable', 'image', 'max:2048'],
        ]);

        $profile = ApplicantProfile::where('user_id', Auth::id())->first();

        // NIK重複チェック（自分以外）
        $duplicate = ApplicantProfile::where('nik', $request->nik)
            ->where('id', '!=', $profile->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['nik' => 'NIK sudah digunakan oleh anggota lain.']);
        }

        $data = [
            'nik'         => $request->nik,
            'ktp_address' => $request->ktp_address,
        ];

        // KTP写真アップロード
        if ($request->hasFile('ktp_card')) {
            // 古いファイルを削除
            if ($profile->ktp_card) {
                Storage::disk('public')->delete($profile->ktp_card);
            }
            $path = $request->file('ktp_card')->store('ktp', 'public');
            $data['ktp_card'] = $path;
        }

        $profile->update($data);

        return redirect()->route('applicant.dashboard')
            ->with('success', 'Identitas berhasil disimpan.');
    }
}