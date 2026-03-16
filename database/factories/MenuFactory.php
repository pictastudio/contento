<?php

namespace PictaStudio\Contento\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PictaStudio\Contento\Models\Menu;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->words(2, true),
            'active' => true,
            'visible_date_from' => null,
            'visible_date_to' => null,
        ];
    }
}
