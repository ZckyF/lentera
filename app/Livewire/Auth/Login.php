<?php

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\LoginForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Login')]
class Login extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        if (! $this->form->store()) {
            return;
        }

        $user = Auth::user();

        if ($user?->status === 'pending') {
            session(['pending_identifier' => $user->identifier]);

            $this->redirect('/activate', navigate: true);

            return;
        }

        $this->redirect('/dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}

