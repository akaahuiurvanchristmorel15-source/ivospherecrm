<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Droits de l'assistant IA et rôles qui les reçoivent par défaut (l'administrateur a tout).
     *
     * @var array<string, array{name: string, roles: list<string>|null}>
     */
    private array $permissions = [
        'ai.assistant' => ['name' => "Utiliser l'assistant IA (accueil, Ivo, recherche dans le menu)", 'roles' => null],
        'ai.sales' => ['name' => "Interroger l'IA sur les ventes, clients et factures", 'roles' => ['responsable', 'responsable_commercial', 'responsable_financiere', 'agent_commercial_terrain', 'agent_caissier_vendeur']],
        'ai.stock' => ['name' => "Interroger l'IA sur les stocks", 'roles' => ['responsable', 'responsable_commercial', 'agent_commercial_terrain', 'agent_caissier_vendeur', 'agent_technique']],
        'ai.finance' => ['name' => "Interroger l'IA sur la trésorerie, les dépenses et le bénéfice", 'roles' => ['responsable', 'responsable_financiere']],
        'ai.projects' => ['name' => "Interroger l'IA sur les projets", 'roles' => ['responsable', 'agent_technique', 'responsable_communication']],
        'ai.studio' => ['name' => "Générer des contenus avec l'IA Studio", 'roles' => ['responsable', 'responsable_commercial', 'responsable_communication']],
    ];

    public function up(): void
    {
        foreach ($this->permissions as $slug => $permission) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $slug],
                ['name' => $permission['name'], 'group' => 'ai', 'updated_at' => now(), 'created_at' => now()],
            );

            $permissionId = DB::table('permissions')->where('slug', $slug)->value('id');
            $roleIds = $permission['roles'] === null
                ? DB::table('roles')->pluck('id')
                : DB::table('roles')->whereIn('slug', [...$permission['roles'], 'administrateur'])->pluck('id');

            foreach ($roleIds as $roleId) {
                DB::table('permission_role')->updateOrInsert(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')->whereIn('slug', array_keys($this->permissions))->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
