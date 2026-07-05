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
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('مفيش يوزرز في الداتابيز. اعمل UserSeeder أو UserFactory الأول.');
            return;
        }
        $users->each(function ($user) {
            $count = fake()->numberBetween(1, 5);
            $addresses = Address::factory()->count($count)->create(['user_id' => $user->id]);
            $addresses->random()->update(['is_default' => true]);
        });
    }
}
