<?php

use PictaStudio\Contento\Models\Page;
use PictaStudio\Translatable\Locales;

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

it('can fill multiple translations on create', function () {
    app()->setLocale('en');

    $page = Page::query()->create([
        'author' => 'Test page',
        'en' => ['title' => 'My first post', 'abstract' => 'English abstract'],
        'it' => ['title' => 'Il mio primo post', 'abstract' => 'Sommario breve'],
    ]);

    $page->refresh();

    expect($page->translate('en')->title)->toBe('My first post');
    expect($page->translate('it')->title)->toBe('Il mio primo post');
    expect($page->translate('it')->abstract)->toBe('Sommario breve');
});

it('discards translations when the locale is not configured', function () {
    $page = Page::query()->create([
        'author' => 'Test page',
        'en' => ['title' => 'My first post', 'abstract' => 'English abstract'],
        'it' => ['title' => 'Il mio primo post', 'abstract' => 'Sommario breve'],
        'de' => ['title' => 'Mein erster Beitrag', 'abstract' => 'Kurzer Übersicht'],
    ]);

    $page->refresh();

    expect($page->translate('en')->title)->toBe('My first post');
    expect($page->translate('it')->title)->toBe('Il mio primo post');
    expect($page->translate('de'))->toBeNull();

    app(Locales::class)->add('de');

    $page = Page::query()->create([
        'author' => 'Test page',
        'en' => ['title' => 'My first post', 'abstract' => 'English abstract'],
        'it' => ['title' => 'Il mio primo post', 'abstract' => 'Sommario breve'],
        'de' => ['title' => 'Mein erster Beitrag', 'abstract' => 'Kurzer Übersicht'],
    ]);

    $page->refresh();

    expect($page->translate('en')->title)->toBe('My first post');
    expect($page->translate('it')->title)->toBe('Il mio primo post');
    expect($page->translate('de')->title)->toBe('Mein erster Beitrag');
    expect($page->translate('de')->abstract)->toBe('Kurzer Übersicht');
});
