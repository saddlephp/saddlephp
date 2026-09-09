<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use SaddlePHP\Saddle;
use SaddlePHP\Tests\Fixtures\FormattedHorseResource;
use Workbench\App\Models\Horse;

beforeEach(function () {
    app(Saddle::class)->register([FormattedHorseResource::class]);
    $this->actingAsUser();
});

it('renders the formatted value in the index cells', function () {
    Horse::factory()->create(['name' => 'Cisco', 'age' => 7]);
    Horse::factory()->create(['name' => 'Scout', 'age' => null]);

    $this->get('/admin/resources/formatted-horses')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('rows.data', fn ($rows) => collect($rows)->pluck('cells.age')->sort()->values()->all()
                === ['Cisco is 7', 'unrecorded'])
        );
});

/**
 * The point of formatting after resolve() rather than in a model accessor: the
 * column still names a real database column, so ORDER BY still compiles.
 */
it('still sorts a formatted column in the database', function () {
    Horse::factory()->create(['name' => 'Cisco', 'age' => 20]);
    Horse::factory()->create(['name' => 'Scout', 'age' => 3]);

    $this->get('/admin/resources/formatted-horses?sort=age&direction=asc')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('query.sort', 'age')
            // 3 before 20 -- ordered numerically by the column, not
            // lexicographically by the formatted string it renders as.
            ->where('rows.data.0.cells.age', 'Scout is 3')
            ->where('rows.data.1.cells.age', 'Cisco is 20')
        );
});

it('exports the formatted text rather than the raw column', function () {
    Horse::factory()->create(['name' => 'Cisco', 'age' => 7]);

    $csv = $this->get('/admin/resources/formatted-horses/export')->streamedContent();

    expect($csv)->toContain('Cisco is 7');
});
