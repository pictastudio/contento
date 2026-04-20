<?php

use PictaStudio\Contento\Models\FaqCategory;

it('generates faq category slugs from the model title', function () {
    $category = FaqCategory::factory()->create(['title' => 'Test Category']);

    expect($category->slug)->toBe('test-category');
});
