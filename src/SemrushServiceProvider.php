<?php

namespace JeffersonGoncalves\Semrush;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SemrushServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('semrush')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('semrush', fn () => new Semrush);
    }
}
