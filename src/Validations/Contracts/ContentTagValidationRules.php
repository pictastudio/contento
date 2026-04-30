<?php

namespace PictaStudio\Contento\Validations\Contracts;

interface ContentTagValidationRules extends ProvidesValidationRules
{
    public function getBulkUpdateValidationRules(): array;
}
