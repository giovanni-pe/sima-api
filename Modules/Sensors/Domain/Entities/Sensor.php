<?php

namespace Modules\Sensors\Domain\Entities;

use Modules\Core\Domain\BaseEntity;

class Sensor extends BaseEntity
{
    public function __construct(
        protected ?int $id,
        protected ?\Carbon\Carbon $created_at,
        protected ?\Carbon\Carbon $updated_at,
        public string $name,
        public string $type,
        public int $control_unit_id,
        public bool $active,
    ) {}
}