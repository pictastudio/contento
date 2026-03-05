<?php

namespace PictaStudio\Contento\Validations\Contracts;

interface SettingValidationRules extends ProvidesValidationRules
{
    public function getBulkUpdateValidationRules(): array;
}
