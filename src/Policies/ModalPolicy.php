<?php

namespace PictaStudio\Contento\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use PictaStudio\Contento\Models\Modal;

class ModalPolicy
{
    use HandlesAuthorization;

    public function viewAny(mixed $user): bool
    {
        return true;
    }

    public function view(mixed $user, Modal $modal): bool
    {
        return true;
    }

    public function create(mixed $user): bool
    {
        return true;
    }

    public function update(mixed $user, Modal $modal): bool
    {
        return true;
    }

    public function delete(mixed $user, Modal $modal): bool
    {
        return true;
    }
}
