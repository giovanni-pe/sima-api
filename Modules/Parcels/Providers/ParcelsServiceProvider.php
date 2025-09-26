<?php

namespace Modules\Parcels\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Parcels\Application\Contracts\IParcelsService;
use Modules\Parcels\Application\ParcelsService;
use Modules\Parcels\Domain\Interfaces\IParcelsRepository;
use Modules\Parcels\Infrastructure\Persistence\ParcelEloquent;
use Modules\Parcels\Infrastructure\Repositories\ParcelsRepository;

class ParcelsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IParcelsRepository::class, fn () =>
            new ParcelsRepository(new ParcelEloquent())
        );

        $this->app->bind(IParcelsService::class, fn ($app) =>
            new ParcelsService($app->make(IParcelsRepository::class))
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Parcels', 'Database/Migrations'));
    }
}
