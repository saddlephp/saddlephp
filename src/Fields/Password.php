<?php

declare(strict_types=1);

namespace SaddlePHP\Fields;

use Illuminate\Database\Eloquent\Model;

/**
 * A password input that leaves the stored value alone when submitted blank.
 *
 * Masking was already solved -- `Text::make('password')->type('password')`
 * emits the type in the field meta and the panel binds it straight to the input
 * -- and hashing is Laravel's job via the `hashed` cast. Neither covers the one
 * behaviour every panel that manages users has to write for itself:
 *
 * AN EDIT FORM CANNOT SHOW THE CURRENT PASSWORD, so the field always renders
 * empty, and a plain field therefore SETS THE PASSWORD TO AN EMPTY STRING every
 * time somebody edits a user's name.
 *
 * The `hashed` cast makes that worse rather than better: it faithfully hashes
 * '', so the account keeps a valid-looking bcrypt hash and the only symptom is
 * that nobody can sign in as them again. No error, no log line.
 *
 *     Password::make('password')->helper('Leave blank to keep the current one.'),
 *
 * Pair it with `'password' => 'hashed'` in the model's casts. Saddle does not
 * hash for you: the choice of hasher, and whether the column is hashed at all,
 * belongs to the application.
 *
 * Leave it optional. `required()` works, but because the field always renders
 * empty it would force every edit of any other attribute to retype the
 * password.
 */
final class Password extends Text
{
    protected string $type = 'password';

    /**
     * Ignore a blank submission so the stored hash survives an edit that was
     * never about the password. A caller wanting to clear the column can set
     * it directly; a form field cannot tell "leave it alone" apart from
     * "set it to nothing", and of the two only one is ever meant.
     */
    public function fill(Model $record, mixed $value): void
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return;
        }

        parent::fill($record, $value);
    }

    /**
     * Never hand the stored value back to the form.
     *
     * This matters as much as the fill(). The base field returns the column's
     * contents as the input's value, which puts the bcrypt hash into the edit
     * page's HTML and into the Inertia payload for anything that can read
     * either. It buys nothing -- the hash is unusable in a form.
     */
    public function resolve(Model $record): mixed
    {
        return null;
    }
}
