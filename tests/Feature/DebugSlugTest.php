<?php

use PictaStudio\Contento\Models\FaqCategory;

it('debugs faq category slug generation', function () {
    $category = FaqCategory::factory()->create(['title' => 'Test Category']);

    $traits = class_uses_recursive(FaqCategory::class);
    // dump($traits);

    expect($traits)->toContain('Spatie\Sluggable\HasSlug');
    expect($traits)->toContain('PictaStudio\Contento\Traits\HasAuthors');

    expect($category->slug)->toBe('test-category');
});
