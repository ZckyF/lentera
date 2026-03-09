<?php

namespace App\Livewire\Forms\Admin;

use App\Models\User;
use Livewire\Form;
use Illuminate\Validation\Rule;

class UserForm extends Form
{
    public ?User $user;

    public $name = '';
    public $identifier = '';
    public $role = '';
    public $status = 'pending';
    public $password = '';

    public function setUser(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->identifier = $user->identifier;
        $this->role = $user->role;
        $this->status = $user->status;
    }

    public function rules()
    {
        return [
            'name' => 'required|min:3',
            'identifier' => [
                'required',
                Rule::unique('users', 'identifier')->ignore($this->user?->id),
            ],
            'role' => 'required|in:admin,mahasiswa,dosen,staff',
            'status' => 'required|in:pending,active,inactive',
            'password' => $this->user ? 'nullable|min:6' : 'required|min:6',
        ];
    }

    public function messages() 
    {
        return [
            'name.required'       => 'Nama lengkap wajib diisi.',
            'name.min'            => 'Nama minimal harus 3 karakter.',
            'identifier.required' => 'Identifier (NIM/NIP) tidak boleh kosong.',
            'identifier.unique'   => 'Identifier sudah terdaftar di sistem.',
            'role.required'       => 'Silakan pilih peran pengguna.',
            'role.in'             => 'Peran yang dipilih tidak valid.',
            'status.required'     => 'Status akun wajib ditentukan.',
            'status.in'           => 'Status tidak valid.',
            'password.required'   => 'Kata sandi wajib diisi untuk pengguna baru.',
            'password.min'        => 'Kata sandi minimal harus 6 karakter.',
        ];
    }

    public function store()
    {
        $this->validate();

        User::create([
            'name' => $this->name,
            'identifier' => $this->identifier,
            'role' => $this->role,
            'status' => 'active',
            'password' => bcrypt($this->password),
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'identifier' => $this->identifier,
            'role' => $this->role,
            'status' => $this->status,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        $this->user->update($data);
        $this->reset();
    }
}