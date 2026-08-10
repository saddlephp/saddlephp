<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use SaddlePHP\Fields\FileUpload;
use SaddlePHP\Forms\Form;
use Workbench\App\Models\Horse;

// ---------------------------------------------------------------------------
// Serialization + rules
// ---------------------------------------------------------------------------

it('serializes the file-field component', function () {
    expect(FileUpload::make('photo')->toArray()['component'])->toBe('file-field');
});

it('defaults to nullable file rules with a type allowlist and a size cap', function () {
    $rules = FileUpload::make('photo')->getRules();

    expect($rules[0])->toBe('nullable')
        ->and($rules[1])->toBe('file')
        ->and($rules)->toContain('max:10240');

    expect(mimesList($rules))->not->toBeNull();
});

/**
 * The default allowlist is the only thing standing between an unrestricted
 * FileUpload and same-origin script execution: Laravel names a stored file from
 * its detected content type, so an "invoice.txt" full of HTML lands as
 * <random>.html on the public disk and runs with the admin's session.
 */
it('never allows a type that renders as script from our own origin', function () {
    $allowed = explode(',', mimesList(FileUpload::make('doc')->getRules()));

    foreach (FileUpload::DENIED_EXTENSIONS as $denied) {
        expect($allowed)->not->toContain($denied);
    }
});

it('always caps the size even when maxSize() is never called', function () {
    expect(FileUpload::make('photo')->getRules())->toContain('max:10240');
});

it('image() adds the image rule and skips the default allowlist', function () {
    $rules = FileUpload::make('photo')->image()->getRules();

    expect($rules)->toBe(['nullable', 'file', 'image', 'max:10240']);
});

it('acceptedTypes() adds a mimes rule and replaces the default allowlist', function () {
    expect(FileUpload::make('doc')->acceptedTypes(['pdf'])->getRules())
        ->toBe(['nullable', 'file', 'mimes:pdf', 'max:10240']);
});

it('maxSize() overrides the configured cap', function () {
    expect(FileUpload::make('photo')->maxSize(2048)->getRules())->toContain('max:2048');
});

it('falls back to sane upload paths when a host publishes a partial config block', function () {
    // mergeConfigFrom is a shallow array_merge, so publishing only
    // uploads.disk used to leave uploads.directory null and TypeError on
    // every single upload.
    config(['saddle.uploads' => ['disk' => 'public']]);

    Storage::fake('public');

    $horse = new Horse;
    FileUpload::make('photo')->fill($horse, UploadedFile::fake()->image('p.jpg'));

    expect($horse->photo)->toStartWith(FileUpload::DEFAULT_DIRECTORY.'/');
});

function mimesList(array $rules): ?string
{
    foreach ($rules as $rule) {
        if (is_string($rule) && str_starts_with($rule, 'mimes:')) {
            return substr($rule, strlen('mimes:'));
        }
    }

    return null;
}

it('composes required with every fluent rule', function () {
    expect(
        FileUpload::make('doc')
            ->required()
            ->image()
            ->acceptedTypes(['pdf', 'docx'])
            ->maxSize(4096)
            ->getRules()
    )->toBe(['required', 'file', 'image', 'mimes:pdf,docx', 'max:4096']);
});

// ---------------------------------------------------------------------------
// meta accept attribute
// ---------------------------------------------------------------------------

it('emits image/* accept for image()', function () {
    expect(FileUpload::make('photo')->image()->toArray()['accept'])->toBe('image/*');
});

it('emits a dotted extension list accept for acceptedTypes()', function () {
    expect(FileUpload::make('doc')->acceptedTypes(['pdf', 'docx'])->toArray()['accept'])
        ->toBe('.pdf,.docx');
});

it('emits a null accept by default', function () {
    expect(FileUpload::make('photo')->toArray()['accept'])->toBeNull();
});

// ---------------------------------------------------------------------------
// fill() — storage side effects
// ---------------------------------------------------------------------------

it('stores an uploaded file on the configured disk and sets the path', function () {
    Storage::fake('public');

    $horse = new Horse;
    $file = UploadedFile::fake()->image('p.jpg');

    FileUpload::make('photo')->directory('horses')->fill($horse, $file);

    expect($horse->photo)->toBeString()
        ->and($horse->photo)->toStartWith('horses/');

    Storage::disk('public')->assertExists($horse->photo);
});

it('clears the attribute on an explicit null', function () {
    Storage::fake('public');

    $horse = new Horse;
    $horse->photo = 'horses/old.jpg';

    FileUpload::make('photo')->fill($horse, null);

    expect($horse->photo)->toBeNull();
});

it('ignores a non-file value, leaving the attribute untouched', function () {
    Storage::fake('public');

    $horse = new Horse;
    $horse->photo = 'horses/keep.jpg';

    FileUpload::make('photo')->fill($horse, 'a-string');

    expect($horse->photo)->toBe('horses/keep.jpg');
});

it('honors a custom disk and directory', function () {
    Storage::fake('public');
    Storage::fake('local');

    $horse = new Horse;
    $file = UploadedFile::fake()->create('contract.pdf', 12, 'application/pdf');

    FileUpload::make('photo')->disk('local')->directory('docs')->fill($horse, $file);

    expect($horse->photo)->toStartWith('docs/');

    Storage::disk('local')->assertExists($horse->photo);
    Storage::disk('public')->assertDirectoryEmpty('docs');
});

// ---------------------------------------------------------------------------
// Form-level routing (validated-array shape)
// ---------------------------------------------------------------------------

it('stores through a Form fill with the validated-array shape', function () {
    Storage::fake('public');

    $horse = new Horse;
    $file = UploadedFile::fake()->image('p.jpg');

    $form = Form::make()
        ->model(new Horse)
        ->schema([FileUpload::make('photo')->directory('horses')]);

    $form->fill($horse, ['photo' => $file]);

    expect($horse->photo)->toStartWith('horses/');

    Storage::disk('public')->assertExists($horse->photo);
});
