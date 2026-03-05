<?php

namespace PictaStudio\Contento\Contracts;

interface ContentTagValidationRules
{
    public function getStoreValidationRules(): array;

    public function getUpdateValidationRules(): array;
}
