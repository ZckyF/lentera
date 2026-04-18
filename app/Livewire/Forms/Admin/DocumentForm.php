<?php

namespace App\Livewire\Forms\Admin;

use App\Jobs\ProcessDocumentChunking;
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

        $contentRaw = null;
        $pageCount = 0;

        if ($this->file->getClientMimeType() === 'application/pdf' || $this->file->getClientOriginalExtension() === 'pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile(storage_path('app/public/' . $path));
            $pages = $pdf->getPages();
            $pageCount = count($pages);

            $fullText = '';
            $isStarted = false;

            foreach ($pages as $page) {
                $pageText = $page->getText();

                // 1. Titik Jangkar: Mulai ambil teks hanya jika menemukan kata MEMUTUSKAN/MENETAPKAN
                if (!$isStarted && preg_match('/(MEMUTUSKAN|MENETAPKAN)\s*:/i', $pageText)) {
                    $isStarted = true;
                    $parts = preg_split('/(MEMUTUSKAN|MENETAPKAN)\s*:/i', $pageText);
                    $pageText = $parts[1] ?? $pageText;
                }

                if ($isStarted) {
                    // 2. Pembersihan dasar: karakter ilegal & spasi berlebih
                    $pageText = str_replace(chr(0), '', $pageText);
                    $fullText .= "\n" . trim($pageText);
                }
            }

            // 3. Sanitasi: Gabungkan kalimat terputus, jaga struktur BAB/Pasal/List
            // Ditambahkan [-•] agar list jadwal yang kamu buat tadi tidak berantakan
            $contentRaw = preg_replace_callback('/\n(?!\s*(BAB|Pasal|\(\d+\)|[a-z]\.|[-•]))/i', function($matches) {
                return ' ';
            }, $fullText);
        }

        $document = Document::create([
            'title' => $this->title,
            'year' => $this->year,
            'file_path' => $path,
            'mime_type' => $this->file->getClientOriginalExtension(),
            'file_size' => $this->file->getSize(),
            'content_raw' => $contentRaw,
            'page_count' => $pageCount,
            'uploaded_by' => auth()->id(),
            'status' => 'processing'
        ]);

        ProcessDocumentChunking::dispatch($document);
        $this->reset();
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