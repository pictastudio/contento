<?php

namespace PictaStudio\Contento\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use PictaStudio\Contento\Models\FaqCategory;

class FaqCategoryFactory extends Factory
{
    protected $model = FaqCategory::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'slug' => fn (array $attributes) => Str::slug((string) ($attributes['title'] ?? '')),
            'active' => $this->faker->boolean(),
        ];
    }
}
