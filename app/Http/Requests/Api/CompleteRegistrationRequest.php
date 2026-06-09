<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CompleteRegistrationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Step 1: Data tim
            'team_name' => 'required|string|max:100|unique:teams,team_name',
            'institution' => 'nullable|string|max:200',
            'city' => 'required|string|max:100',

            // Step 2: Anggota (3 orang: ketua, anggota1, anggota2)
            'members' => 'required|array|min:3|max:3',
            'members.*.name' => 'required|string|max:100',
            'members.*.email' => 'required|email|distinct',
            'members.*.phone' => 'nullable|string|max:20',
            'members.*.nim' => 'required|string|max:50',
            'members.*.faculty' => 'required|string|max:100',
            'members.*.study_program' => 'required|string|max:100',
            'members.*.position' => 'required|in:ketua,anggota1,anggota2',

            // Step 3: Dokumen (file & link) - semua file diubah menjadi 100MB
            'hak_cipta' => 'required|file|mimes:pdf,doc,docx|max:102400',      // 100MB
            'komitmen' => 'required|file|mimes:pdf,doc,docx|max:102400',
            'rekomendasi' => 'required|file|mimes:pdf,doc,docx|max:102400',
            'video_link' => 'required|url|max:500',
            'summary_brief' => 'required|file|mimes:pdf,doc,docx|max:102400',  // 100MB
            'ktm_ketua' => 'required|file|image|mimes:jpg,jpeg,png|max:102400', // 100MB
            'ktm_anggota1' => 'required|file|image|mimes:jpg,jpeg,png|max:102400',
            'ktm_anggota2' => 'required|file|image|mimes:jpg,jpeg,png|max:102400',

            // Step 4: Konfirmasi
            'agree_privacy' => 'required|accepted',
            'agree_truth' => 'required|accepted',
        ];
    }

    public function messages()
    {
        return [
            'team_name.required' => 'Nama tim wajib diisi.',
            'team_name.unique' => 'Nama tim sudah digunakan.',
            'city.required' => 'Kota/Wilayah wajib diisi.',
            'members.*.nim.required' => 'NIM anggota wajib diisi.',
            'members.*.faculty.required' => 'Fakultas anggota wajib diisi.',
            'members.*.study_program.required' => 'Program studi anggota wajib diisi.',
            'hak_cipta.required' => 'Surat pernyataan hak cipta wajib diunggah.',
            'hak_cipta.max' => 'Ukuran file hak cipta tidak boleh lebih dari 100MB.',
            'komitmen.required' => 'Surat komitmen wajib diunggah.',
            'komitmen.max' => 'Ukuran file komitmen tidak boleh lebih dari 100MB.',
            'rekomendasi.required' => 'Surat rekomendasi universitas wajib diunggah.',
            'rekomendasi.max' => 'Ukuran file rekomendasi tidak boleh lebih dari 100MB.',
            'video_link.required' => 'Link video portofolio wajib diisi.',
            'summary_brief.required' => 'Summary brief konsep proyek wajib diunggah.',
            'summary_brief.max' => 'Ukuran file summary brief tidak boleh lebih dari 100MB.',
            'ktm_ketua.required' => 'Foto KTM ketua wajib diunggah.',
            'ktm_ketua.max' => 'Ukuran file KTM ketua tidak boleh lebih dari 100MB.',
            'ktm_anggota1.required' => 'Foto KTM anggota 1 wajib diunggah.',
            'ktm_anggota1.max' => 'Ukuran file KTM anggota 1 tidak boleh lebih dari 100MB.',
            'ktm_anggota2.required' => 'Foto KTM anggota 2 wajib diunggah.',
            'ktm_anggota2.max' => 'Ukuran file KTM anggota 2 tidak boleh lebih dari 100MB.',
            'agree_privacy.accepted' => 'Anda harus menyetujui kebijakan privasi.',
            'agree_truth.accepted' => 'Anda harus menyatakan kebenaran data.',
        ];
    }
}