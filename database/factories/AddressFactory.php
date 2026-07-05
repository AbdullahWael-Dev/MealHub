<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected array $cities = [
        'القاهرة' => ['مدينة نصر', 'المعادي', 'مصر الجديدة', 'الزمالك', 'المهندسين'],
        'الجيزة'  => ['6 أكتوبر', 'الشيخ زايد', 'فيصل', 'الهرم'],
        'الإسكندرية' => ['سموحة', 'ميامي', 'سيدي بشر', 'المنتزه'],
    ];

    public function definition(): array
    {
        $city = $this->faker->randomElement(array_keys($this->cities));
        $area = $this->faker->randomElement($this->cities[$city]);

        return [
            'user_id'        => User::factory(),
            'title'          => $this->faker->randomElement(['Home', 'Work', 'Other']),
            'recipient_name' => $this->faker->name(),
            'phone'          => '01' . $this->faker->numerify('#########'),
            'city'           => $city,
            'area'           => $area,
            'street'         => $this->faker->streetName(),
            'building'       => (string) $this->faker->numberBetween(1, 60),
            'floor'          => (string) $this->faker->numberBetween(1, 12),
            'apartment'      => (string) $this->faker->numberBetween(1, 20),
            'landmark'       => $this->faker->randomElement([
                'جنب صيدلية سيف',
                'أمام مسجد النور',
                'بجوار فرع بنك مصر',
                'قريب من محطة المترو',
                null,
            ]),
            'notes'          => $this->faker->boolean(30)
                ? $this->faker->sentence()
                : null,
            'latitude'       => $this->faker->latitude(29.9, 31.3),
            'longitude'      => $this->faker->longitude(29.9, 31.5),
            'is_default'     => false,
        ];
    }
}
