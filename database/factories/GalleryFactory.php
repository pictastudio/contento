<?php

namespace PictaStudio\Contento\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use PictaStudio\Contento\Models\Gallery;

class GalleryFactory extends Factory
{
    protected $model = Gallery::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->words(2, true),
            'slug' => fn (array $attributes): string => Str::slug((string) ($attributes['title'] ?? '')),
            'code' => $this->faker->unique()->slug(2),
            'abstract' => $this->faker->sentence(),
            'active' => true,
        ];
    }
}
