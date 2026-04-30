<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Services\OpenRouterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Vector;

class ProcessDocumentChunking implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document, public int $tries = 3){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $text = $this->document->content_raw;

        if (empty($text)) {
            Log::info("Document {$this->document->id} is empty.");
            $this->document->update(['status' => 'failed']);
            return;
        }

        $aiService = new OpenRouterService();

        /**
         * REGEX SAPU JAGAT
         * Memotong berdasarkan:
         * 1. ## BAB ...
         * 2. ### Pasal ...
         * 3. Kata kunci formal: MEMUTUSKAN, MENETAPKAN, MENIMBANG (untuk dokumen tak resmi/awal)
         * 4. Double Newline + Heading (Untuk dokumen umum/acak)
         */
        // Tambahkan 'Bagian' ke pattern jika dokumenmu pakai Bagian Kesatu, dsb.
        $pattern = '/(?=^##\s+BAB)|(?=^###\s+Pasal)|(?=^###\s+Bagian)|(?=^Ditetapkan\s+di)|(?=^Bagian\s+Ke)|(?=^MEMUTUSKAN)|(?=^Menetapkan)|(?=^Menimbang)(?=^Mengingat)/mi';
        
        // Pakai PREG_SPLIT_DELIM_CAPTURE agar header BAB/Pasal tidak hilang saat dipotong
        $chunksRaw = preg_split($pattern, $text, -1, PREG_SPLIT_NO_EMPTY);

        $chunksToSave = [];
        $order = 1;
        $currentBab = 'KETENTUAN UMUM'; // Default context

        try {
            $aiService = new OpenRouterService();

            foreach ($chunksRaw as $chunkContent) {
                $chunkContent = trim($chunkContent);
                if (strlen($chunkContent) < 20) continue;

                // 1. Update Context BAB
                if (str_starts_with($chunkContent, '## BAB')) {
                    $lines = explode("\n", $chunkContent);
                    $currentBab = trim($lines[0]); 
                    
                    // Jika cuma judul BAB tanpa isi, jangan simpan sebagai chunk mandiri
                    if (count($lines) <= 1) continue; 
                }

                if (str_contains($chunkContent, 'Ditetapkan di')) {
                    $currentBab = null; 
                }
                
                // 2. Siapkan Header Konteks
                $documentTitle = $this->document->title;
                $header = "[DOKUMEN: {$documentTitle} {$this->document->year}]\n";

                // // Tambahkan BAB jika belum ada di dalam teks
                if (!str_contains($chunkContent, $currentBab)) {
                    $header .= "{$currentBab}\n";
                }

                // GABUNGKAN: Header + Isi Asli
                $fullContent = $header . $chunkContent;

                // 3. Logika Simpan (Normal vs Fallback)
                if (!str_contains($chunkContent, 'Pasal') && strlen($fullContent) > 3000) {
                    $subChunks = explode("\n\n", $chunkContent); // Split isi aslinya saja
                    foreach ($subChunks as $sub) {
                        $sub = trim($sub);
                        if (strlen($sub) < 20) continue;

                        $finalText = $header . $sub;

                        $vector = $aiService->embed($finalText);
                        // Tiap potongan paragraf tetap ditempeli header yang sama
                        $chunksToSave[] = $this->formatChunk($header . $sub, $order++, $vector);
                    }
                } else {
                    $vector = $aiService->embed($fullContent);
                    $chunksToSave[] = $this->formatChunk($fullContent, $order++, $vector);
                }
            }

            DB::transaction(function () use ($chunksToSave) {
                $this->document->chunks()->delete();

                foreach ($chunksToSave as $chunk) {
                    DocumentChunk::create($chunk);
                }
            });

            $this->document->update(['status' => 'active']);

        } catch (\Exception $e) {
            Log::error("Gagal chunking Dokumen {$this->document->id}: " . $e->getMessage());
            $this->document->update(['status' => 'failed']);
        }
    }

    /**
     * Helper for formatting data before saving
     */
    private function formatChunk($content, $order, $vector = null)
    {
        return [
            'document_id' => $this->document->id,
            'content'     => $content,
            'chunk_order' => $order,
            'embedding'   => new Vector($vector),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
