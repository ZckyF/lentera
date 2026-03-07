<?php

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\ActivateForm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Login')]
class Activate extends Component
{
    public string $identifier = '';

    public ActivateForm $form;

    public function mount(): void
    {
        $identifier = session('pending_identifier');

        if (! $identifier) {
            $this->redirect('/login', navigate: true);

            return;
        }

        $user = User::where('identifier', $identifier)->first();

        if (! $user) {
            $this->redirect('/login', navigate: true);

            return;
        }

        $this->identifier = $user->identifier;
    }

    public function activate(): void
    {
        $user = Auth::user();

        if (! $user) {
            $identifier = session('pending_identifier');

            if ($identifier) {
                $user = User::where('identifier', $identifier)->first();
            }
        }

        if (! $user) {
            $this->redirect('/login', navigate: true);

            return;
        }

        if (! $this->form->updatePassword($user)) {
            return;
        }

        session()->forget('pending_identifier');

        Auth::login($user);

        $this->redirect('/dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.activate');
    }
}

