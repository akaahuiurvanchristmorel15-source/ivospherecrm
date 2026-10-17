<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Modification, ajustement du stock et suppression des produits du catalogue : réservés à l'administrateur.
     *
     * @var list<array{slug: string, name: string}>
     */
    private array $permissions = [
        ['slug' => 'products.edit', 'name' => 'Modifier les produits du catalogue (fiche, prix, photos)'],
        ['slug' => 'products.adjust', 'name' => 'Ajuster le stock des produits du catalogue'],
        ['slug' => 'products.delete', 'name' => 'Supprimer des produits du catalogue'],
    ];

    public function up(): void
    {
        $administratorRoleIds = DB::table('roles')->where('slug', 'administrateur')->pluck('id');

        foreach ($this->permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                ['name' => $permission['name'], 'group' => 'stock', 'updated_at' => now(), 'created_at' => now()],
            );

            $permissionId = DB::table('permissions')->where('slug', $permission['slug'])->value('id');

            foreach ($administratorRoleIds as $roleId) {
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
