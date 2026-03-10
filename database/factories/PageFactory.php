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
            'active' => true,
            'visible_date_from' => null,
            'visible_date_to' => null,
            'published_at' => now()->subMinute(),
            'content' => ['body' => $this->faker->paragraph()],
        ];
    }
}
