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
            'education_level'      => 'required|string',
            'school_name'          => 'required|string|max:255',
            'school_location'      => 'nullable|string|max:255',
            'degree_name'          => 'nullable|string|max:255',
            'enrollment_date'      => 'nullable|date',
            'graduation_date'      => 'nullable|date',
            'graduation_status'    => 'nullable|string',
            'ipk_gpa'              => 'nullable|numeric|min:0|max:4',
            'academic_achievements'=> 'nullable|string',
            'ijazah_transcript'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
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
            'education_level'      => 'required|string',
            'school_name'          => 'required|string|max:255',
            'school_location'      => 'nullable|string|max:255',
            'degree_name'          => 'nullable|string|max:255',
            'enrollment_date'      => 'nullable|date',
            'graduation_date'      => 'nullable|date',
            'graduation_status'    => 'nullable|string',
            'ipk_gpa'              => 'nullable|numeric|min:0|max:4',
            'academic_achievements'=> 'nullable|string',
            'ijazah_transcript'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
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
            'company_name'           => 'required|string|max:255',
            'company_address'        => 'nullable|string|max:255',
            'department_position'    => 'required|string|max:255',
            'employment_type'        => 'required|string',
            'employment_start_date'  => 'required|date',
            'employment_end_date'    => 'nullable|date',
            'job_description'        => 'nullable|string',
            'resignation_reason'     => 'nullable|string',
            'employment_achievements'=> 'nullable|string',
            'supervisor_full_name'   => 'nullable|string|max:255',
            'supervisor_position'    => 'nullable|string|max:255',
            'supervisor_phone'       => 'nullable|string|max:255',
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
            'company_name'           => 'required|string|max:255',
            'company_address'        => 'nullable|string|max:255',
            'department_position'    => 'required|string|max:255',
            'employment_type'        => 'required|string',
            'employment_start_date'  => 'required|date',
            'employment_end_date'    => 'nullable|date',
            'job_description'        => 'nullable|string',
            'resignation_reason'     => 'nullable|string',
            'employment_achievements'=> 'nullable|string',
            'supervisor_full_name'   => 'nullable|string|max:255',
            'supervisor_position'    => 'nullable|string|max:255',
            'supervisor_phone'       => 'nullable|string|max:255',
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
            'certificate_name'      => 'required|string|max:255',
            'issuing_organization'  => 'required|string|max:255',
            'issue_date'            => 'required|date',
            'expiration_date'       => 'nullable|date',
            'certificate_score'     => 'nullable|string|max:100',
            'certificate_notes'     => 'nullable|string',
            'certificate_file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'certificate_attachment'=> 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except(['certificate_file', 'certificate_attachment']);
        $data['user_id'] = Auth::id();
        $data['certification_request_id'] = null;

        if ($request->hasFile('certificate_file')) {
            $data['certificate_file'] = $request->file('certificate_file')
                ->store('cv/certifications', 'public');
        }
        if ($request->hasFile('certificate_attachment')) {
            $data['certificate_attachment'] = $request->file('certificate_attachment')
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
            'certificate_name'      => 'required|string|max:255',
            'issuing_organization'  => 'required|string|max:255',
            'issue_date'            => 'required|date',
            'expiration_date'       => 'nullable|date',
            'certificate_score'     => 'nullable|string|max:100',
            'certificate_notes'     => 'nullable|string',
            'certificate_file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'certificate_attachment'=> 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except(['certificate_file', 'certificate_attachment']);

        if ($request->hasFile('certificate_file')) {
            if ($item->certificate_file) {
                Storage::disk('public')->delete($item->certificate_file);
            }
            $data['certificate_file'] = $request->file('certificate_file')
                ->store('cv/certifications', 'public');
        }
        if ($request->hasFile('certificate_attachment')) {
            if ($item->certificate_attachment) {
                Storage::disk('public')->delete($item->certificate_attachment);
            }
            $data['certificate_attachment'] = $request->file('certificate_attachment')
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
        if ($item->certificate_attachment) {
            Storage::disk('public')->delete($item->certificate_attachment);
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

        $profile = \App\Models\ApplicantProfile::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'member_id'                     => 'HRIM-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 7)),
                'free_certification_used'       => false,
                'free_certification_expires_at' => now()->addDays(90),
                'certification_status'          => 'not_applied',
            ]
        );

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