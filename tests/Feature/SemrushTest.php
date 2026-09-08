<?php

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Semrush\Exceptions\SemrushException;
use JeffersonGoncalves\Semrush\Facades\Semrush as SemrushFacade;
use JeffersonGoncalves\Semrush\Semrush;

/**
 * A faked Semrush response: plain text, `;`-separated CSV.
 */
function csv(string $body)
{
    return Http::response($body, 200, ['Content-Type' => 'text/plain']);
}

it('parses a semicolon separated csv body into associative rows', function () {
    Http::fake([
        'api.semrush.com/*' => csv("Ph;Nq;Cp\nlaravel;12100;1.35\nphp;90500;0.94\n"),
    ]);

    expect(app(Semrush::class)->keywordOverview('laravel'))->toBe([
        ['Ph' => 'laravel', 'Nq' => '12100', 'Cp' => '1.35'],
        ['Ph' => 'php', 'Nq' => '90500', 'Cp' => '0.94'],
    ]);
});

it('honours quoted fields containing the delimiter', function () {
    Http::fake([
        'api.semrush.com/*' => csv("Ph;Nq\n\"laravel; php\";10\n"),
    ]);

    expect(app(Semrush::class)->keywordOverview('laravel'))->toBe([
        ['Ph' => 'laravel; php', 'Nq' => '10'],
    ]);
});

it('returns an empty array when the report has no rows', function () {
    Http::fake([
        'api.semrush.com/*' => csv("Ph;Nq\n"),
    ]);

    expect(app(Semrush::class)->keywordOverview('laravel'))->toBe([]);
});

it('sends the api key and escapes exports on every request', function () {
    Http::fake([
        'api.semrush.com/*' => csv("Db;Dn\nus;example.com\n"),
    ]);

    app(Semrush::class)->domainOverview('example.com');

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'type=domain_ranks')
        && str_contains($request->url(), 'domain=example.com')
        && str_contains($request->url(), 'key=fake-key')
        && str_contains($request->url(), 'export_escape=1'));
});

it('falls back to the configured default database', function () {
    config()->set('semrush.database', 'br');

    Http::fake([
        'api.semrush.com/*' => csv("Ph;Nq\nlaravel;10\n"),
    ]);

    app(Semrush::class)->domainOrganic('example.com');

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'type=domain_organic')
        && str_contains($request->url(), 'database=br'));
});

it('prefers an explicit database over the configured default', function () {
    Http::fake([
        'api.semrush.com/*' => csv("Ph;Nq\nlaravel;10\n"),
    ]);

    app(Semrush::class)->relatedKeywords('laravel', 'de', 25);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'type=phrase_related')
        && str_contains($request->url(), 'database=de')
        && str_contains($request->url(), 'display_limit=25'));
});

it('omits the display limit when it is null', function () {
    Http::fake([
        'api.semrush.com/*' => csv("Dn;Cr\nexample.com;0.5\n"),
    ]);

    app(Semrush::class)->domainCompetitors('example.com');

    Http::assertSent(fn (Request $request) => ! str_contains($request->url(), 'display_limit='));
});

it('requests the keyword difficulty report', function () {
    Http::fake([
        'api.semrush.com/*' => csv("Ph;Kd\nlaravel;61.5\n"),
    ]);

    expect(app(Semrush::class)->keywordDifficulty('laravel', 'us'))
        ->toBe([['Ph' => 'laravel', 'Kd' => '61.5']]);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'type=phrase_kdi'));
});

it('requests the backlinks reports with a target type', function () {
    Http::fake([
        'api.semrush.com/*' => csv("total;domains_num\n42;7\n"),
    ]);

    app(Semrush::class)->backlinksOverview('example.com');
    app(Semrush::class)->backlinks('example.com', 'domain', 10);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'type=backlinks_overview')
        && str_contains($request->url(), 'target_type=root_domain'));

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'type=backlinks&')
        && str_contains($request->url(), 'target_type=domain')
        && str_contains($request->url(), 'display_limit=10'));
});

it('throws a SemrushException when the body reports an error', function () {
    Http::fake([
        'api.semrush.com/*' => csv('ERROR 50 :: NOTHING FOUND'),
    ]);

    expect(fn () => app(Semrush::class)->keywordOverview('laravel'))
        ->toThrow(SemrushException::class, 'ERROR 50 :: NOTHING FOUND');
});

it('throws on a non-2xx response', function () {
    Http::fake([
        'api.semrush.com/*' => Http::response('Forbidden', 403),
    ]);

    expect(fn () => app(Semrush::class)->domainOverview('example.com'))
        ->toThrow(RequestException::class);
});

it('resolves the facade to the Semrush client', function () {
    expect(SemrushFacade::getFacadeRoot())->toBeInstanceOf(Semrush::class);
});

it('falls back to the services.semrush.key config value', function () {
    config()->set('semrush.key', null);
    config()->set('services.semrush.key', 'services-key');

    Http::fake([
        'api.semrush.com/*' => csv("Db;Dn\nus;example.com\n"),
    ]);

    app(Semrush::class)->domainOverview('example.com');

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'key=services-key'));
});
