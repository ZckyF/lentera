<?php

namespace App\Livewire\Forms\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string')]
    public string $identifier = '';

    #[Validate('required|string|min:8')]
    public string $password = '';

    public function messages() 
    {
        return [
            'identifier.required' => 'Identitas wajib diisi.',
            'identifier.string'   => 'Identitas harus berupa string.',
            'password.required'   => 'Kata sandi wajib diisi.',
            'password.string'     => 'Kata sandi harus berupa string.',
            'password.min'        => 'Kata sandi minimal harus 8 karakter.'
        ];
    }

    public function store(): bool
    {
        $this->validate();

        if (! Auth::attempt(['identifier' => $this->identifier, 'password' => $this->password], true)) {
            $this->addError('identifier', "Identitas atau Kata sandi salah.");

            return false;
        }

        return true;
    }
}

