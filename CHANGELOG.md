# Changelog

All notable changes to this project will be documented in this file.

## v1.0.0 - 2026-09-08

Initial release.

A lightweight Semrush Analytics API client for Laravel.

- Domain reports: `domainOverview()`, `domainOrganic()`, `domainCompetitors()`
- Keyword reports: `keywordOverview()`, `relatedKeywords()`, `keywordDifficulty()`
- Backlink reports: `backlinksOverview()`, `backlinks()`
- `get()` escape hatch for any other report type
- Semicolon-separated CSV decoded into plain associative rows
- `ERROR` bodies (HTTP 200) raise `SemrushException`; non-2xx raises `RequestException`
- Config via `SEMRUSH_API_KEY` / `SEMRUSH_DATABASE`, with a `services.semrush.key` fallback

## [Unreleased]
