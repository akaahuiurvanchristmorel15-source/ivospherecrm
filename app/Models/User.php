<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\EmployeeSyncService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'avatar', 'is_active', 'all_domains'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'all_domains' => 'boolean',
        ];
    }

    /**
     * Roles assigned to this user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    /**
     * Domains assigned to this user.
     */
    public function domains()
    {
        return $this->belongsToMany(Domain::class, 'domain_user')->withTimestamps();
    }

    /**
     * Activity logs for this user.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Profil employé associé si le compte utilisateur est rattaché à un collaborateur.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * S'assure que le compte utilisateur dispose d'une fiche employé synchronisée.
     */
    public function ensureEmployeeProfile(array $extra = []): Employee
    {
        return app(EmployeeSyncService::class)->syncUserToEmployee($this, $extra);
    }

    /**
     * Indique si l'utilisateur possède une fiche employé.
     */
    public function getIsEmployeeAttribute(): bool
    {
        return $this->employee()->exists();
    }

    /**
     * Check if user is administrator.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('administrateur');
    }

    /**
     * Check if user has a given role by slug, array of slugs, or multiple arguments.
     */
    public function hasRole(string|array ...$roles): bool
    {
        if (empty($roles)) {
            return false;
        }

        $flattened = [];
        foreach ($roles as $r) {
            if (is_array($r)) {
                $flattened = array_merge($flattened, $r);
            } else {
                $flattened[] = $r;
            }
        }

        return $this->roles->whereIn('slug', $flattened)->isNotEmpty();
    }

    /**
     * Check if user has any of the given roles.
     *
     * @param  array<string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('slug', $roles)->isNotEmpty();
    }

    /**
     * Check if user has a permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->permissions->contains('slug', $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the given permissions.
     *
     * @param  array<string>  $permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can access a specific domain.
     */
    public function canAccessDomain(int|string|Domain $domain): bool
    {
        if ($this->isAdmin() || $this->all_domains) {
            return true;
        }

        if ($domain instanceof Domain) {
            return $this->domains->contains('id', $domain->id);
        }

        if (is_numeric($domain)) {
            return $this->domains->contains('id', (int) $domain);
        }

        return $this->domains->contains('code', strtoupper($domain));
    }

    /**
     * Get the primary role name for display.
     */
    public function getPrimaryRoleAttribute(): string
    {
        return $this->roles->first()?->name ?? 'Utilisateur';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
