<?php

use PictaStudio\Contento\Models\{Faq, FaqCategory};

it('generates faq slugs from the model title', function () {
    $category = FaqCategory::factory()->create();
    $faq = Faq::factory()->create(['title' => 'Test Faq', 'faq_category_id' => $category->getKey()]);

    expect($faq->slug)->toBe('test-faq');
});
