<?php

namespace JeffersonGoncalves\Semrush;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Semrush\Exceptions\SemrushException;

/**
 * Thin client over the Semrush Analytics API (https://api.semrush.com/). Every
 * method maps to a single report `type` and returns the semicolon-separated CSV
 * body decoded into a list of associative rows keyed by the report's columns.
 *
 * Semrush answers errors with HTTP 200 and a body starting with `ERROR`, so a
 * failed report raises SemrushException; a non-2xx response raises
 * Illuminate\Http\Client\RequestException.
 */
class Semrush
{
    /**
     * Rank, traffic and cost overview for a domain across every database.
     *
     * @return array<int, array<string, string>>
     */
    public function domainOverview(string $domain): array
    {
        return $this->get([
            'type' => 'domain_ranks',
            'domain' => $domain,
            'export_columns' => 'Db,Dn,Rk,Or,Ot,Oc,Ad,At,Ac',
        ]);
    }

    /**
     * Organic keywords a domain ranks for.
     *
     * @return array<int, array<string, string>>
     */
    public function domainOrganic(string $domain, ?string $database = null, ?int $limit = null): array
    {
        return $this->get([
            'type' => 'domain_organic',
            'domain' => $domain,
            'database' => $database ?? $this->database(),
            'export_columns' => 'Ph,Po,Pp,Pd,Nq,Cp,Ur,Tr,Tc,Co,Nr',
            'display_limit' => $limit,
        ]);
    }

    /**
     * Domains competing with the given domain for the same organic keywords.
     *
     * @return array<int, array<string, string>>
     */
    public function domainCompetitors(string $domain, ?string $database = null, ?int $limit = null): array
    {
        return $this->get([
            'type' => 'domain_organic_organic',
            'domain' => $domain,
            'database' => $database ?? $this->database(),
            'export_columns' => 'Dn,Cr,Np,Or,Ot,Oc,Ad',
            'display_limit' => $limit,
        ]);
    }

    /**
     * Volume, CPC, competition and result count for a keyword.
     *
     * @return array<int, array<string, string>>
     */
    public function keywordOverview(string $phrase, ?string $database = null): array
    {
        return $this->get([
            'type' => 'phrase_all',
            'phrase' => $phrase,
            'database' => $database ?? $this->database(),
            'export_columns' => 'Ph,Nq,Cp,Co,Nr',
        ]);
    }

    /**
     * Keywords semantically related to a seed phrase.
     *
     * @return array<int, array<string, string>>
     */
    public function relatedKeywords(string $phrase, ?string $database = null, ?int $limit = null): array
    {
        return $this->get([
            'type' => 'phrase_related',
            'phrase' => $phrase,
            'database' => $database ?? $this->database(),
            'export_columns' => 'Ph,Nq,Cp,Co,Nr,Td',
            'display_limit' => $limit,
        ]);
    }

    /**
     * Keyword difficulty index for a phrase.
     *
     * @return array<int, array<string, string>>
     */
    public function keywordDifficulty(string $phrase, ?string $database = null): array
    {
        return $this->get([
            'type' => 'phrase_kdi',
            'phrase' => $phrase,
            'database' => $database ?? $this->database(),
            'export_columns' => 'Ph,Kd',
        ]);
    }

    /**
     * Backlink totals for a target.
     *
     * @return array<int, array<string, string>>
     */
    public function backlinksOverview(string $target, string $targetType = 'root_domain'): array
    {
        return $this->get([
            'type' => 'backlinks_overview',
            'target' => $target,
            'target_type' => $targetType,
        ]);
    }

    /**
     * Individual backlinks pointing at a target.
     *
     * @return array<int, array<string, string>>
     */
    public function backlinks(string $target, string $targetType = 'root_domain', ?int $limit = null): array
    {
        return $this->get([
            'type' => 'backlinks',
            'target' => $target,
            'target_type' => $targetType,
            'export_columns' => 'source_url,source_title,target_url,anchor',
            'display_limit' => $limit,
        ]);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<int, array<string, string>>
     *
     * @throws RequestException
     * @throws SemrushException
     */
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

    /**
     * Decode a Semrush `;`-separated CSV export into associative rows.
     *
     * @return array<int, array<string, string>>
     */
    private function parseCsv(string $body): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $body) ?: [];
        $lines = array_values(array_filter($lines, fn (string $line) => trim($line) !== ''));

        if (count($lines) < 2) {
            return [];
        }

        $headers = str_getcsv(array_shift($lines), ';', '"', '\\');
        $rows = [];

        foreach ($lines as $line) {
            $values = str_getcsv($line, ';', '"', '\\');
            $row = [];

            foreach ($headers as $index => $header) {
                $row[(string) $header] = (string) ($values[$index] ?? '');
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function key(): string
    {
        $key = config('semrush.key') ?? config('services.semrush.key');

        return is_string($key) ? $key : '';
    }

    private function database(): string
    {
        $database = config('semrush.database', 'us');

        return is_string($database) && $database !== '' ? $database : 'us';
    }

    private function baseUrl(): string
    {
        $baseUrl = config('semrush.base_url', 'https://api.semrush.com');

        return is_string($baseUrl) && $baseUrl !== '' ? $baseUrl : 'https://api.semrush.com';
    }
}
