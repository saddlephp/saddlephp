<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Workbench\App\Models\Horse;
use Workbench\App\Models\User;

it('imports valid rows and skips invalid ones', function () {
    $this->actingAsUser();

    $csv = "name,breed\nCisco,quarter\n,mustang\nScout,appaloosa\n"; // row 2 has no name (required)
    $file = UploadedFile::fake()->createWithContent('horses.csv', $csv);

    $this->post('/admin/resources/horses/import', ['file' => $file])
        ->assertRedirect('/admin/resources/horses')
        ->assertSessionHas('success');

    expect(Horse::pluck('name')->all())->toEqualCanonicalizing(['Cisco', 'Scout']);
});

it('gates import behind create', function () {
    $this->actingAsUser(['is_admin' => false]);
    Gate::policy(Horse::class, DenyImportCreatePolicy::class);

    $file = UploadedFile::fake()->createWithContent('horses.csv', "name\nCisco\n");
    $this->post('/admin/resources/horses/import', ['file' => $file])->assertForbidden();
});

class DenyImportCreatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }
}
