<?php

namespace Database\Factories;

use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => rand(1,5),
            'status' => VendorStatusEnum::Approved,
            'store_name' => fake()->word() . ' Store',
            'store_address' => fake()->address(),
        ];
    }
}
