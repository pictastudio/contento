<?php

namespace PictaStudio\Contento\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PictaStudio\Contento\Models\Modal;

class ModalFactory extends Factory
{
    protected $model = Modal::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'slug' => $this->faker->unique()->slug(),
            'content' => $this->faker->paragraph(),
            'cta_button_text' => $this->faker->words(2, true),
            'active' => true,
            'template' => 'default',
        ];
    }
}
