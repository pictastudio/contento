<?php

use PictaStudio\Contento\Models\MailForm;

use function Pest\Laravel\{assertDatabaseHas, getJson, postJson, putJson};

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

it('can update a mail form', function () {
    $mailForm = MailForm::factory()->create([
        'name' => 'Contact Us',
        'email_to' => 'old@example.com',
    ]);

    putJson(config('contento.prefix') . '/mail-forms/' . $mailForm->getKey(), [
        'name' => 'Support',
        'email_to' => 'support@example.com',
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Support');

    assertDatabaseHas(config('contento.table_names.mail_forms'), [
        'id' => $mailForm->getKey(),
        'name' => 'Support',
        'email_to' => 'support@example.com',
        'slug' => 'support',
    ]);
});
