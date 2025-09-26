<?php

namespace Modules\Sensors\Application;

use Modules\Core\Application\BaseService;
use Modules\Sensors\Application\Contracts\ISensorsService;
use Modules\Sensors\Domain\Interfaces\ISensorsRepository;

class SensorsService extends BaseService implements ISensorsService
{
    public function __construct(ISensorsRepository $repository)
    {
        parent::__construct($repository);
    }
}