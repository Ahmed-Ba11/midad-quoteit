<?php

namespace Database\Factories;

use App\Models\Diwan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Diwan>
 */
class DiwanFactory extends Factory
{
    /**
     * Define the model's default state: a public diwan of a poet long out of copyright.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'poet_name' => fake()->name(),
            'poet_death_year' => fake()->numberBetween(600, 1900),
            'title' => fake()->words(2, true),
            'about' => fake()->sentence(),
            'source' => fake()->sentence(3),
            'color' => fake()->randomElement(array_keys(config('quotes.palettes'))),
            'is_public' => true,
        ];
    }

    /**
     * A living poet: only ever a private notebook.
     */
    public function private(): static
    {
        return $this->state(fn () => ['poet_death_year' => null, 'is_public' => false]);
    }
}
