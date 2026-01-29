<?php

namespace PictaStudio\Contento\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use PictaStudio\Contento\Models\FaqCategory;

class FaqCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(mixed $user): bool
    {
        return true;
    }

    public function view(mixed $user, FaqCategory $faqCategory): bool
    {
        return true;
    }

    public function create(mixed $user): bool
    {
        return true;
    }

    public function update(mixed $user, FaqCategory $faqCategory): bool
    {
        return true;
    }

    public function delete(mixed $user, FaqCategory $faqCategory): bool
    {
        return true;
    }
}
