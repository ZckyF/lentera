<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pgvector\Laravel\Vector;

class DocumentChunk extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentChunkFactory> */
    use HasFactory;

    protected $fillable = [
        'document_id',
        'content',
        'page_number',
        'chunk_order',
        'embedding'
    ];

    protected $casts = [
        'embedding' => Vector::class
    ];
    
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
