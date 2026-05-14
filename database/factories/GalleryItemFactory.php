<?php

namespace PictaStudio\Contento\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PictaStudio\Contento\Models\{Gallery, GalleryItem};

class GalleryItemFactory extends Factory
{
    protected $model = GalleryItem::class;

    public function definition(): array
    {
        return [
            'gallery_id' => Gallery::factory(),
            'title' => $this->faker->words(3, true),
            'subtitle' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'sort_order' => $this->faker->numberBetween(0, 50),
            'active' => true,
            'visible_from' => null,
            'visible_until' => null,
            'links' => [
                [
                    'label' => 'Website',
                    'url' => $this->faker->url(),
                ],
            ],
            'img' => [
                'id' => (string) $this->faker->uuid(),
                'name' => 'Factory image',
                'alt' => 'Factory image alt',
                'mimetype' => 'image/jpeg',
                'src' => 'gallery_items/factory.jpg',
                'metadata' => ['source' => 'factory'],
            ],
        ];
    }
}
