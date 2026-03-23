<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Forms\Admin\UserForm;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Pengguna')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public UserForm $form;

    public $isEditing = false;
    public $isAdding = false;
    public bool $isViewing = false;
    public bool $showHistory = false;
    public $userIdBeingDeleted = null;
    public $userIdBeingRestored = null;

    public function view($id)
    {
        $this->cancel();
        $user = User::withTrashed()->findOrFail($id);
        
        $this->form->setUser($user);
        $this->isViewing = true;
    }

    public function create()
    {
        $this->cancel();
        $this->isAdding = true;
    }

    public function store()
    {
        $this->form->store();
        
        $this->isAdding = false;
        session()->flash('message', 'Pengguna berhasil ditambahkan.');
    }

    public function confirmRestore($id)
    {
        $this->userIdBeingRestored = $id;

        $this->dispatch('show-restore-modal');
    }

    public function restore()
    {
        if ($this->userIdBeingRestored) {
            $user = User::withTrashed()->findOrFail($this->userIdBeingRestored);
            $user->restore();
            $this->userIdBeingRestored = null;
            $this->dispatch('hide-restore-modal');   
            session()->flash('message', 'Pengguna "' . $user->name . '" berhasil dipulihkan.');
        }
    }

    public function edit(User $user)
    {
        $this->cancel();
        $this->form->setUser($user);
        $this->isEditing = true;
    }

    public function update()
    {
        $this->form->update();
        $this->isEditing = false;
        session()->flash('message', 'Pengguna berhasil diperbarui.');
    }

    public function confirmDelete($id)
    {
        $this->userIdBeingDeleted = $id;

        $this->dispatch('show-delete-modal');

        
    }

    public function delete()
    {
        if ($this->userIdBeingDeleted) {
            $user = User::findOrFail($this->userIdBeingDeleted);
            $user->delete();

            $this->userIdBeingDeleted = null;
            $this->dispatch('hide-delete-modal');
            session()->flash('message', 'Pengguna berhasil diarsipkan.');
        }
    }

    public function cancel()
    {
        $this->form->reset();
        $this->isEditing = false;
        $this->isAdding = false;
        $this->isViewing = false;
        $this->resetErrorBag();
    }

    public function toggleHistory()
    {
        $this->showHistory = !$this->showHistory;
        $this->cancel();
        $this->resetPage();
    }
    
    public function render()
    {
        $query = $this->showHistory 
        ? User::onlyTrashed() 
        : User::query();

        $users = $query->where('id','!=',auth()->user()->id)->latest()->paginate(10);

        return view('livewire.admin.users.index',[
            'users' => $users
        ]);
    }
}

