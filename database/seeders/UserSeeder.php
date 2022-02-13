<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Address;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdminRole = Role::create(['name' => 'super-admin']);
        $clientRole     = Role::create(['name' => 'client']);

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@loanapp.com',
            'password' => Hash::make('loanApp123')
        ]);

        $superAdmin->assignRole($superAdminRole);

        $users=User::factory(5)->has(Address::factory()->count(1))->create();
        foreach ($users as $user) {
            $user->assignRole($clientRole);
        }
    }
}
