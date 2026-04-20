<?php

namespace PictaStudio\Contento\Validations\Contracts;

interface MenuItemValidationRules extends ProvidesValidationRules
{
    public function getBulkUpsertValidationRules(): array;
}
