<?php

namespace SabitAhmad\Bkash\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SabitAhmad\Bkash\Bkash
 */
class Bkash extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SabitAhmad\Bkash\Bkash::class;
    }
}
