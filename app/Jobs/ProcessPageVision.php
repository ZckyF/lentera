<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPageVision implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Document $document;
    public $imagePath;
    public $pageNumber;

    public function __construct(Document $document, $imagePath, $pageNumber)
    {
        $this->document = $document;
        $this->imagePath = $imagePath;
        $this->pageNumber = $pageNumber;
    }

    public function handle()
    {
        try {
            $fullPath = storage_path('app/public/' . $this->imagePath);
            $imageData = base64_encode(file_get_contents($fullPath));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openrouter.key'),
            ])->post(config('services.openrouter.base_url').'/v1/chat/completions', [
                'model' => 'google/gemini-2.0-flash-001',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text', 
                                'text' => "Task: Document Extraction to Structured Markdown.
                                            1. FORMATTING (CRITICAL)
                                            - MANDATORY: Start 'BAB [ROMAN]' with ## (e.g., ## BAB I).
                                            - MANDATORY: Start 'Pasal [NUMBER]' with ### (e.g., ### Pasal 1).
                                            - Even if 'Pasal' or 'BAB' appears as plain text in the image, you MUST add the hashtags.
                                            - Ensure a double newline before every ## and ###.
                                            2. EXCLUSIONS
                                            - DELETE: Page numbers, campus logos, and repetitive headers/footers.
                                            - DAFTAR ISI: If the page is a Table of Contents, transcribe as PLAIN TEXT only. DO NOT use ## or ### for TOC entries.
                                            3. CONTENT
                                            - BODY: Transcribe verses (1), (2) and lists (a, b) as PLAIN TEXT verbatim.
                                            - TABLES: Use Markdown Table format.
                                            - NO COMMENTARY: Output raw markdown only. No code blocks."
                            ],
                            ['type' => 'image_url', 'image_url' => ['url' => "data:image/jpeg;base64,{$imageData}"]]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $markdown = $response->json('choices.0.message.content');
                
                $this->document->pages()->updateOrCreate(
                    ['page_number' => $this->pageNumber],
                    ['content' => $markdown]
                );

                Storage::disk('public')->delete($this->imagePath);

                $this->checkIfAllPagesProcessed();
            }
        } catch (\Exception $e) {
            Log::error("Vision Error Page {$this->pageNumber}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function checkIfAllPagesProcessed()
    {
        if ($this->document->pages()->count() === $this->document->page_count) {
            
            $fullMarkdown = $this->document->pages()
                ->orderBy('page_number', 'asc')
                ->pluck('content')
                ->implode("\n\n");

            $this->document->update([
                'content_raw' => $fullMarkdown
            ]);

            dispatch(new ProcessDocumentChunking($this->document));
            
            $directory = dirname($this->imagePath);
            Storage::disk('public')->deleteDirectory($directory);
        }
    }
}