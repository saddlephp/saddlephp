<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

afterEach(function () {
    File::deleteDirectory(app_path('Saddle'));
});

it('generates a resource class with the given model', function () {
    $this->artisan('saddle:resource', ['name' => 'PonyResource', '--model' => 'Workbench\\App\\Models\\Horse'])
        ->assertSuccessful();

    $path = app_path('Saddle/PonyResource.php');
    expect(File::exists($path))->toBeTrue();

    $contents = File::get($path);
    expect($contents)->toContain('class PonyResource extends Resource')
        ->toContain('Workbench\App\Models\Horse::class');
});

it('guesses the model from the resource name when omitted', function () {
    $this->artisan('saddle:resource', ['name' => 'HorseResource'])->assertSuccessful();

    expect(File::get(app_path('Saddle/HorseResource.php')))
        ->toContain('\App\Models\Horse::class');
});
