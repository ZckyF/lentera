<?php

namespace App\Livewire\Forms\Auth;

use Livewire\Form;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ProfileForm extends Form
{
    public $name;
    public $identifier;
    public $password;
    public $password_confirmation;

    public function setProfile($user)
    {
        $this->name = $user->name;
        $this->identifier = $user->identifier;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|min:3',
            'password' => 'nullable|confirmed|min:6',
        ];
    
        if (auth()->user()->role === 'admin') {
            $rules['identifier'] = [
                'required',
                Rule::unique('users', 'identifier')->ignore(auth()->id()),
            ];
        }
    
        return $rules;
    }
    
    public function update()
    {
        $this->validate();
    
        $user = auth()->user();
        $data = ['name' => $this->name];
    
        if ($user->role === 'admin') {
            $data['identifier'] = $this->identifier;
        }
    
        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }
    
        $user->update($data);
        $this->reset(['password', 'password_confirmation']);
    }
}