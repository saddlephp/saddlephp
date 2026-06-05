<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Horse;

beforeEach(function () {
    Storage::fake('public');
});

// ---------------------------------------------------------------------------
// FileUpload: multipart create stores the file and persists its path
// ---------------------------------------------------------------------------
it('stores an uploaded photo on create and persists its path', function () {
    $this->actingAsUser();

    $this->post('/admin/resources/horses', [
        'name' => 'Cisco',
        'photo' => UploadedFile::fake()->image('cisco.jpg'),
    ])->assertRedirect('/admin/resources/horses');

    $horse = Horse::query()->where('name', 'Cisco')->firstOrFail();

    expect($horse->photo)->not->toBeNull()
        ->and($horse->photo)->toStartWith('horses/');

    Storage::disk('public')->assertExists($horse->photo);
});

// ---------------------------------------------------------------------------
// FileUpload: oversize image is rejected by the maxSize (max:4096) rule
// ---------------------------------------------------------------------------
it('rejects an oversize photo upload', function () {
    $this->actingAsUser();

    $this->from('/admin/resources/horses/create')
        ->post('/admin/resources/horses', [
            'name' => 'Cisco',
            'photo' => UploadedFile::fake()->image('huge.jpg')->size(5000),
        ])
        ->assertSessionHasErrors(['photo']);

    expect(Horse::query()->count())->toBe(0);
});

// ---------------------------------------------------------------------------
// FileUpload: a non-image file is rejected by the image() rule
// ---------------------------------------------------------------------------
it('rejects a non-image file for an image upload field', function () {
    $this->actingAsUser();

    $this->from('/admin/resources/horses/create')
        ->post('/admin/resources/horses', [
            'name' => 'Cisco',
            'photo' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ])
        ->assertSessionHasErrors(['photo']);

    expect(Horse::query()->count())->toBe(0);
});

// ---------------------------------------------------------------------------
// FileUpload: updating without a photo key keeps the stored path untouched
// ---------------------------------------------------------------------------
it('keeps the stored photo when an update omits the photo key', function () {
    $this->actingAsUser();
    $horse = Horse::factory()->create(['name' => 'Cisco', 'photo' => 'horses/keep.jpg']);

    $this->put("/admin/resources/horses/{$horse->id}", ['name' => 'Dakota'])
        ->assertRedirect('/admin/resources/horses');

    expect($horse->fresh())
        ->name->toBe('Dakota')
        ->photo->toBe('horses/keep.jpg');
});

// ---------------------------------------------------------------------------
// FileUpload: an explicit null clears the stored path
// ---------------------------------------------------------------------------
it('clears the stored photo when an update sends an explicit null', function () {
    $this->actingAsUser();
    $horse = Horse::factory()->create(['name' => 'Cisco', 'photo' => 'horses/keep.jpg']);

    $this->put("/admin/resources/horses/{$horse->id}", ['name' => 'Cisco', 'photo' => null])
        ->assertRedirect('/admin/resources/horses');

    expect($horse->fresh()->photo)->toBeNull();
});

// ---------------------------------------------------------------------------
// DateTime: round-trips create -> edit payload as Y-m-d\TH:i
// ---------------------------------------------------------------------------
it('round-trips a datetime value from create to the edit payload', function () {
    $this->actingAsUser();

    $this->post('/admin/resources/horses', [
        'name' => 'Cisco',
        'last_vet_visit' => '2026-06-01T14:30',
    ])->assertRedirect('/admin/resources/horses');

    $horse = Horse::query()->where('name', 'Cisco')->firstOrFail();

    expect($horse->last_vet_visit)->not->toBeNull();

    $this->get("/admin/resources/horses/{$horse->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('fields', fn ($fields) => findField(collect($fields)->all(), 'last_vet_visit')['value'] === '2026-06-01T14:30')
        );
});

// ---------------------------------------------------------------------------
// Tree payload over HTTP: sections + tabs render and findField locates leaves
// ---------------------------------------------------------------------------
it('serves a layout tree over HTTP that findField can walk', function () {
    $this->actingAsUser();

    $this->get('/admin/resources/horses/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('fields', function ($fields) {
                $fields = collect($fields)->all();
                $hasTabs = collect($fields)->contains(fn ($node) => is_array($node) && ($node['layout'] ?? null) === 'tabs');

                return ($fields[0]['layout'] ?? null) === 'section'
                    && $hasTabs
                    && findField($fields, 'name') !== null
                    && findField($fields, 'photo') !== null
                    && findField($fields, 'notes') !== null
                    && findField($fields, 'rider_id') !== null;
            })
        );
});

// ---------------------------------------------------------------------------
// canSee gate over HTTP: notes is absent from the tree for a non-admin
// ---------------------------------------------------------------------------
it('hides the gated notes field from the layout tree for non-admins', function () {
    $this->actingAsUser(['is_admin' => false]);

    $this->get('/admin/resources/horses/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('fields', fn ($fields) => findField(collect($fields)->all(), 'notes') === null
                && findField(collect($fields)->all(), 'name') !== null)
        );
});
