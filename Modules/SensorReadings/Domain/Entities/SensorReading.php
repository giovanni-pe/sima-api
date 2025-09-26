<?php

namespace Modules\SensorReadings\Domain\Entities;

use Modules\Core\Domain\BaseEntity;

class SensorReading extends BaseEntity
{
    public function __construct(
        protected ?int $id,
        protected ?\Carbon\Carbon $created_at,
        protected ?\Carbon\Carbon $updated_at,
        public int $sensor_id,
        public string $timestamp,
        public float $value,
        public string $2),
        public ?string $unit,
    ) {}
}