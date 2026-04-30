<?php

namespace PictaStudio\Contento\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PictaStudio\Contento\Models\CatalogImage;

class CatalogImageFactory extends Factory
{
    protected $model = CatalogImage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'title' => $this->faker->sentence(4),
            'alt' => $this->faker->sentence(5),
            'caption' => $this->faker->sentence(),
            'disk' => 'public',
            'path' => 'catalog_images/' . $this->faker->uuid() . '.jpg',
            'mime_type' => 'image/jpeg',
            'size' => $this->faker->numberBetween(10_000, 500_000),
            'width' => $this->faker->numberBetween(320, 1920),
            'height' => $this->faker->numberBetween(240, 1080),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
