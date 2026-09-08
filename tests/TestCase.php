<?php

namespace Jeffersongoncalves\Semrush\Tests;

use Jeffersongoncalves\Semrush\SemrushServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SemrushServiceProvider::class,
        ];
    }
}
