<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();

        if ($users->isEmpty()) {
            $this->call(UserSeeder::class);
            $users = User::query()->get();
        }

        foreach ($users as $user) {
            $existingAddresses = $user->addresses()->count();

            if ($existingAddresses === 0) {
                $count = fake()->numberBetween(1, 3);
                $addresses = Address::factory()->count($count)->create(['user_id' => $user->id]);
                $addresses->first()->update(['is_default' => true]);
                continue;
            }

            $user->addresses()->update(['is_default' => false]);
            $user->addresses()->inRandomOrder()->first()->update(['is_default' => true]);
        }
    }
}
