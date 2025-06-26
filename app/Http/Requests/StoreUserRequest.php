<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\User;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Allow all authenticated users to make this request
    }

    public function rules()
    {
        return [
            'nama' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($this->user),
            ],
            'role' => 'required|in:admin,petugas,inventor', // Update to match form values
            'status' => 'required|in:active,inactive',
            'password' => $this->isMethod('post') ? 'required|min:8|confirmed' : 'nullable|min:8|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'role.required' => 'Role wajib dipilih',
            'role.in' => 'Role yang dipilih tidak valid',
            'status.required' => 'Status wajib dipilih',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok'
        ];
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    }
}