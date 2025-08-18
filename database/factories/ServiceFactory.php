<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->words(3, true);
       return [
            'title' => ucfirst($title),
            'slug' => Str::slug($title),
            'short_desc' => $this->faker->sentence(8), // Short tagline
            'content' => $this->faker->paragraphs(3, true), // Detailed description
            // 'image' => 'services/' . $this->faker->image('public/storage/services', 640, 480, null, false), // Generates & stores image
            'status' => $this->faker->randomElement([0, 1]),
        ];
    }
}
