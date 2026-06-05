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

it('defaults to nullable file rules', function () {
    expect(FileUpload::make('photo')->getRules())->toBe(['nullable', 'file']);
});

it('image() adds the image rule', function () {
    expect(FileUpload::make('photo')->image()->getRules())->toBe(['nullable', 'file', 'image']);
});

it('acceptedTypes() adds a mimes rule', function () {
    expect(FileUpload::make('doc')->acceptedTypes(['pdf'])->getRules())
        ->toBe(['nullable', 'file', 'mimes:pdf']);
});

it('maxSize() adds a max rule in kilobytes', function () {
    expect(FileUpload::make('photo')->maxSize(2048)->getRules())
        ->toBe(['nullable', 'file', 'max:2048']);
});

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
