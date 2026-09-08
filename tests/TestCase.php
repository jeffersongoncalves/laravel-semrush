<?php

namespace JeffersonGoncalves\Semrush\Tests;

use JeffersonGoncalves\Semrush\SemrushServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SemrushServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('semrush.key', 'fake-key');
    }
}
