<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\ApplicantProfile;
use App\Models\EducationHistory;
use App\Models\WorkHistory;
use App\Models\Certification;

class CvController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $profile = ApplicantProfile::where('user_id', $user->id)->first();

        $educations = EducationHistory::where('user_id', $user->id)->get();
        $works      = WorkHistory::where('user_id', $user->id)->get();
        $certs      = Certification::where('user_id', $user->id)->get();

        return Inertia::render('Applicant/Cv', [
            'profile'    => $profile,
            'educations' => $educations,
            'works'      => $works,
            'certs'      => $certs,
        ]);
    }

    // ===== 学歴 =====
    public function storeEducation(Request $request)
    {
        $request->validate([
            'level'             => 'required|string',
            'school'            => 'required|string|max:255',
            'school_location'   => 'nullable|string|max:255',
            'major'             => 'nullable|string|max:255',
            'enrollment_date'   => 'nullable|date',
            'graduation_date'   => 'nullable|date',
            'graduation_status' => 'nullable|string',
            'gpa'               => 'nullable|numeric|min:0|max:4',
            'achievements'      => 'nullable|string',
            'ijazah_transcript' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except('ijazah_transcript');
        $data['user_id'] = Auth::id();
        $data['certification_request_id'] = null;

        if ($request->hasFile('ijazah_transcript')) {
            $data['ijazah_transcript'] = $request->file('ijazah_transcript')
                ->store('cv/education', 'public');
        }

        EducationHistory::create($data);
        return back()->with('success', 'Data pendidikan berhasil disimpan.');
    }

    public function updateEducation(Request $request, $id)
    {
        $item = EducationHistory::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();

        $request->validate([
            'level'             => 'required|string',
            'school'            => 'required|string|max:255',
            'school_location'   => 'nullable|string|max:255',
            'major'             => 'nullable|string|max:255',
            'enrollment_date'   => 'nullable|date',
            'graduation_date'   => 'nullable|date',
            'graduation_status' => 'nullable|string',
            'gpa'               => 'nullable|numeric|min:0|max:4',
            'achievements'      => 'nullable|string',
            'ijazah_transcript' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except('ijazah_transcript');

        if ($request->hasFile('ijazah_transcript')) {
            if ($item->ijazah_transcript) {
                Storage::disk('public')->delete($item->ijazah_transcript);
            }
            $data['ijazah_transcript'] = $request->file('ijazah_transcript')
                ->store('cv/education', 'public');
        }

        $item->update($data);
        return back()->with('success', 'Data pendidikan berhasil diperbarui.');
    }

    public function destroyEducation($id)
    {
        $item = EducationHistory::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();
        if ($item->ijazah_transcript) {
            Storage::disk('public')->delete($item->ijazah_transcript);
        }
        $item->delete();
        return back()->with('success', 'Data pendidikan dihapus.');
    }

    // ===== 職歴 =====
    public function storeWork(Request $request)
    {
        $request->validate([
            'company'                => 'required|string|max:255',
            'company_address'        => 'nullable|string|max:255',
            'position'               => 'required|string|max:255',
            'employment_type'        => 'required|string',
            'start_date'             => 'required|date',
            'end_date'               => 'nullable|date',
            'duties'                 => 'nullable|string',
            'resignation_reason'     => 'nullable|string',
            'achievements'           => 'nullable|string',
            'supervisor_name'        => 'nullable|string|max:255',
            'supervisor_position'    => 'nullable|string|max:255',
            'supervisor_contact'     => 'nullable|string|max:255',
            'employment_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except('employment_certificate');
        $data['user_id'] = Auth::id();
        $data['certification_request_id'] = null;

        if ($request->hasFile('employment_certificate')) {
            $data['employment_certificate'] = $request->file('employment_certificate')
                ->store('cv/work', 'public');
        }

        WorkHistory::create($data);
        return back()->with('success', 'Data pekerjaan berhasil disimpan.');
    }

    public function updateWork(Request $request, $id)
    {
        $item = WorkHistory::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();

        $request->validate([
            'company'                => 'required|string|max:255',
            'company_address'        => 'nullable|string|max:255',
            'position'               => 'required|string|max:255',
            'employment_type'        => 'required|string',
            'start_date'             => 'required|date',
            'end_date'               => 'nullable|date',
            'duties'                 => 'nullable|string',
            'resignation_reason'     => 'nullable|string',
            'achievements'           => 'nullable|string',
            'supervisor_name'        => 'nullable|string|max:255',
            'supervisor_position'    => 'nullable|string|max:255',
            'supervisor_contact'     => 'nullable|string|max:255',
            'employment_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except('employment_certificate');

        if ($request->hasFile('employment_certificate')) {
            if ($item->employment_certificate) {
                Storage::disk('public')->delete($item->employment_certificate);
            }
            $data['employment_certificate'] = $request->file('employment_certificate')
                ->store('cv/work', 'public');
        }

        $item->update($data);
        return back()->with('success', 'Data pekerjaan berhasil diperbarui.');
    }

    public function destroyWork($id)
    {
        $item = WorkHistory::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();
        if ($item->employment_certificate) {
            Storage::disk('public')->delete($item->employment_certificate);
        }
        $item->delete();
        return back()->with('success', 'Data pekerjaan dihapus.');
    }

    // ===== 資格 =====
    public function storeCertification(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'organization'      => 'required|string|max:255',
            'issued_date'       => 'required|date',
            'valid_until'       => 'nullable|date',
            'certificate_score' => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
            'certificate_file'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except('certificate_file');
        $data['user_id'] = Auth::id();
        $data['certification_request_id'] = null;

        if ($request->hasFile('certificate_file')) {
            $data['certificate_file'] = $request->file('certificate_file')
                ->store('cv/certifications', 'public');
        }

        Certification::create($data);
        return back()->with('success', 'Data sertifikasi berhasil disimpan.');
    }

    public function updateCertification(Request $request, $id)
    {
        $item = Certification::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();

        $request->validate([
            'name'              => 'required|string|max:255',
            'organization'      => 'required|string|max:255',
            'issued_date'       => 'required|date',
            'valid_until'       => 'nullable|date',
            'certificate_score' => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
            'certificate_file'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except('certificate_file');

        if ($request->hasFile('certificate_file')) {
            if ($item->certificate_file) {
                Storage::disk('public')->delete($item->certificate_file);
            }
            $data['certificate_file'] = $request->file('certificate_file')
                ->store('cv/certifications', 'public');
        }

        $item->update($data);
        return back()->with('success', 'Data sertifikasi berhasil diperbarui.');
    }

    public function destroyCertification($id)
    {
        $item = Certification::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();
        if ($item->certificate_file) {
            Storage::disk('public')->delete($item->certificate_file);
        }
        $item->delete();
        return back()->with('success', 'Data sertifikasi dihapus.');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'full_name'        => ['required', 'string'],
            'gender'           => ['required', 'string'],
            'birth_date'       => ['required', 'date'],
            'nationality'      => ['required', 'string'],
            'marital_status'   => ['required', 'string'],
            'phone_number'     => ['required', 'string'],
            'whatsapp_number'  => ['nullable', 'string'],
            'current_address'  => ['required', 'string'],
            'self_pr'          => ['nullable', 'string'],
            'profile_photo'    => ['nullable', 'image', 'max:2048'],
        ]);
        
        $profile = \App\Models\ApplicantProfile::where('user_id', Auth::id())->first();
        
        $data = $request->only([
            'full_name', 'gender', 'birth_date', 'nationality',
            'marital_status', 'phone_number', 'whatsapp_number',
            'current_address', 'self_pr',
        ]);
        
        if ($request->hasFile('profile_photo')) {
            if ($profile->profile_photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($profile->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }
        
        $profile->update($data);
        
        return back()->with('success', 'Informasi dasar berhasil disimpan.');
    }
}