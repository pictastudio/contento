<?php

namespace PictaStudio\Contento\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PictaStudio\Contento\Models\{Faq, FaqCategory};

class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'faq_category_id' => FaqCategory::factory(),
            'title' => $this->faker->sentence(),
            'slug' => $this->faker->unique()->slug(),
            'active' => true,
            'content' => $this->faker->paragraph(),
        ];
    }
}
