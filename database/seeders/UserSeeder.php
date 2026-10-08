<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allDomains = Domain::all();
        $domainIds = $allDomains->pluck('id')->toArray();

        $users = [
            [
                'name' => 'Directeur Général',
                'email' => 'admin@ivosphere.com',
                'password' => Hash::make('password'),
                'phone' => '+225 07 00 00 00 01',
                'is_active' => true,
                'all_domains' => true,
                'role' => 'administrateur',
            ],

        ];

        foreach ($users as $userData) {
            $roleSlug = $userData['role'];
            unset($userData['role']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $role = Role::where('slug', $roleSlug)->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }

            // Assign all domains or primary domains
            if ($userData['all_domains']) {
                $user->domains()->sync($domainIds);
            } else {
                $user->domains()->sync(array_slice($domainIds, 0, 2));
            }
        }
    }
}
