<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agent qui a réalisé la prestation (distinct du caissier qui encaisse) et droit de consulter
     * la performance des agents cyber, accordé par défaut aux responsables.
     *
     * @var list<string>
     */
    private array $performanceRoles = ['administrateur', 'responsable', 'responsable_commercial'];

    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('performed_by_employee_id')->nullable()->after('user_id')->constrained('employees')->nullOnDelete();
        });

        DB::table('permissions')->updateOrInsert(
            ['slug' => 'cyber.performance'],
            ['name' => 'Consulter la performance des agents cyber', 'group' => 'commercial', 'updated_at' => now(), 'created_at' => now()],
        );

        $permissionId = DB::table('permissions')->where('slug', 'cyber.performance')->value('id');

        foreach (DB::table('roles')->whereIn('slug', $this->performanceRoles)->pluck('id') as $roleId) {
            DB::table('permission_role')->updateOrInsert(['permission_id' => $permissionId, 'role_id' => $roleId]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('slug', 'cyber.performance')->value('id');
        DB::table('permission_role')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('id', $permissionId)->delete();

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('performed_by_employee_id');
        });
    }
};
