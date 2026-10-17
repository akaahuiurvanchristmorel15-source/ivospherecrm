<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Lien d'inscription à usage unique : seul le hachage du jeton est enregistré.
 */
#[Fillable(['tenant_id', 'token_hash', 'name', 'email', 'role_ids', 'domain_ids', 'all_domains', 'created_by', 'user_id', 'expires_at', 'used_at'])]
class UserInvitation extends Model
{
    use BelongsToTenant;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role_ids' => 'array',
            'domain_ids' => 'array',
            'all_domains' => 'boolean',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Crée l'invitation et renvoie le jeton en clair (affiché une seule fois à l'administrateur).
     *
     * @param  array<string, mixed>  $attributes
     * @return array{0: self, 1: string}
     */
    public static function generate(array $attributes): array
    {
        $token = Str::random(48);
        $invitation = self::create($attributes + ['token_hash' => self::hashToken($token)]);

        return [$invitation, $token];
    }

    /**
     * Invitation encore utilisable correspondant au jeton, tous espaces confondus (visiteur non connecté).
     */
    public static function findUsable(string $token): ?self
    {
        return self::withoutGlobalScopes()
            ->where('token_hash', self::hashToken($token))
            ->usable()
            ->first();
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeUsable(Builder $query): void
    {
        $query->whereNull('used_at')->where('expires_at', '>', now());
    }

    /**
     * Réserve l'invitation de façon atomique ; renvoie false si elle a déjà été utilisée entre-temps.
     */
    public function claim(): bool
    {
        $claimed = self::withoutGlobalScopes()
            ->whereKey($this->id)
            ->whereNull('used_at')
            ->update(['used_at' => now(), 'updated_at' => now()]) === 1;

        if ($claimed) {
            $this->refresh();
        }

        return $claimed;
    }

    /**
     * @return Collection<int, Role>
     */
    public function roles(): Collection
    {
        return Role::whereIn('id', $this->role_ids ?? [])->get();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
