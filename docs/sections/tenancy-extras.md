Two optional hooks extend multi-tenancy: an access gate (for billing/subscription checks) and a tenant registration flow.

### Access gate

Set `saddle.tenancy.gate` to an invokable class. It runs after a tenant is resolved and membership is confirmed, receiving the request and the tenant. Return a `Response` (such as a redirect) to deny access, or `null` to allow it. This is where a billing or subscription check lives.

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Team::class,
    'gate' => App\Saddle\RequireSubscription::class,
],
```

```php
class RequireSubscription
{
    public function __invoke($request, $tenant)
    {
        return $tenant->subscribed() ? null : redirect('/billing');
    }
}
```

### Tenant registration

Set `saddle.tenancy.registration` to a class implementing `SaddlePHP\Tenancy\RegistersTenants`. Saddle mounts a `/{path}/register` page and shows a **＋ New workspace** link in the tenant switcher. Your handler declares the form fields and creates the tenant.

```php
use SaddlePHP\Tenancy\RegistersTenants;

class TeamRegistration implements RegistersTenants
{
    public function fields(): array
    {
        return [Text::make('name')->required()];
    }

    public function register(array $validated, $user): Model
    {
        $team = Team::create($validated);
        $team->users()->attach($user);

        return $team; // Saddle redirects to the new tenant's panel
    }
}
```

Registration is off until you configure a handler; the panel renders the fields, validates against their rules, calls your `register()`, and redirects to the new tenant.
