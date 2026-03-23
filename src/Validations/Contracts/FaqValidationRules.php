<?php

namespace PictaStudio\Contento\Validations\Contracts;

interface FaqValidationRules extends ProvidesValidationRules
{
    public function getBulkUpsertValidationRules(): array;
}
