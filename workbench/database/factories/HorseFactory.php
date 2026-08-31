<?php

declare(strict_types=1);

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\Horse;

class HorseFactory extends Factory
{
    protected $model = Horse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->firstName(),
            'breed' => fake()->randomElement(['quarter', 'mustang', 'appaloosa']),
            // Deliberately null, not fake()->sentence(). The notes column is
            // searchable, so a random sentence made every exact-match search
            // assertion in the suite a coin flip -- GlobalSearchTest asserting
            // q=Com matches only 'Comanche' went red whenever faker produced a
            // sentence containing "Com". Tests that need notes set them.
            'notes' => null,
            'is_saddled' => fake()->boolean(),
        ];
    }
}
