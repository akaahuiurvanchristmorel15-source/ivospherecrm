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
     * Extensions acceptées dans la GED : documents bureautiques et images matricielles uniquement
     * (pas de PHP, HTML ni SVG, qui pourraient s'exécuter depuis le serveur ou le navigateur).
     *
     * @var list<string>
     */
    public const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'doc', 'docx', 'odt', 'rtf', 'txt', 'xls', 'xlsx', 'ods', 'csv', 'ppt', 'pptx', 'odp', 'zip'];

    /**
     * Rôles autorisés à consulter et alimenter le coffre-fort.
     *
     * @var list<string>
     */
    public const VAULT_ROLES = ['administrateur', 'responsable'];

    /**
     * Règles de validation d'un fichier déposé dans la GED (20 Mo maximum).
     *
     * @return list<string>
     */
    public static function uploadRules(): array
    {
        return ['file', 'max:20480', 'mimes:'.implode(',', self::ALLOWED_EXTENSIONS), 'extensions:'.implode(',', self::ALLOWED_EXTENSIONS)];
    }

    public static function canAccessVault(?User $user): bool
    {
        return $user !== null && $user->hasRole(self::VAULT_ROLES);
    }

    /**
     * Consultation : documents de la GED pour tout compte autorisé, coffre-fort réservé.
     */
    public function canBeViewedBy(?User $user): bool
    {
        return $user !== null && (! $this->is_vault || self::canAccessVault($user));
    }

    /**
     * Suppression : l'auteur du dépôt, ou un administrateur / responsable.
     */
    public function canBeDeletedBy(?User $user): bool
    {
        return $this->canBeViewedBy($user) && ((int) $this->user_id === (int) $user->id || $user->hasRole(self::VAULT_ROLES));
    }

    /**
     * Disque du fichier : les nouveaux dépôts sont privés (servis uniquement par la route d'ouverture).
     * Les anciens documents déposés sur le disque public y restent lisibles.
     */
    public function storageDisk(): string
    {
        return $this->file_path && Storage::disk('local')->exists($this->file_path) ? 'local' : 'public';
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
