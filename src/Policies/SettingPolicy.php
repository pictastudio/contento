<?php

namespace PictaStudio\Contento\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use PictaStudio\Contento\Models\Setting;

class SettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(mixed $user): bool
    {
        return true;
    }

    public function create(mixed $user): bool
    {
        return true;
    }

    public function delete(mixed $user, Setting $setting): bool
    {
        return true;
    }
}
