<?php

namespace Modules\Sensors\Infrastructure\Repositories;

use Modules\Core\Infrastructure\BaseRepository;
use Modules\Sensors\Domain\Entities\Sensor;
use Modules\Sensors\Domain\Interfaces\ISensorsRepository;
use Modules\Sensors\Infrastructure\Persistence\SensorEloquent;

class SensorsRepository extends BaseRepository implements ISensorsRepository
{
    public function __construct(SensorEloquent $model)
    {
        parent::__construct($model, Sensor::class);
    }
}