<?php

namespace Modules\SensorReadings\Http\DTOs;

use Modules\SensorReadings\Domain\Entities\SensorReading;
use Modules\SensorReadings\Http\Requests\CreateSensorReadingRequest;
use Modules\SensorReadings\Http\Requests\UpdateSensorReadingRequest;

final class SensorReadingDTO
{
    public function __construct(
            public int $sensor_id,
            public string $timestamp,
            public float $value,
            public ?string $unit,
    ) {}

    public static function fromCreateRequest(CreateSensorReadingRequest $r): self
    {
        $v = $r->validated();
        return new self(
            sensor_id: isset($v['sensor_id']) ? (int)$v['sensor_id'] : 0,
            timestamp: ($v['timestamp'] ?? ''),
            value: isset($v['value']) ? (float)$v['value'] : 0.0,
            unit: ($v['unit'] ?? null),
        );
    }

    public static function fromUpdateRequest(UpdateSensorReadingRequest $r): self
    {
        $v = $r->validated();
        return new self(
            sensor_id: isset($v['sensor_id']) ? (int)$v['sensor_id'] : 0,
            timestamp: ($v['timestamp'] ?? ''),
            value: isset($v['value']) ? (float)$v['value'] : 0.0,
            unit: ($v['unit'] ?? null),
        );
    }

    public function toEntity(?int $id = null): SensorReading
    {
        return new SensorReading(
            id: $id,
            sensor_id: $this->sensor_id,
            timestamp: $this->timestamp,
            value: $this->value,
            unit: $this->unit,
            created_at: null,
            updated_at: null,
        );
    }
}