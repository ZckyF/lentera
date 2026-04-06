<?php

namespace App\Livewire\Forms\Admin;

use App\Models\Document;
use Livewire\Form;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class DocumentForm extends Form
{
    public ?Document $document;

    #[Validate('required|min:5')]
    public $title = '';

    #[Validate('required|integer|min:1900|max:2099')]
    public $year = '';

    #[Validate('nullable|file|mimes:pdf|max:10240')]
    public $file;

    #[Validate('required|in:active,inactive')]
    public $status = 'active';

    public function setDocument(Document $document)
    {
        $this->document = $document;
        $this->title = $document->title;
        $this->year = $document->year;
        $this->status = $document->status;
    }

    public function messages() 
    {
        return [
            'title.required' => 'Judul dokumen wajib diisi.',
            'title.min' => 'Judul dokumen minimal harus 5 karakter.',
            
            'year.required' => 'Tahun dokumen wajib diisi.',
            'year.integer' => 'Tahun harus berupa angka.',
            'year.min' => 'Tahun minimal adalah 1900.',
            'year.max' => 'Tahun tidak boleh lebih dari 2099.',
            
            'file.required' => 'Berkas dokumen wajib diunggah.',
            'file.file' => 'Format yang diunggah harus berupa berkas/file.',
            'file.mimes' => 'Hanya mendukung format berkas PDF.',
            'file.max' => 'Ukuran berkas maksimal adalah 10 MB.',

            'status.in' => 'Status tidak valid.',
            'status.required' => 'Status wajib diisi.',
        ];
    }

    public function store()
    {
        $this->validate();

        $path = $this->file->store('documents', 'public');

        $contentRaw = null;
        $pageCount = 0;

        if ($this->file->getClientMimeType() === 'application/pdf') {
             $parser = new Parser();
            $pdf = $parser->parseFile(storage_path('app/public/' . $path));
                
            $contentRaw = $pdf->getText();
            $pageCount = count($pdf->getPages());
        }

        Document::create([
            'title' => $this->title,
            'year' => $this->year,
            'file_path' => $path,
            'mime_type' => $this->file->getClientMimeType(),
            'file_size' => $this->file->getSize(),
            'content_raw' => $contentRaw,
            'page_count' => $pageCount,
            'uploaded_by' => auth()->id(),
            'status' => $this->status
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();
        
        $data = [
            'title' => $this->title,
            'year' => $this->year,
            'status' => $this->status
        ];

        if ($this->file) {
            Storage::disk('public')->delete($this->document->file_path);
            $data['file_path'] = $this->file->store('documents', 'public');
            $data['mime_type'] = $this->file->getClientMimeType();
            $data['file_size'] = $this->file->getSize();

            if ($this->file->getClientMimeType() === 'application/pdf') {
                $parser = new Parser();
                $pdf = $parser->parseFile(storage_path('app/public/' . $data['file_path']));
                
                $data['content_raw'] = $pdf->getText();
                $data['page_count'] = count($pdf->getPages());
           }
        }

        $this->document->update($data);
        $this->reset();
    }
}