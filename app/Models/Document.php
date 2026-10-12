<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'domain_id',
        'user_id',
        'title',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'version',
        'tags',
        'is_vault',
        'status',
        'downloads_count',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'tags' => 'array',
            'is_vault' => 'boolean',
            'downloads_count' => 'integer',
        ];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class, 'folder_id');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Disque du fichier : les documents du coffre-fort sont privés (non accessibles par une URL publique).
     * Les anciens documents du coffre déposés avant ce changement restent sur le disque public.
     */
    public function storageDisk(): string
    {
        return $this->is_vault && $this->file_path && Storage::disk('local')->exists($this->file_path) ? 'local' : 'public';
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' Mo';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' Ko';
        }

        return $bytes.' o';
    }
}
