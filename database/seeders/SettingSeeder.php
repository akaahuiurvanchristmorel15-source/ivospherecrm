<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'company_name',
                'value' => 'GROUPE IVOSPHERE',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Nom de l\'entreprise',
            ],
            [
                'key' => 'company_tagline',
                'value' => 'ERP Administratif, Commercial et Multi-Domaines',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Slogan de l\'entreprise',
            ],
            [
                'key' => 'currency',
                'value' => 'FCFA',
                'group' => 'finance',
                'type' => 'string',
                'description' => 'Devise monétaire par défaut',
            ],
            [
                'key' => 'company_email',
                'value' => 'contact@ivosphere.com',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Adresse email officielle',
            ],
            [
                'key' => 'company_phone',
                'value' => '+225 27 22 00 00 00',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Téléphone du siège',
            ],
            [
                'key' => 'company_address',
                'value' => 'Abidjan, Côte d\'Ivoire • Cocody Angré 8e Tranche',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Adresse géographique',
            ],
            [
                'key' => 'company_postal_box',
                'value' => 'BP 456 Abidjan 01',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Boîte postale',
            ],
            [
                'key' => 'company_legal_form',
                'value' => 'SARL',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Forme juridique',
            ],
            [
                'key' => 'company_capital',
                'value' => '10 000 000 FCFA',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Capital social',
            ],
            [
                'key' => 'company_rccm',
                'value' => 'CI-ABJ-2026-B-0012',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Numéro RCCM (Registre du Commerce)',
            ],
            [
                'key' => 'company_cc',
                'value' => '2026-IVOSPHERE',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Compte Contribuable (NIF / CC)',
            ],
            [
                'key' => 'company_tax_regime',
                'value' => 'Régime Réel Normal (RRN)',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Régime fiscal',
            ],
            [
                'key' => 'company_tax_center',
                'value' => 'DGE - Direction des Grandes Entreprises',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Centre des impôts de rattachement',
            ],
            [
                'key' => 'company_cnps',
                'value' => '109283-A',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Numéro employeur CNPS',
            ],
            [
                'key' => 'company_bank_name',
                'value' => 'Société Générale Côte d\'Ivoire (SGCI)',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Banque principale',
            ],
            [
                'key' => 'company_bank_rib',
                'value' => 'CI034 01001 012345678901 45',
                'group' => 'general',
                'type' => 'string',
                'description' => 'RIB / IBAN officiel',
            ],
            [
                'key' => 'default_tax_rate',
                'value' => '18',
                'group' => 'finance',
                'type' => 'integer',
                'description' => 'Taux de TVA standard (%)',
            ],
            [
                'key' => 'fiscal_year',
                'value' => '2026',
                'group' => 'finance',
                'type' => 'string',
                'description' => 'Exercice fiscal en cours',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
