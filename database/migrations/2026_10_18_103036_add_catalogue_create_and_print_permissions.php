<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ajout de produits et impression / téléchargement du catalogue : permissions attribuables par rôle.
     * Elles sont accordées aux rôles qui consultent déjà le stock, pour ne retirer aucun accès existant.
     *
     * @var list<array{slug: string, name: string}>
     */
    private array $permissions = [
        ['slug' => 'products.create', 'name' => 'Ajouter des produits au catalogue'],
        ['slug' => 'stocks.catalog', 'name' => 'Imprimer et télécharger le catalogue'],
    ];

    public function up(): void
    {
        $stockViewPermissionId = DB::table('permissions')->where('slug', 'stocks.view')->value('id');
        $roleIds = DB::table('roles')->where('slug', 'administrateur')->pluck('id')
            ->merge($stockViewPermissionId ? DB::table('permission_role')->where('permission_id', $stockViewPermissionId)->pluck('role_id') : [])
            ->unique();

        foreach ($this->permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                ['name' => $permission['name'], 'group' => 'stock', 'updated_at' => now(), 'created_at' => now()],
            );

            $permissionId = DB::table('permissions')->where('slug', $permission['slug'])->value('id');

            foreach ($roleIds as $roleId) {
                DB::table('permission_role')->updateOrInsert(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')->whereIn('slug', array_column($this->permissions, 'slug'))->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
