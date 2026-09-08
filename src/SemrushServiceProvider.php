<?php

namespace Jeffersongoncalves\Semrush;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SemrushServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-semrush')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
