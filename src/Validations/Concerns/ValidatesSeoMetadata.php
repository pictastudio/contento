<?php

namespace PictaStudio\Contento\Validations\Concerns;

trait ValidatesSeoMetadata
{
    protected function seoMetadataValidationRules(array $metadataRules = ['nullable', 'array']): array
    {
        return [
            'metadata' => $metadataRules,
            'metadata.*' => ['nullable'],
            'metadata.titolo' => ['nullable', 'string', 'max:255'],
            'metadata.autore' => ['nullable', 'string', 'max:255'],
            'metadata.descrizione' => ['nullable', 'string'],
            'metadata.robots' => ['nullable', 'string', 'max:255'],
            'metadata.open_graph_titolo' => ['nullable', 'string', 'max:255'],
            'metadata.open_graph_url' => ['nullable', 'string', 'max:2048'],
            'metadata.open_graph_immagine' => ['nullable', 'string', 'max:2048'],
            'metadata.open_graph_tipo' => ['nullable', 'string', 'max:255'],
            'metadata.twitter_card' => ['nullable', 'string', 'max:255'],
            'metadata.twitter_site' => ['nullable', 'string', 'max:255'],
            'metadata.twitter_creator' => ['nullable', 'string', 'max:255'],
            'metadata.twitter_titolo' => ['nullable', 'string', 'max:255'],
            'metadata.twitter_descrizione' => ['nullable', 'string'],
            'metadata.twitter_src' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
