<?php

use PictaStudio\Contento\Models\MailForm;

use function Pest\Laravel\{assertDatabaseHas, getJson, postJson};

it('can list mail forms', function () {
    MailForm::factory()->count(2)->create();

    getJson(config('contento.prefix') . '/mail-forms')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can create a mail form', function () {
    postJson(config('contento.prefix') . '/mail-forms', [
        'name' => 'Contact Us',
        'slug' => 'contact-us',
        'email_to' => 'test@example.com',
    ])
        ->assertCreated();

    assertDatabaseHas(config('contento.table_names.mail_forms'), [
        'name' => 'Contact Us',
    ]);
});
