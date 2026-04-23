<?php

namespace PictaStudio\Contento\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PictaStudio\Contento\Models\Metadata;

class MetadataFactory extends Factory
{
    protected $model = Metadata::class;

    public function definition(): array
    {
        $slug = $this->faker->unique()->slug();

        return [
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'uri' => '/' . $this->faker->unique()->slug(),
            'metadata' => [
                'title' => $this->faker->sentence(),
                'description' => $this->faker->sentence(),
            ],
        ];
    }
}
