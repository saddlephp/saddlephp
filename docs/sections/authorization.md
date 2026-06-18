Saddle consumes standard Laravel policies. Register a policy for a model and the panel enforces it throughout: the index listing, the view page, create and edit forms, row actions, relation managers, and the relation options endpoint. Your role system stays completely in your application code.

### No-policy default

When no policy is registered for a model, all abilities are allowed for every authenticated user. This means a fresh install works out of the box without any policy setup.

### Policy abilities

| Ability | Where it is checked |
|---|---|
| `viewAny` | Resource index page; sidebar visibility (resources whose `viewAny` returns false are hidden from the nav); relation manager listing |
| `view` | Record view page; per-row View link |
| `create` | Create form; store action; relation manager New button and store; relation options endpoint |
| `update` | Edit form; update action; per-row Edit link; relation manager row edit; relation options endpoint (checked against a fresh model instance when no specific record is in scope) |
| `delete` | Destroy action; per-row Delete button; relation manager row delete |

Relation managers check these abilities against the **related** model's policy. See the relations section for details.

### Using any role system

Any layer that backs your policies works unchanged. The panel calls `$user->can($ability, $modelOrClass)` through Laravel's Gate, so whether you use Spatie Permissions, a homegrown `is_admin` flag, or anything else, the integration is transparent.

### Field visibility with `canSee`

Individual fields can be gated per request using the `canSee` modifier. Hidden fields are excluded entirely: they are stripped from the form payload sent to the frontend, contribute no validation rules, are never written on save, and their relation options endpoint returns a 404.

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

The callback receives the current `Request` and must return a real boolean. Keep the closure cheap and idempotent because it may be called several times per request (once per call to `visibleFields()`). Avoid database queries inside the closure; prefer pre-loaded authorisation decisions.

**Return a real boolean.** Using `Gate::inspect(...)` is a common mistake: its `Response` object is always truthy and will never hide the field. Use `Gate::allows('ability', $model)` instead.
