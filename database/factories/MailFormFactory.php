<?php

namespace PictaStudio\Contento\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PictaStudio\Contento\Models\MailForm;

class MailFormFactory extends Factory
{
    protected $model = MailForm::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->unique()->slug(),
            'email_to' => $this->faker->email(),
        ];
    }
}
