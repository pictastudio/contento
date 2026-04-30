<?php

use PictaStudio\Contento\Models\{CatalogImage, ContentTag, Metadata, Page};

use function Pest\Laravel\patchJson;

it('normalizes metadata empty strings across content resources', function () {
    foreach (metadataUpdateCases() as [$model, $uri]) {
        $resource = $model::factory()->create();

        patchJson($uri($resource), [
            'metadata' => [
                'titolo' => 'SEO title',
                'autore' => '',
                'descrizione' => 'SEO description',
                'twitter_descrizione' => '',
            ],
        ])->assertOk()
            ->assertJsonPath(contentoResourcePath('metadata.titolo'), 'SEO title')
            ->assertJsonPath(contentoResourcePath('metadata.autore'), null)
            ->assertJsonPath(contentoResourcePath('metadata.twitter_descrizione'), null);

        expect($resource->refresh()->metadata)->toMatchArray([
            'titolo' => 'SEO title',
            'autore' => null,
            'descrizione' => 'SEO description',
            'twitter_descrizione' => null,
        ]);
    }
});

it('validates seo metadata fields across structured metadata resources', function () {
    foreach (seoMetadataUpdateCases() as [$model, $uri]) {
        $resource = $model::factory()->create();

        patchJson($uri($resource), [
            'metadata' => [
                'twitter_titolo' => ['not a string'],
            ],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('metadata.twitter_titolo');
    }
});

function metadataUpdateCases(): array
{
    $prefix = config('contento.routes.api.v1.prefix');

    return [
        [Page::class, fn ($model): string => $prefix . '/pages/' . $model->getKey()],
        [ContentTag::class, fn ($model): string => $prefix . '/content-tags/' . $model->getKey()],
        [Metadata::class, fn ($model): string => $prefix . '/metadata/' . $model->getKey()],
        [CatalogImage::class, fn ($model): string => $prefix . '/catalog-images/' . $model->getKey()],
    ];
}

function seoMetadataUpdateCases(): array
{
    return array_slice(metadataUpdateCases(), 0, 3);
}
