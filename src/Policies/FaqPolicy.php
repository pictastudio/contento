<?php

namespace PictaStudio\Contento\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use PictaStudio\Contento\Models\Faq;

class FaqPolicy
{
    use HandlesAuthorization;

    public function viewAny(mixed $user): bool
    {
        return true;
    }

    public function view(mixed $user, Faq $faq): bool
    {
        return true;
    }

    public function create(mixed $user): bool
    {
        return true;
    }

    public function update(mixed $user, Faq $faq): bool
    {
        return true;
    }

    public function delete(mixed $user, Faq $faq): bool
    {
        return true;
    }
}
