<?php

namespace Modules\ControlUnits\Infrastructure\Repositories;

use Modules\Core\Infrastructure\BaseRepository;
use Modules\ControlUnits\Domain\Entities\ControlUnit;
use Modules\ControlUnits\Domain\Interfaces\IControlUnitsRepository;
use Modules\ControlUnits\Infrastructure\Persistence\ControlUnitEloquent;

class ControlUnitsRepository extends BaseRepository implements IControlUnitsRepository
{
    public function __construct(ControlUnitEloquent $model)
    {
        parent::__construct($model, ControlUnit::class);
    }
}