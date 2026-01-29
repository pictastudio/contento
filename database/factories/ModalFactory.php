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
            'active' => true,
            'template' => 'default',
        ];
    }
}
