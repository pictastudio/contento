<?php

namespace PictaStudio\Contento\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use PictaStudio\Contento\Models\MailForm;

class MailFormPolicy
{
    use HandlesAuthorization;

    public function viewAny(mixed $user): bool
    {
        return true;
    }

    public function view(mixed $user, MailForm $mailForm): bool
    {
        return true;
    }

    public function create(mixed $user): bool
    {
        return true;
    }

    public function update(mixed $user, MailForm $mailForm): bool
    {
        return true;
    }

    public function delete(mixed $user, MailForm $mailForm): bool
    {
        return true;
    }
}
