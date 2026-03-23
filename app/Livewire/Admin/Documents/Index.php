<?php

namespace App\Livewire\Admin\Documents;

use App\Models\Document;
use App\Livewire\Forms\Admin\DocumentForm;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Dokumen')]
class Index extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public DocumentForm $form;

    public $isEditing = false;
    public $isAdding = false;
    public bool $isViewing = false;
    public bool $showHistory = false;
    public $docIdBeingDeleted = null;
    public $docIdBeingRestored = null;

    public function create()
    {
        $this->cancel();
        $this->isAdding = true;
    }

    public function store()
    {
        $this->form->store();
        $this->isAdding = false;
        session()->flash('message', 'Dokumen berhasil diunggah.');
    }

    public function edit(Document $document)
    {
        $this->cancel();
        $this->form->setDocument($document);
        $this->isEditing = true;
    }

    public function update()
    {
        $this->form->update();
        $this->isEditing = false;
        session()->flash('message', 'Dokumen berhasil diperbarui.');
    }

    public function view($id)
    {
        $this->cancel();
        $doc = Document::withTrashed()->findOrFail($id);
        $this->form->setDocument($doc);
        $this->isViewing = true;
    }

    public function confirmDelete($id)
    {
        $this->docIdBeingDeleted = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete()
    {
        Document::findOrFail($this->docIdBeingDeleted)->delete();
        $this->docIdBeingDeleted = null;
        $this->dispatch('hide-delete-modal');
        session()->flash('message', 'Dokumen dipindahkan ke arsip.');
    }

    public function confirmRestore($id)
    {
        $this->docIdBeingRestored = $id;
        $this->dispatch('show-restore-modal');
    }

    public function restore()
    {
        Document::withTrashed()->findOrFail($this->docIdBeingRestored)->restore();
        $this->docIdBeingRestored = null;
        $this->dispatch('hide-restore-modal');
        session()->flash('message', 'Dokumen berhasil dipulihkan.');
    }

    public function toggleHistory()
    {
        $this->showHistory = !$this->showHistory;
        $this->cancel();
        $this->resetPage();
    }

    public function cancel()
    {
        $this->isEditing = false;
        $this->isAdding = false;
        $this->isViewing = false;
        if (isset($this->form)) {
            $this->form->reset(); 
        }
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = $this->showHistory 
            ? Document::onlyTrashed() 
            : Document::query();

        return view('livewire.admin.documents.index', [
            'documents' => $query->latest()->paginate(10)
        ]);
    }
}

