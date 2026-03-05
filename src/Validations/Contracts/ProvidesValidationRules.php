<?php

namespace PictaStudio\Contento\Validations\Contracts;

interface ProvidesValidationRules
{
    public function getStoreValidationRules(): array;

    public function getUpdateValidationRules(): array;
}
