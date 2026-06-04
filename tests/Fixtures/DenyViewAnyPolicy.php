<?php

declare(strict_types=1);

namespace SaddlePHP\Tests\Fixtures;

use Workbench\App\Models\User;

class DenyViewAnyPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }
}
