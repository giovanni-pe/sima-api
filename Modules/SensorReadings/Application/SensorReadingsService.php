<?php

namespace Modules\SensorReadings\Application;

use Modules\Core\Application\BaseService;
use Modules\SensorReadings\Application\Contracts\ISensorReadingsService;
use Modules\SensorReadings\Domain\Interfaces\ISensorReadingsRepository;

class SensorReadingsService extends BaseService implements ISensorReadingsService
{
    public function __construct(ISensorReadingsRepository $repository)
    {
        parent::__construct($repository);
    }
}