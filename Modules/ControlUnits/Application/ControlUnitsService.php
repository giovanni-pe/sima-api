<?php

namespace Modules\ControlUnits\Application;

use Modules\Core\Application\BaseService;
use Modules\ControlUnits\Application\Contracts\IControlUnitsService;
use Modules\ControlUnits\Domain\Interfaces\IControlUnitsRepository;

class ControlUnitsService extends BaseService implements IControlUnitsService
{
    public function __construct(IControlUnitsRepository $repository)
    {
        parent::__construct($repository);
    }
}