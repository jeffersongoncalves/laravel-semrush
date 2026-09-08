## Laravel Semrush

### Overview

A lightweight [Semrush Analytics API](https://developer.semrush.com/api/) client
for Laravel. It wraps the domain, keyword and backlink reports behind a small
`Semrush` client/facade, threads your API key, and decodes the `;`-separated
CSV export into plain associative rows — no DTOs, no response wrapper classes.

### Installation

@verbatim
<code-snippet name="Install the package" lang="bash">
composer require jeffersongoncalves/laravel-semrush
</code-snippet>
@endverbatim

### Features

- **Domain reports**: `domainOverview()`, `domainOrganic()`, `domainCompetitors()`.
- **Keyword reports**: `keywordOverview()`, `relatedKeywords()`, `keywordDifficulty()`.
- **Backlink reports**: `backlinksOverview()`, `backlinks()`.
- **Escape hatch**: `get(array $query)` reaches any other Semrush report `type`.

@verbatim
<code-snippet name="Domain overview and keyword research" lang="php">
use JeffersonGoncalves\Semrush\Facades\Semrush;

$overview = Semrush::domainOverview('example.com');
$related = Semrush::relatedKeywords('laravel', database: 'br', limit: 25);
</code-snippet>
@endverbatim

### Configuration

@verbatim
<code-snippet name="Config example" lang="php">
// config/semrush.php
return [
    'key' => env('SEMRUSH_API_KEY'),
    'database' => env('SEMRUSH_DATABASE', 'us'),
    'base_url' => env('SEMRUSH_BASE_URL', 'https://api.semrush.com'),
];
</code-snippet>
@endverbatim

### Best Practices

- Catch `JeffersonGoncalves\Semrush\Exceptions\SemrushException` — Semrush answers errors with HTTP 200 and an `ERROR ...` body, so a bad key or empty report never surfaces as an HTTP failure. Catch `Illuminate\Http\Client\RequestException` for genuine non-2xx responses.
- Every value in a returned row is a **string**; cast to int/float yourself before doing arithmetic.
- Pass `database`/`limit` as `null` (the default) to omit them; `database` then falls back to `config('semrush.database')`.
- Prefer the `Semrush` facade in application code; resolve `JeffersonGoncalves\Semrush\Semrush::class` directly only when you need to swap the implementation in tests.
