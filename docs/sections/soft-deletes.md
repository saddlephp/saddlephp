Saddle supports Laravel's soft deletes with no configuration. Add the `SoftDeletes` trait to a model and the panel adapts automatically: deletes become soft deletes, the index gains a trashed filter, and trashed rows can be restored or permanently removed.

### Enabling

Use Laravel's trait on the model and make sure the table has a `deleted_at` column:

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Horse extends Model
{
    use SoftDeletes;
}
```

That is the only step. Saddle detects the trait and turns on the soft-delete behavior for that resource.

### The trashed filter

A soft-deletable resource's index gains a **Status** filter with three options:

- **Active** (default) hides trashed records.
- **With trashed** shows active and trashed records together.
- **Only trashed** shows just the trashed records.

### Deleting, restoring, purging

The row **Delete** control soft-deletes a record (it disappears from the default Active view but is recoverable). Surface trashed records with the Status filter and each one offers:

- **Restore** brings the record back into the active set.
- **Delete permanently** removes the row from the database for good, behind a confirmation.

### Authorization

Restore and force-delete are gated by the standard Laravel policy abilities `restore` and `forceDelete`. As with every ability, a resource without a registered policy allows them by default (or denies them when `saddle.authorization.require_policy` is on). Multi-tenancy applies too: a trashed record belonging to another tenant can never be restored or purged.
