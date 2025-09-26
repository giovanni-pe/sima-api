<?php

namespace Modules\SensorReadings\Infrastructure\Repositories;

use Modules\Core\Infrastructure\BaseRepository;
use Modules\SensorReadings\Domain\Entities\SensorReading;
use Modules\SensorReadings\Domain\Interfaces\ISensorReadingsRepository;
use Modules\SensorReadings\Infrastructure\Persistence\SensorReadingEloquent;

class SensorReadingsRepository extends BaseRepository implements ISensorReadingsRepository
{
    public function __construct(SensorReadingEloquent $model)
    {
        parent::__construct($model, SensorReading::class);
    }
}