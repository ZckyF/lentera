<?php

namespace App\Livewire\Forms\Admin;

use App\Jobs\ProcessDocumentChunking;
use App\Jobs\ProcessPdfParser;
use App\Models\Document;
use Illuminate\Support\Facades\Log;
use Livewire\Form;
use Livewire\Attributes\Validate;
use Smalot\PdfParser\Parser;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DocumentForm extends Form
{
    public ?Document $document;

    #[Validate('required|min:5')]
    public $title = '';

    #[Validate('required|integer|min:1900|max:2099')]
    public $year = '';

    public $file;

    #[Validate('required|in:active,inactive')]
    public $status = 'inactive';

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
            
            'file.file' => 'Format yang diunggah harus berupa berkas/file.',
            'file.mimes' => 'Hanya mendukung format berkas PDF.',
            'file.max' => 'Ukuran berkas maksimal adalah 10 MB.',

            'status.in' => 'Status tidak valid.',
            'status.required' => 'Status wajib diisi.',
        ];
    }

    public function store()
    {
        $this->validate(['file' => 'required|file|mimes:pdf|max:10240']);

        $path = $this->file->store('documents', 'public');
        $fullPath = storage_path('app/public/' . $path);

        $parser = new Parser();
        $pdf = $parser->parseFile($fullPath);
        $pageCount = count($pdf->getPages());

        $document = Document::create([
            'title' => $this->title,
            'year' => $this->year,
            'file_path' => $path,
            'mime_type' => 'pdf',
            'file_size' => $this->file->getSize(),
            'uploaded_by' => auth()->id(),
            'page_count' => $pageCount,
            'status' => 'processing'
        ]);

        ProcessPdfParser::dispatch($document);

        $this->reset();
        session()->flash('message', 'Dokumen sedang diproses.');
    }

    public function update()
    {
        $this->validate(['file' => 'required|file|mimes:pdf|max:10240']);
        
        $data = [
            'title' => $this->title,
            'year' => $this->year,
            'status' => $this->status
        ];

        $this->document->update($data);

        $this->reset();
    }
}