<?php

use PictaStudio\Contento\Models\MailForm;

use function Pest\Laravel\{assertDatabaseHas, getJson, postJson, putJson};

it('can list mail forms', function () {
    MailForm::factory()->count(2)->create();

    getJson(config('contento.routes.api.v1.prefix') . '/mail-forms')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can filter, sort and paginate mail forms', function () {
    $first = MailForm::factory()->create(['newsletter' => true]);
    MailForm::factory()->create(['newsletter' => false]);
    $third = MailForm::factory()->create(['newsletter' => true]);

    $query = http_build_query([
        'id' => [$first->getKey(), $third->getKey()],
        'newsletter' => 1,
        'sort_by' => 'id',
        'sort_dir' => 'desc',
        'per_page' => 1,
        'page' => 1,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/mail-forms?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

it('rejects unsupported mail form list query params', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/mail-forms?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can create a mail form', function () {
    postJson(config('contento.routes.api.v1.prefix') . '/mail-forms', [
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

    putJson(config('contento.routes.api.v1.prefix') . '/mail-forms/' . $mailForm->getKey(), [
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
