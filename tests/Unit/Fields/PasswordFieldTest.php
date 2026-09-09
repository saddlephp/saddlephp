<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Hash;
use SaddlePHP\Fields\Password;
use SaddlePHP\Tests\Fixtures\HashedUser;
use Workbench\App\Models\User;

it('serializes as a masked text input', function () {
    $payload = Password::make('password')->toArray();

    expect($payload['type'])->toBe('password')
        ->and($payload['component'])->toBe('text-field')
        ->and($payload['label'])->toBe('Password');
});

it('is nullable and bounded by default', function () {
    expect(Password::make('password')->getRules())->toBe(['nullable', 'string', 'max:65535']);
});

/**
 * The base Text field returns the column's contents as the input's value, which
 * would put the bcrypt hash into the edit page's HTML and into the Inertia
 * payload. It buys nothing: the hash is unusable in a form.
 */
it('never hands the stored hash back to the form', function () {
    $user = User::factory()->create(['password' => bcrypt('correct-horse')]);

    expect(Password::make('password')->resolve($user))->toBeNull()
        ->and(Password::make('password')->toArray($user)['value'])->toBeNull();
});

it('keeps the hash off the read-only view page too', function () {
    $user = User::factory()->create(['password' => bcrypt('correct-horse')]);

    expect(Password::make('password')->toDisplay($user)['display'])->toBeNull();
});

/**
 * The behaviour the field exists for. An edit form cannot show the current
 * password, so the input always renders empty -- and a plain field therefore
 * sets the password to an empty string every time somebody edits a user's name.
 */
it('leaves the stored hash byte-identical when submitted blank', function (mixed $blank) {
    $user = User::factory()->create(['password' => bcrypt('correct-horse')]);
    $before = $user->password;

    Password::make('password')->fill($user, $blank);
    $user->save();

    expect($user->refresh()->password)->toBe($before);
})->with([
    'null' => null,
    'empty string' => '',
    'whitespace' => '   ',
]);

it('sets a real value', function () {
    $user = User::factory()->create(['password' => bcrypt('correct-horse')]);

    Password::make('password')->fill($user, 'a-new-secret');

    expect($user->password)->toBe('a-new-secret');
});

/**
 * With Laravel's `hashed` cast -- the pairing this field is meant to be used
 * with -- a blank submission is worse than a no-op would be: the cast
 * faithfully hashes '', the row keeps a valid-looking bcrypt hash, and the only
 * symptom is that nobody can sign in as that account again. No error, no log.
 */
it('hashes a real value and refuses to hash a blank one', function () {
    $user = HashedUser::query()->create([
        'name' => 'Matthew',
        'email' => 'matthew@example.test',
        'password' => 'correct-horse',
        'is_admin' => true,
    ]);
    $before = $user->password;

    expect($before)->not->toBe('correct-horse');

    Password::make('password')->fill($user, '');
    $user->save();
    expect($user->refresh()->password)->toBe($before);

    Password::make('password')->fill($user, 'a-new-secret');
    $user->save();

    expect($user->refresh()->password)->not->toBe($before)
        ->and($user->password)->not->toBe('a-new-secret')
        ->and(Hash::check('a-new-secret', $user->password))->toBeTrue();
});
