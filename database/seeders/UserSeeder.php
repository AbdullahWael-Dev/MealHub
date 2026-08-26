<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->call(RoleSeeder::class);
        }

        if (User::count() > 0) {
            return;
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@mealhub.test'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'phone' => '01000000000',
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        $customers = [
            ['name' => 'Ahmed Ali', 'email' => 'ahmed@mealhub.test', 'phone' => '01011111111'],
            ['name' => 'Sara Hassan', 'email' => 'sara@mealhub.test', 'phone' => '01022222222'],
            ['name' => 'Mohamed Khaled', 'email' => 'mohamed@mealhub.test', 'phone' => '01033333333'],
            ['name' => 'Nour Yasser', 'email' => 'nour@mealhub.test', 'phone' => '01044444444'],
            ['name' => 'Omar Samir', 'email' => 'omar@mealhub.test', 'phone' => '01055555555'],
            ['name' => 'Lina Mostafa', 'email' => 'lina@mealhub.test', 'phone' => '01066666666'],
            ['name' => 'Youssef Adel', 'email' => 'youssef@mealhub.test', 'phone' => '01077777777'],
            ['name' => 'Mariam Tarek', 'email' => 'mariam@mealhub.test', 'phone' => '01088888888'],
            ['name' => 'Karim Wael', 'email' => 'karim@mealhub.test', 'phone' => '01099999999'],
            ['name' => 'Hana Emad', 'email' => 'hana@mealhub.test', 'phone' => '01100000000'],
        ];

        foreach ($customers as $customer) {
            $user = User::firstOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'password' => bcrypt('password'),
                    'phone' => $customer['phone'],
                    'is_active' => true,
                ]
            );
            $user->assignRole('customer');
        }

        User::factory()->count(15)->create()->each(function ($user) {
            $user->assignRole('customer');
        });
    }
}
