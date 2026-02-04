<?php

use PictaStudio\Contento\Models\{Faq, FaqCategory};

it('debugs faq slug generation', function () {
    $category = FaqCategory::factory()->create();
    $faq = Faq::factory()->create(['title' => 'Test Faq', 'faq_category_id' => $category->id]);
    
    expect($faq->slug)->toBe('test-faq');
});
