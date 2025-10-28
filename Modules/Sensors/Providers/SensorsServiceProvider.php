<?php

namespace Modules\Sensors\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Sensors\Application\Contracts\ISensorsService;
use Modules\Sensors\Application\SensorsService;
use Modules\Sensors\Domain\Interfaces\ISensorsRepository;
use Modules\Sensors\Infrastructure\Persistence\SensorEloquent;
use Modules\Sensors\Infrastructure\Repositories\SensorsRepository;
use Nwidart\Modules\Traits\PathNamespace;

class SensorsServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Sensors';

    protected string $nameLower = 'sensors';
    public function register(): void
    {
        $this->app->bind(ISensorsRepository::class, fn() =>
        new SensorsRepository(new SensorEloquent()));
        $this->app->bind(ISensorsService::class, fn($app) =>
        new SensorsService($app->make(ISensorsRepository::class)));
    }
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));
    }
}
