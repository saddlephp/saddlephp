<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use SaddlePHP\Saddle;
use SaddlePHP\Tests\Fixtures\HashedUser;
use SaddlePHP\Tests\Fixtures\PanelUserResource;

beforeEach(function () {
    app(Saddle::class)->register([PanelUserResource::class]);
    $this->actingAsUser();

    $this->managed = HashedUser::query()->create([
        'name' => 'Wilma',
        'email' => 'wilma@example.test',
        'password' => 'correct-horse',
        'is_admin' => false,
    ]);
});

it('renders the edit form with an empty password input', function () {
    $this->get("/admin/resources/panel-users/{$this->managed->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('fields', function ($fields) {
                $password = findField(collect($fields)->all(), 'password');

                return $password['value'] === null && $password['type'] === 'password';
            })
        );
});

it('keeps the bcrypt hash out of the edit page entirely', function () {
    $response = $this->get("/admin/resources/panel-users/{$this->managed->id}/edit")->assertOk();

    expect($response->getContent())->not->toContain($this->managed->password);
});

/**
 * The failure this field exists to prevent, end to end: an admin edits a user's
 * name, the password input renders empty because it has to, and the account is
 * quietly locked out of its own login. With the `hashed` cast the row keeps a
 * valid-looking hash, so nothing reports a fault.
 */
it('leaves the password alone when an edit submits it blank', function () {
    $before = $this->managed->password;

    $this->put("/admin/resources/panel-users/{$this->managed->id}", [
        'name' => 'Wilma Flint',
        'email' => 'wilma@example.test',
        'password' => '',
    ])->assertRedirect('/admin/resources/panel-users');

    expect($this->managed->refresh())
        ->name->toBe('Wilma Flint')
        ->password->toBe($before);

    expect(Hash::check('correct-horse', $this->managed->password))->toBeTrue();
});

it('sets the password when an edit actually supplies one', function () {
    $this->put("/admin/resources/panel-users/{$this->managed->id}", [
        'name' => 'Wilma',
        'email' => 'wilma@example.test',
        'password' => 'a-new-secret',
    ])->assertRedirect('/admin/resources/panel-users');

    expect(Hash::check('a-new-secret', $this->managed->refresh()->password))->toBeTrue();
});
