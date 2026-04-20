<?php

use PictaStudio\Contento\Models\MailForm;
use PictaStudio\Translatable\Locales;

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

    $mailForm = MailForm::query()->firstOrFail();

    assertDatabaseHas('translations', [
        'translatable_type' => $mailForm->getMorphClass(),
        'translatable_id' => $mailForm->getKey(),
        'locale' => 'en',
        'attribute' => 'name',
        'value' => 'Contact Us',
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
        ->assertJsonPath(contentoResourcePath('name'), 'Support');

    assertDatabaseHas(config('contento.table_names.mail_forms'), [
        'id' => $mailForm->getKey(),
        'name' => 'Support',
        'email_to' => 'support@example.com',
        'slug' => 'support',
    ]);
});

it('can create a mail form with translated fields', function () {
    config()->set('translatable.locales', ['en', 'it']);
    app(Locales::class)->load();

    postJson(config('contento.routes.api.v1.prefix') . '/mail-forms', [
        'translations' => [
            'en' => [
                'name' => 'Contact Us',
                'custom_fields' => [
                    ['name' => 'company', 'type' => 'text'],
                ],
                'redirect_url' => 'https://example.com/thanks',
            ],
            'it' => [
                'name' => 'Contattaci',
                'custom_fields' => [
                    ['name' => 'azienda', 'type' => 'text'],
                ],
                'redirect_url' => 'https://example.com/grazie',
            ],
        ],
        'email_to' => 'test@example.com',
    ])
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('name'), 'Contact Us');

    $mailForm = MailForm::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.mail_forms'), [
        'id' => $mailForm->getKey(),
        'slug' => 'contact-us',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $mailForm->getMorphClass(),
        'translatable_id' => $mailForm->getKey(),
        'locale' => 'it',
        'attribute' => 'name',
        'value' => 'Contattaci',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $mailForm->getMorphClass(),
        'translatable_id' => $mailForm->getKey(),
        'locale' => 'it',
        'attribute' => 'custom_fields',
        'value' => json_encode([
            ['name' => 'azienda', 'type' => 'text'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/mail-forms/' . $mailForm->getKey(), ['Locale' => 'it'])
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('name'), 'Contattaci')
        ->assertJsonPath(contentoResourcePath('redirect_url'), 'https://example.com/grazie')
        ->assertJsonPath(contentoResourcePath('custom_fields.0.name'), 'azienda');
});
