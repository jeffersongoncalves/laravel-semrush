<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Semrush\Tests\TestCase;

uses(TestCase::class)
    ->beforeEach(fn () => Http::preventStrayRequests())
    ->in('Feature');
