<?php

declare(strict_types=1);

namespace SaddlePHP\Tests\Fixtures;

use Workbench\App\Models\Horse;
use Workbench\App\Models\User;

class LockedDownHorsePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Horse $horse): bool
    {
        return false;
    }
}
