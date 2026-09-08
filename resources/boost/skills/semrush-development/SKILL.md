---
name: semrush-development
description: Build and work with the Laravel Semrush client - a thin wrapper over the Semrush Analytics API (domain, keyword and backlink reports)
---

# Semrush Development

## When to use this skill

Use this skill when:

- Adding a new Semrush report `type` to the `Semrush` client
- Adjusting how query parameters or `export_columns` are built
- Working on the CSV decoding
- Writing tests for Semrush API interactions with `Http::fake()`
- Troubleshooting authentication, database selection or `ERROR` bodies

## Core Concepts

### The `Semrush` client

`src/Semrush.php` is a single class with one public method per report. Every
method builds a query array and delegates to `get()`, which:

1. Appends `key` (from config) and `export_escape=1`.
2. Strips `null`-valued params with `array_filter()` so optional ones
   (`database`, `display_limit`) are omitted rather than sent as `"null"`.
3. Calls `$response->throw()` so a non-2xx response raises
   `Illuminate\Http\Client\RequestException`.
4. Raises `SemrushException` when the 200 body starts with `ERROR`.
5. Decodes the `;`-separated CSV into `array<int, array<string, string>>`.

```php
public function get(array $query): array
{
    $query['key'] = $this->key();
    $query['export_escape'] = '1';

    $response = Http::baseUrl($this->baseUrl())
        ->get('/', array_filter($query, fn ($value) => $value !== null));

    $response->throw();

    $body = trim($response->body());

    if (str_starts_with($body, 'ERROR')) {
        throw new SemrushException($body);
    }

    return $this->parseCsv($body);
}
```

`get()` is public on purpose — it is the escape hatch for any report type the
client does not yet wrap.

### Why an exception class

Semrush does not use HTTP status codes for report failures. `WRONG KEY`,
`NOTHING FOUND` and quota errors all arrive as **HTTP 200** with a body like
`ERROR 120 :: WRONG KEY`. Without the `str_starts_with($body, 'ERROR')` check
those would silently decode to an empty array.

### CSV decoding

`export_escape=1` makes Semrush quote fields, so `parseCsv()` uses
`str_getcsv($line, ';', '"', '\\')` rather than `explode(';', ...)` — a value
containing a literal `;` would otherwise shift every later column. The `$escape`
argument is passed explicitly because PHP 8.4 deprecates relying on its default.

The first non-empty line is the header row; a body with fewer than two lines
decodes to `[]`.

### Facade and key resolution

`src/Facades/Semrush.php` proxies to a container singleton named `semrush`,
registered in `SemrushServiceProvider::packageRegistered()`.

```php
private function key(): string
{
    $key = config('semrush.key') ?? config('services.semrush.key');

    return is_string($key) ? $key : '';
}
```

`config('semrush.key')` reads `env('SEMRUSH_API_KEY')`, falling back to
`config('services.semrush.key')` — the pattern used across the other
jeffersongoncalves API-client packages.

### Database resolution

Keyword and organic reports need a regional database. A `null` `$database`
argument resolves to `config('semrush.database')`, defaulting to `us`. Reports
that are database-agnostic (`domain_ranks`, `backlinks*`) must not send it.

## Adding a New Report

1. Add a public method to `src/Semrush.php` returning
   `array<int, array<string, string>>`.
2. Build the query with the Semrush param names (`type`, `domain`/`phrase`/`target`,
   `export_columns`, `display_limit`), passing `null` for anything optional.
3. Use `$database ?? $this->database()` only for reports that accept a database.
4. Add the method to the `@method static` block in `src/Facades/Semrush.php`.
5. Add a `Http::fake()` test in `tests/Feature/SemrushTest.php` asserting the
   `type=` and the params built from the arguments.

## Common Patterns

### Success test

```php
it('parses a semicolon separated csv body into associative rows', function () {
    Http::fake([
        'api.semrush.com/*' => csv("Ph;Nq;Cp\nlaravel;12100;1.35\n"),
    ]);

    expect(app(Semrush::class)->keywordOverview('laravel'))->toBe([
        ['Ph' => 'laravel', 'Nq' => '12100', 'Cp' => '1.35'],
    ]);
});
```

### Error test

```php
it('throws a SemrushException when the body reports an error', function () {
    Http::fake([
        'api.semrush.com/*' => csv('ERROR 50 :: NOTHING FOUND'),
    ]);

    expect(fn () => app(Semrush::class)->keywordOverview('laravel'))
        ->toThrow(SemrushException::class);
});
```

## Troubleshooting

### Every method returns `[]`

**Cause**: The report answered with a single header line, or with an `ERROR`
body that predates the `str_starts_with` guard.

**Solution**: Dump `$response->body()`. A real empty report is one header line;
anything starting with `ERROR` should have thrown.

### Columns are shifted by one

**Cause**: A quoted field containing `;` was split with `explode()` instead of
`str_getcsv()`, or the request dropped `export_escape=1`.

**Solution**: Keep decoding in `parseCsv()`; never bypass `get()`.

### `RequestException` on every call in tests

**Cause**: `Http::preventStrayRequests()` (set in `tests/Pest.php`) blocks any
request that doesn't match a `Http::fake()` pattern.

**Solution**: Fake `'api.semrush.com/*'` — every report hits the same host and
path, differing only by query string.

## API Reference

| Method | Report `type` |
|--------|---------------|
| `domainOverview(string $domain)` | `domain_ranks` |
| `domainOrganic(string $domain, ?string $database, ?int $limit)` | `domain_organic` |
| `domainCompetitors(string $domain, ?string $database, ?int $limit)` | `domain_organic_organic` |
| `keywordOverview(string $phrase, ?string $database)` | `phrase_all` |
| `relatedKeywords(string $phrase, ?string $database, ?int $limit)` | `phrase_related` |
| `keywordDifficulty(string $phrase, ?string $database)` | `phrase_kdi` |
| `backlinksOverview(string $target, string $targetType)` | `backlinks_overview` |
| `backlinks(string $target, string $targetType, ?int $limit)` | `backlinks` |
| `get(array $query)` | any |

**Returns**: `array<int, array<string, string>>` for every method.

**Throws**: `SemrushException` on an `ERROR` body,
`Illuminate\Http\Client\RequestException` on any non-2xx response.
