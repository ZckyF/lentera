<?php

namespace App\Livewire\Admin;

use App\Models\Document as DocumentModel;
use App\Livewire\Forms\Admin\DocumentForm;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Dokumen')]
class Document extends Component
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
    }

    public function edit(DocumentModel $document)
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
        $doc = DocumentModel::withTrashed()->findOrFail($id);
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
        DocumentModel::findOrFail($this->docIdBeingDeleted)->delete();
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
        DocumentModel::withTrashed()->findOrFail($this->docIdBeingRestored)->restore();
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

    public function download(string $id)
    {
        $document = DocumentModel::findOrFail($id);

        // Pastikan file benar-benar ada di storage
        if (!Storage::disk('public')->exists($document->file_path)) {
            session()->flash('error', 'File tidak ditemukan di server.');
            return;
        }

        // Mengambil nama file asli atau menggunakan judul dokumen sebagai nama file download
        $fileName = $document->title . '.pdf'; 

        return Storage::disk('public')->download($document->file_path, $fileName);
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
            ? DocumentModel::onlyTrashed() 
            : DocumentModel::query();

        return view('livewire.admin.document', [
            'documents' => $query->latest()->paginate(10)
        ]);
    }
}

