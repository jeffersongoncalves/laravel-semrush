<?php

namespace JeffersonGoncalves\Semrush\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<int, array<string, string>> domainOverview(string $domain)
 * @method static array<int, array<string, string>> domainOrganic(string $domain, ?string $database = null, ?int $limit = null)
 * @method static array<int, array<string, string>> domainCompetitors(string $domain, ?string $database = null, ?int $limit = null)
 * @method static array<int, array<string, string>> keywordOverview(string $phrase, ?string $database = null)
 * @method static array<int, array<string, string>> relatedKeywords(string $phrase, ?string $database = null, ?int $limit = null)
 * @method static array<int, array<string, string>> keywordDifficulty(string $phrase, ?string $database = null)
 * @method static array<int, array<string, string>> backlinksOverview(string $target, string $targetType = 'root_domain')
 * @method static array<int, array<string, string>> backlinks(string $target, string $targetType = 'root_domain', ?int $limit = null)
 * @method static array<int, array<string, string>> get(array<string, mixed> $query)
 *
 * @see \JeffersonGoncalves\Semrush\Semrush
 */
class Semrush extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'semrush';
    }
}
