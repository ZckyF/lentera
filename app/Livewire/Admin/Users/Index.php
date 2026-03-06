<?php

namespace App\Livewire\Admin\Users;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Pengguna')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.admin.users.index');
    }
}

