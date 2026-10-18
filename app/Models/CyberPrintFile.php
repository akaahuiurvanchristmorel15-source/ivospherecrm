<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fichier d'un dépôt d'impression, avec son nombre de pages compté à la réception.
 */
#[Fillable(['cyber_print_request_id', 'original_name', 'path', 'extension', 'size', 'pages', 'pages_estimated', 'count_method'])]
class CyberPrintFile extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'pages' => 'integer',
            'pages_estimated' => 'boolean',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(CyberPrintRequest::class, 'cyber_print_request_id');
    }
}
