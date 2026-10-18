<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Dépôt de documents à imprimer envoyé par un client du cyber depuis son téléphone.
 */
#[Fillable(['tenant_id', 'code', 'customer_name', 'customer_phone', 'color', 'duplex', 'copies', 'notes', 'status', 'handled_by'])]
class CyberPrintRequest extends Model
{
    use BelongsToTenant;

    /**
     * Statuts du dépôt : libellé et classes du badge.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    public const STATUSES = [
        'recu' => ['Reçu', 'bg-blue-50 text-[#0066FF] border-blue-200'],
        'imprime' => ['Imprimé', 'bg-amber-50 text-amber-700 border-amber-200'],
        'retire' => ['Remis au client', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'annule' => ['Annulé', 'bg-slate-100 text-slate-600 border-slate-200'],
    ];

    /**
     * Fichiers acceptés au dépôt.
     *
     * @var list<string>
     */
    public const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'color' => 'boolean',
            'duplex' => 'boolean',
            'copies' => 'integer',
        ];
    }

    public function files(): HasMany
    {
        return $this->hasMany(CyberPrintFile::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', ['recu', 'imprime']);
    }

    /**
     * Pages à imprimer pour un exemplaire (null si au moins un fichier n'a pas pu être compté).
     */
    public function pagesPerCopy(): ?int
    {
        return $this->files->contains(fn (CyberPrintFile $file) => $file->pages === null) ? null : (int) $this->files->sum('pages');
    }

    /**
     * Feuilles à imprimer au total (exemplaires compris).
     */
    public function totalPages(): ?int
    {
        $pages = $this->pagesPerCopy();

        return $pages === null ? null : $pages * max(1, $this->copies);
    }

    /**
     * Code de retrait court, unique parmi les dépôts du jour de l'espace (ex. « K7Q »).
     */
    public static function newCode(int $tenantId): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (self::withoutGlobalScopes()->where('tenant_id', $tenantId)->where('code', $code)->whereDate('created_at', today())->exists());

        return $code;
    }

    /**
     * Supprime les fichiers stockés avec le dépôt.
     */
    public function deleteStoredFiles(): void
    {
        foreach ($this->files as $file) {
            Storage::disk('local')->delete($file->path);
        }
    }
}
