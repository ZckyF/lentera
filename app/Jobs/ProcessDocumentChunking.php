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
        $this->document->update(['status' => 'processing']);
        $text = $this->document->content_raw;

        if (empty($text)) {
            $this->document->update(['status' => 'active']);
            return;
        }

        // Cleaning spasi dan baris kosong berlebih
        $text = preg_replace('/(?<!\n)(BAB\s+[IVXLC]+|Pasal\s+\d+)/i', "\n$1", $text);
        $lines = explode("\n", $text);

        $currentBab = 'KETENTUAN UMUM';
        $currentChunk = '';
        $chunksRaw = [];

        // STEP 1: Parsing Struktur (Bab -> Pasal)
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Deteksi BAB & Ambil Judulnya
            if (preg_match('/^BAB\s+([IVXLC]+)(.*)/i', $line, $matches)) {
                // Amankan chunk sebelumnya jika ada isi yang tertinggal
                if (!empty($currentChunk)) {
                    $chunksRaw[] = trim($currentChunk);
                    $currentChunk = '';
                }

                $nomorRomawi = trim($matches[1]);
                $sisaTeks = trim($matches[2]);

                // Jika judul BAB ada di baris berikutnya
                if (empty($sisaTeks) && isset($lines[$index + 1])) {
                    $next = trim($lines[$index + 1]);
                    if (!preg_match('/^Pasal/i', $next)) {
                        $sisaTeks = $next;
                    }
                }

                $currentBab = "BAB {$nomorRomawi}" . ($sisaTeks ? ": {$sisaTeks}" : "");
                continue;
            }

            if (preg_match('/^Pasal\s+\d+/i', $line)) {
                if (!empty($currentChunk)) {
                    $chunksRaw[] = trim($currentChunk);
                }
                
                // Injeksi Konteks: Sekarang $currentBab sudah terupdate sebelum Pasal diproses
                $currentChunk = "[Konteks: {$currentBab}]\n" . $line;
            } else {
                // Gabungkan isi ayat ke dalam chunk pasal yang aktif
                if (!empty($currentChunk)) {
                    $currentChunk .= "\n" . $line;
                }
            }
        }
        
        if (!empty($currentChunk)) $chunksRaw[] = $currentChunk;

        // STEP 2: Embedding & Mass Insert
        $aiService = new OpenRouterService();
        $chunksToSave = [];
        $order = 1;

        try {
            foreach ($chunksRaw as $chunkContent) {
                if (strlen(trim($chunkContent)) < 50) continue;

                $vector = null;
                for ($retry = 0; $retry < 3; $retry++) {
                    $vector = $aiService->embed($chunkContent);
                    if ($vector) break;
                    sleep(pow(2, $retry));
                }

                if (!$vector) throw new \Exception("Embedding gagal di chunk {$order}");

                $chunksToSave[] = [
                    'document_id' => $this->document->id,
                    'content'     => $chunkContent,
                    'chunk_order' => $order++,
                    'embedding'   => new \Pgvector\Laravel\Vector($vector),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            DB::transaction(function () use ($chunksToSave) {
                $this->document->chunks()->delete();
                // Mass insert untuk performa maksimal
                DocumentChunk::insert($chunksToSave);
            });

            $this->document->update(['status' => 'active']);
        } catch (\Exception $e) {
            Log::error("Gagal proses dokumen {$this->document->id}: " . $e->getMessage());
            $this->document->update(['status' => 'failed']);
        }
    }
}
