<?php

namespace Modules\Sensors\Http\DTOs;

use Modules\Sensors\Domain\Entities\Sensor;
use Modules\Sensors\Http\Requests\CreateSensorRequest;
use Modules\Sensors\Http\Requests\UpdateSensorRequest;

final class SensorDTO
{
    public function __construct(
            public string $name,
            public string $type,
            public bool $active,
            public ?int $control_unit_id,
    ) {}


    public static function fromCreateRequest(CreateSensorRequest $r): self
    {
        $v = $r->validated();
        return new self(
            name: ($v['name'] ?? ''),
            type: ($v['type'] ?? ''),
            active: isset($v['active']) ? (bool)$v['active'] : false,
            control_unit_id: isset($v['control_unit_id']) ? (int)$v['control_unit_id'] : null,
        );
    }

    public static function fromUpdateRequest(UpdateSensorRequest $r): self
    {
        $v = $r->validated();
        return new self(
            name: ($v['name'] ?? ''),
            type: ($v['type'] ?? ''),
            active: isset($v['active']) ? (bool)$v['active'] : false,
            control_unit_id: isset($v['control_unit_id']) ? (int)$v['control_unit_id'] : null,
        );
    }

    public function toEntity(?int $id = null): Sensor
    {
        return new Sensor(
            id: $id,
            name: $this->name,
            type: $this->type,
            active: $this->active,
            control_unit_id: $this->control_unit_id,
            created_at: null,
            updated_at: null,
        );
    }
}