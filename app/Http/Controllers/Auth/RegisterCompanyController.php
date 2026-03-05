<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RegisterCompanyController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/RegisterCompany');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'nib' => ['required', 'string', 'max:100'],
            'pic_name' => ['required', 'string', 'max:255'],
            'pic_position' => ['required', 'string', 'max:255'],
            'pic_phone' => ['required', 'regex:/^[0-9+\-\s]+$/', 'max:20'],
            'company_email' => ['required', 'email:rfc,dns', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'akta_pendirian' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048'
            ],
        ], [
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'nib.required' => 'NIB wajib diisi.',
            'pic_name.required' => 'Nama penanggung jawab wajib diisi.',
            'pic_position.required' => 'Jabatan penanggung jawab wajib diisi.',
            'pic_phone.required' => 'Nomor penanggung jawab wajib diisi.',
            'pic_phone.regex' => 'Format nomor telepon tidak valid.',
            'company_email.required' => 'Email perusahaan wajib diisi.',
            'company_email.email' => 'Format email tidak valid.',
            'company_email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
            'akta_pendirian.required' => 'Dokumen surat kuasa wajib diupload.',
            'akta_pendirian.mimes' => 'File harus berupa PDF, JPG, JPEG, atau PNG.',
            'akta_pendirian.max' => 'Ukuran file maksimal 2MB.',
        ]);

        DB::beginTransaction();

        try {
            $date = now()->format('Ymd');
            $companyName = \Illuminate\Support\Str::slug($request->company_name);
            $extension = $request->file('akta_pendirian')->getClientOriginalExtension();

            $fileName = $date . '_' . $companyName . '.' . $extension;

            $filePath = $request->file('akta_pendirian')
                ->storeAs('company/surat_kuasa', $fileName, 'public');

            $user = User::create([
                'name' => $request->company_name,
                'email' => $request->company_email,
                'password' => Hash::make($request->password),
                'role_type'   => RoleType::COMPANY,
            ]);

            $user->assignRole('company');

            $user->companyProfile()->create([
                'company_name' => $request->company_name,
                'nib' => $request->nib,
                'pic_name' => $request->pic_name,
                'pic_position' => $request->pic_position,
                'pic_phone' => $request->pic_phone,
                'akta_pendirian' => $filePath,
                'company_verification_status' => 'pending',
                'free_job_post_used' => false,
            ]);

            DB::commit();

            Auth::guard('web')->login($user);
            
            return redirect()->route('company.dashboard');

        } catch (\Exception $e) {

            DB::rollBack();

            if (isset($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            throw $e;
        }
    }
}