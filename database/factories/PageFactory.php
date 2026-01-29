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
            'slug' => $this->faker->unique()->slug(),
            'type' => 'page',
            'active' => true,
            'content' => ['body' => $this->faker->paragraph()],
        ];
    }
}
