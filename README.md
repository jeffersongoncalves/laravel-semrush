<div class="filament-hidden">

![Laravel Semrush](https://raw.githubusercontent.com/jeffersongoncalves/laravel-semrush/main/art/jeffersongoncalves-laravel-semrush.png)

</div>

# Laravel Semrush

[![Tests](https://github.com/jeffersongoncalves/laravel-semrush/actions/workflows/tests.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-semrush/actions/workflows/tests.yml)
[![PHPStan](https://github.com/jeffersongoncalves/laravel-semrush/actions/workflows/phpstan.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-semrush/actions/workflows/phpstan.yml)
[![Code Style](https://github.com/jeffersongoncalves/laravel-semrush/actions/workflows/pint.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-semrush/actions/workflows/pint.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-semrush.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-semrush)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-semrush.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-semrush)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/laravel-semrush.svg?style=flat-square)](LICENSE.md)

A lightweight [Semrush Analytics API](https://developer.semrush.com/api/) client for Laravel. It wraps the domain, keyword and backlink reports behind a small `Semrush` client/facade, threads your API key, and decodes the `;`-separated CSV export into plain associative rows — no DTOs, no response wrappers.

## Features

- **Domain overview** — `domainOverview()`
- **Domain organic keywords** — `domainOrganic()`
- **Domain organic competitors** — `domainCompetitors()`
- **Keyword overview** — `keywordOverview()`
- **Related keywords** — `relatedKeywords()`
- **Keyword difficulty** — `keywordDifficulty()`
- **Backlinks overview** — `backlinksOverview()`
- **Backlinks list** — `backlinks()`
- Semicolon-separated CSV decoded into `array<int, array<string, string>>`, keyed by the report's own columns
- Semrush reports errors with HTTP 200 and an `ERROR ...` body — those raise `SemrushException`
- A non-2xx response raises `Illuminate\Http\Client\RequestException`
- Optional `database`/`limit` params are omitted from the query when left `null`

## Installation

```bash
composer require jeffersongoncalves/laravel-semrush
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="semrush-config"
```

## Configuration

Add to your `.env`:

```env
SEMRUSH_API_KEY=your-semrush-api-key
SEMRUSH_DATABASE=us
```

Grab a key at [semrush.com/api-analytics](https://www.semrush.com/api-analytics/).

### Config Options

```php
// config/semrush.php
return [
    'key' => env('SEMRUSH_API_KEY'),
    'database' => env('SEMRUSH_DATABASE', 'us'),
    'base_url' => env('SEMRUSH_BASE_URL', 'https://api.semrush.com'),
];
```

When `semrush.key` is null the client falls back to `config('services.semrush.key')`.

## Usage

Via the facade:

```php
use JeffersonGoncalves\Semrush\Facades\Semrush;

Semrush::domainOverview('example.com');

Semrush::domainOrganic('example.com', database: 'br', limit: 50);

Semrush::domainCompetitors('example.com', limit: 20);

Semrush::keywordOverview('laravel', database: 'us');

Semrush::relatedKeywords('laravel', database: 'us', limit: 25);

Semrush::keywordDifficulty('laravel');

Semrush::backlinksOverview('example.com');

Semrush::backlinks('example.com', targetType: 'domain', limit: 100);
```

Or inject/resolve the underlying client:

```php
use JeffersonGoncalves\Semrush\Semrush;

$semrush = app(Semrush::class);
$semrush->domainOverview('example.com');
```

### Return shape

Every method returns a list of rows keyed by the report's `export_columns`:

```php
Semrush::keywordOverview('laravel');

// [
//     ['Ph' => 'laravel', 'Nq' => '12100', 'Cp' => '1.35', 'Co' => '0.07', 'Nr' => '154000000'],
// ]
```

Values are always strings — cast them yourself where you need numbers.

### Any other report

Every Semrush report type is reachable through `get()`, which appends the key, escapes the export and decodes the CSV for you:

```php
Semrush::get([
    'type' => 'phrase_this',
    'phrase' => 'laravel',
    'database' => 'us',
    'export_columns' => 'Ph,Nq,Cp',
]);
```

### Databases

`us` (default), `br`, `uk`, `de`, `fr`, `es`, … — the full list is in the [Semrush docs](https://developer.semrush.com/api/v3/analytics/basic-docs/#databases). Set the default with `SEMRUSH_DATABASE` or pass `database:` per call.

### Error handling

```php
use Illuminate\Http\Client\RequestException;
use JeffersonGoncalves\Semrush\Exceptions\SemrushException;

try {
    $rows = Semrush::keywordOverview('laravel');
} catch (SemrushException $e) {
    // "ERROR 50 :: NOTHING FOUND", "ERROR 120 :: WRONG KEY", ...
} catch (RequestException $e) {
    // $e->response->status(), $e->response->body(), ...
}
```

## Testing

```bash
composer test
```

## Static Analysis

```bash
composer analyse
```

## Code Formatting

```bash
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
