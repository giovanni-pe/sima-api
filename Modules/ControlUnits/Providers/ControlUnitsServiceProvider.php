<?php

namespace Modules\ControlUnits\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ControlUnits\Application\Contracts\IControlUnitsService;
use Modules\ControlUnits\Application\ControlUnitsService;
use Modules\ControlUnits\Domain\Interfaces\IControlUnitsRepository;
use Modules\ControlUnits\Infrastructure\Persistence\ControlUnitEloquent;
use Modules\ControlUnits\Infrastructure\Repositories\ControlUnitsRepository;

class ControlUnitsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IControlUnitsRepository::class, fn () =>
            new ControlUnitsRepository(new ControlUnitEloquent())
        );

        $this->app->bind(IControlUnitsService::class, fn ($app) =>
            new ControlUnitsService($app->make(IControlUnitsRepository::class))
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('ControlUnits', 'Database/Migrations'));
    }
}
