<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Saisie des observations sur les collaborateurs : permission attribuable par rôle,
     * accordée par défaut à l'administrateur et à tous les rôles de responsable.
     *
     * @var list<string>
     */
    private array $roleSlugs = [
        'administrateur',
        'responsable',
        'responsable_rh',
        'responsable_commercial',
        'responsable_financiere',
        'responsable_communication',
    ];

    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['slug' => 'observations.manage'],
            ['name' => 'Saisir des observations sur les collaborateurs', 'group' => 'rh', 'updated_at' => now(), 'created_at' => now()],
        );

        $permissionId = DB::table('permissions')->where('slug', 'observations.manage')->value('id');

        foreach (DB::table('roles')->whereIn('slug', $this->roleSlugs)->pluck('id') as $roleId) {
            DB::table('permission_role')->updateOrInsert(['permission_id' => $permissionId, 'role_id' => $roleId]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('slug', 'observations.manage')->value('id');

        DB::table('permission_role')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('id', $permissionId)->delete();
    }
};
