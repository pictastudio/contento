<?php

namespace PictaStudio\Contento\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PictaStudio\Contento\Models\Page;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'abstract' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['page', 'news']),
            'active' => $this->faker->boolean(),
            'content' => ['body' => $this->faker->paragraph()],
        ];
    }
}
