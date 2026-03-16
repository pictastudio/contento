<?php

namespace PictaStudio\Contento\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PictaStudio\Contento\Models\{Menu, MenuItem};

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'title' => $this->faker->unique()->words(3, true),
            'link' => '/' . $this->faker->slug(),
            'active' => true,
            'visible_date_from' => null,
            'visible_date_to' => null,
        ];
    }
}
