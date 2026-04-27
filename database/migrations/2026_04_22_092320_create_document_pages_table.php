<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')
                      ->constrained()
                      ->onDelete('cascade');

            $table->integer('page_number');
            
            // Konten hasil ekstraksi AI (Markdown)
            $table->longText('content')->nullable();

            // Indexing agar pencarian berdasarkan dokumen dan halaman super cepat
            $table->index(['document_id', 'page_number']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_pages');
    }
};
