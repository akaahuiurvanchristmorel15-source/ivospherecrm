<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remplace l'ancienne adresse de contact par défaut par l'adresse officielle d'IVOSPHERE SARL.
     */
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'company_email')
            ->where('value', 'contact@ivosphere.com')
            ->update(['value' => 'ivospheresarl@gmail.com']);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'company_email')
            ->where('value', 'ivospheresarl@gmail.com')
            ->update(['value' => 'contact@ivosphere.com']);
    }
};
