<?php

use PictaStudio\Contento\Models\Page;

it('stores and retrieves translations per locale', function () {
    app()->setLocale('en');

    $page = Page::query()->create([
        'title' => 'Hello',
        'abstract' => 'Short summary',
        'content' => ['body' => 'Body'],
    ]);

    $page->translateOrNew('it')->title = 'Ciao';
    $page->translateOrNew('it')->abstract = 'Sommario breve';
    $page->save();

    $page->refresh();

    app()->setLocale('en');
    expect($page->title)->toBe('Hello');
    expect($page->{'title:it'})->toBe('Ciao');
});

it('falls back to the configured locale when enabled', function () {
    config()->set('translatable.use_fallback', true);

    app()->setLocale('en');
    $page = Page::query()->create([
        'title' => 'Default title',
        'abstract' => 'Default abstract',
        'content' => ['body' => 'Body'],
    ]);

    $page->refresh();

    app()->setLocale('it');
    expect($page->title)->toBe('Default title');
    expect($page->abstract)->toBe('Default abstract');
});
