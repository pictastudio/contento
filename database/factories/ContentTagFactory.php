<?php

namespace PictaStudio\Contento\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use PictaStudio\Contento\Models\ContentTag;

class ContentTagFactory extends Factory
{
    protected $model = ContentTag::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'slug' => fn (array $attributes): string => Str::slug((string) ($attributes['name'] ?? '')),
            'abstract' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'metadata' => ['icon' => $this->faker->word()],
            'active' => true,
            'show_in_menu' => $this->faker->boolean(),
            'in_evidence' => $this->faker->boolean(),
            'sort_order' => $this->faker->numberBetween(1, 50),
            'visible_from' => null,
            'visible_until' => null,
        ];
    }
}
