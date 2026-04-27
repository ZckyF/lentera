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
use Spatie\PdfToImage\Pdf;

class ProcessPdfParser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function handle()
    {
        try {
            $this->document->update(['status' => 'processing']);

            $filePath = storage_path('app/public/' . $this->document->file_path);
            $tempPath = "temp/doc_{$this->document->id}_" . now()->timestamp;
            $tempFolder = storage_path("app/public/{$tempPath}");

            if (!file_exists($tempFolder)) {
                mkdir($tempFolder, 0777, true);
            }

            $gs = '"E:\program\gs\gs10.07.0\bin\gswin64c.exe"';

            // STEP 1: Dapatkan total halaman PDF secara cepat
            // Menggunakan command ringan GS untuk menghitung jumlah halaman
            $cleanPath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);

            // Kita suruh GS render ke "null" device hanya untuk dapet info halamannya
            $cmdCount = $gs . " -q -dNODISPLAY -dNOSAFER -c \"(" . str_replace('\\', '/', $cleanPath) . ") (r) file runpdfbegin pdfpagecount = quit\"";

            $outputCount = shell_exec($cmdCount);
            $totalPage = (int) trim($outputCount);

            Log::info("Hasil deteksi PDF ID: {$this->document->id}", [
                'path' => $cleanPath,
                'raw_output' => $outputCount,
                'detected_pages' => $totalPage
            ]);

            if ($totalPage <= 0) {
                // Jika cara di atas masih gagal karena masalah path, kita pakai cara "Brute Force"
                // Mencari string /Type /Page di dalam file mentah (cepat dan efektif untuk PDF standar)
                $fp = @fopen($cleanPath, 'rb');
                if ($fp) {
                    $count = 0;
                    while (!feof($fp)) {
                        $line = fread($fp, 4096);
                        $count += preg_match_all('/\/Type\s*\/Page\b/', $line, $dummy);
                    }
                    fclose($fp);
                    $totalPage = $count;
                }
            }

            if ($totalPage <= 0) {
                throw new \Exception("Gagal menghitung halaman PDF. Ghostscript dan Brute Force gagal.");
            }

            Log::info("Memulai konversi $totalPage halaman untuk Dokumen ID: {$this->document->id}");

        for ($i = 1; $i <= $totalPage; $i++) {
            $outputFile = $tempFolder . DIRECTORY_SEPARATOR . "page-{$i}.jpg";
            
            // Command GS difokuskan hanya untuk FirstPage dan LastPage yang sama
            $cmd = $gs .
                " -dBATCH -dNOPAUSE -dSAFER" .
                " -sDEVICE=jpeg" .
                " -r150" .
                " -dFirstPage=$i -dLastPage=$i" . // Kunci efisiensi ada di sini
                " -sOutputFile=\"" . $outputFile . "\"" .
                " \"" . $filePath . "\"";

            exec($cmd, $output, $returnVar);

            if ($returnVar === 0 && file_exists($outputFile)) {
                $relativeImagePath = $tempPath . "/page-{$i}.jpg";
                $relativeImagePath = str_replace('\\', '/', $relativeImagePath);

                // LANGSUNG DISPATCH: Tidak perlu nunggu loop selesai
                ProcessPageVision::dispatch(
                    $this->document,
                    $relativeImagePath,
                    $i
                );

                Log::info("Halaman {$i} sukses dirender & dikirim ke Vision Queue.");
            } else {
                Log::error("Gagal merender halaman {$i} pada Dokumen ID: {$this->document->id}");
            }
        }

        } catch (\Exception $e) {
            Log::error("PDF Splitter Error: " . $e->getMessage());
            $this->document->update(['status' => 'failed']);
        }
    }
}
