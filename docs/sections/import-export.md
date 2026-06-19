Every resource can export its records to CSV and import records from a CSV, straight from the index.

### Export

The **Export** button downloads the records currently shown, honoring the active search and filters, scoped to the current tenant. The CSV columns are the resource's table columns, and the header row uses their labels. Export is gated by the `viewAny` policy ability. It streams the result, so large tables export without exhausting memory.

```php
// The export reuses your table definition — no extra config.
public static function table(Table $table): Table
{
    return $table->columns([
        TextColumn::make('name'),
        TextColumn::make('breed'),
    ]);
}
```

### Import

The **Import** button opens an upload page. Upload a CSV whose **header names match your field names** (case-insensitive); each data row is validated against the resource's form rules and, on success, created like a normal store (tenant-stamped when the resource is tenant-scoped). Rows that fail validation are skipped, and the result flash reports how many were created and skipped. Import is gated by the `create` ability.

```text
name,breed
Cisco,quarter
Scout,mustang
```

### Scope (v1.0)

Export and import are synchronous and CSV-only. Import is create-only (it does not update existing records) and maps columns by header name (there is no mapping UI). For very large or recurring jobs, run your own queued command using the same resource definitions.
