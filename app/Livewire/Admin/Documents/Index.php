<?php

namespace App\Livewire\Admin\Documents;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dokumen')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.admin.documents.index');
    }
}

