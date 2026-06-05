Saddle supports opt-in, URL-scoped multi-tenancy. A tenant is any Eloquent model. When enabled, the panel mounts under `/admin/{tenant}` and every data path is scoped server-side. Tenancy off means byte-identical v0.5 behaviour.

### Enabling tenancy

Set `tenancy.model` in `config/saddle.php` to your tenant model class:

```php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

`relationship` is the Eloquent relation on the tenant model that returns its members. Saddle calls `$tenant->{relationship}()->whereKey($user)->exists()` to enforce membership.

### Declaring tenant scope on a resource

Add `$tenant` to each resource you want scoped. Its value is the Eloquent BelongsTo relation name on the record model pointing back to the tenant:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch';
}
```

Resources without `$tenant` (shared lookup tables, global reference data) remain unscoped by design. They are accessible from any tenant context.

### URL shape and access control

When tenancy is enabled, every panel URL includes the tenant's route key:

```
/admin/{tenant}/resources/horses
/admin/{tenant}/resources/horses/create
/admin/{tenant}/resources/horses/42/edit
```

Saddle enforces two guards before any controller runs:

- **Unknown tenant key** returns 404. The segment is resolved by route-key lookup on the configured model.
- **Non-member** returns 403. Membership is checked via the configured `relationship` relation.

### Scope guarantees

Every data path applies the tenant scope server-side:

- **Index listings** run through a `whereBelongsTo` constraint on the declared `$tenant` relation.
- **Search and filters** apply on top of the same scoped base query, so search results and filter options never leak cross-tenant rows.
- **Record lookups** for edit, update, and destroy all resolve through the scoped query. A cross-tenant ID returns 404 before any policy runs.
- **Stores** stamp the current tenant on new records after filling form values. Any tenant foreign key submitted by the client is overwritten, so the scope cannot be bypassed by crafting the request payload.
- **Relation option lists** apply the same `whereBelongsTo` scope automatically when the related model's registered resource declares `$tenant`. The `modifyOptionsQuery` hook composes on top of this built-in scope.

### Tenant switcher

When the authenticated user belongs to more than one tenant, the panel sidebar shows a select listing all their memberships. Selecting a different tenant navigates to the same panel path under the new tenant key.

### Caveats

**Do not expose the `$tenant` relation as a form field on a scoped resource.** Saddle stamps the tenant server-side on store, but placing a `BelongsTo::make('ranch')` field on an edit form would let a submitted value re-point the record to a different tenant on update. Keep the tenant relationship out of the form schema entirely.

**A saved relation label still renders on the edit form even when the related row falls outside the current scope.** `BelongsTo` resolves the current selection with an unscoped query so that a record's label never disappears after a scope change (for example, when a rider is later moved out of the current ranch). Only the option list for new selections is filtered by scope.

**Changing the tenancy config requires `php artisan route:clear`**, because the `{tenant}` URL prefix is decided at boot time from the config. Long-running application servers must ensure that request state is fully reset between requests. The bound tenant lives on the Saddle singleton, which is resolved fresh per request under the default container singleton lifetime, so standard PHP-FPM and Octane request isolation both behave correctly.
