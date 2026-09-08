<?php

namespace Jeffersongoncalves\Semrush\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jeffersongoncalves\Semrush\Semrush
 */
class Semrush extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-semrush';
    }
}
