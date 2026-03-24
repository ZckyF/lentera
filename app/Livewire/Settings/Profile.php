<?php

namespace App\Livewire\Settings;

use App\Livewire\Forms\Auth\ProfileForm;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.settings')]
#[Title('Profil')]
class Profile extends Component
{
    public ProfileForm $form;

    public function mount()
    {
        $this->form->setProfile(auth()->user());
    }

    public function save()
    {
        $this->form->update();
        session()->flash('message', 'Profil berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.settings.profile');
    }
}