<?php

namespace SabitAhmad\Bkash;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use SabitAhmad\Bkash\Commands\BkashCommand;

class BkashServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-bkash')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_bkash_payments');
    }

   public function packageRegistered(): void
   {
       $this->app->singleton(Bkash::class, function () {
           return new Bkash();
       });
   }
}
