<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Workbench\App\Models\Horse;
use Workbench\App\Models\Rider;

it('serves relation options on the create form', function () {
    $this->actingAsUser();
    Rider::factory()->create(['name' => 'Amos']);
    Rider::factory()->create(['name' => 'Billie']);

    $this->get('/admin/resources/horses/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('fields.4.component', 'select-field')
            ->where('fields.4.name', 'rider_id')
            ->where('fields.4.label', 'Rider')
            ->count('fields.4.options', 2)
            ->where('fields.4.options.0.label', 'Amos')
        );
});

it('stores a valid relation foreign key', function () {
    $this->actingAsUser();
    $rider = Rider::factory()->create();

    $this->post('/admin/resources/horses', [
        'name' => 'Cisco', 'breed' => 'quarter', 'rider_id' => $rider->id,
        'age' => 7, 'foaled_on' => '2019-05-01',
    ])->assertRedirect('/admin/resources/horses');

    expect(Horse::query()->where('name', 'Cisco')->first()->rider_id)->toBe($rider->id);
});

it('rejects foreign keys that do not exist', function () {
    $this->actingAsUser();

    $this->post('/admin/resources/horses', ['name' => 'Cisco', 'rider_id' => 999])
        ->assertSessionHasErrors(['rider_id']);

    expect(Horse::query()->count())->toBe(0);
});

it('renders badge, boolean, relation and formatted date cells', function () {
    $this->actingAsUser();
    $rider = Rider::factory()->create(['name' => 'Tex']);
    $horse = Horse::factory()->create([
        'name' => 'Cisco', 'breed' => 'quarter', 'is_saddled' => true, 'rider_id' => $rider->id,
    ]);

    $this->get('/admin/resources/horses')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('columns.1.type', 'badge')
            ->where('columns.1.colors.quarter', 'accent')
            ->where('columns.2.type', 'boolean')
            ->where('rows.data.0.cells', function ($cells) use ($horse) {
                $cells = collect($cells);

                return $cells['is_saddled'] === true
                    && $cells['rider.name'] === 'Tex'
                    && $cells['created_at'] === $horse->created_at->format('M j, Y');
            })
        );
});

it('validates the number bounds', function () {
    $this->actingAsUser();

    $this->post('/admin/resources/horses', ['name' => 'Cisco', 'age' => 99])
        ->assertSessionHasErrors(['age']);
});
