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
    ) {}


    public static function fromCreateRequest(CreateSensorRequest $r): self
    {
        $v = $r->validated();
        return new self(
            name: ($v['name'] ?? ''),
            type: ($v['type'] ?? ''),
            active: isset($v['active']) ? (bool)$v['active'] : false,
        );
    }

    public static function fromUpdateRequest(UpdateSensorRequest $r): self
    {
        $v = $r->validated();
        return new self(
            name: ($v['name'] ?? ''),
            type: ($v['type'] ?? ''),
            active: isset($v['active']) ? (bool)$v['active'] : false,
        );
    }

    public function toEntity(?int $id = null): Sensor
    {
        return new Sensor(
            id: $id,
            name: $this->name,
            type: $this->type,
            active: $this->active,

            created_at: null,
            updated_at: null,
        );
    }
}