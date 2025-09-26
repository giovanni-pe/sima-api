<?php

namespace Modules\Parcels\Domain\Entities;

use Carbon\Carbon;
use Modules\Core\Domain\BaseEntity;

class Parcel extends BaseEntity
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $location,
        public float $area_m2,
        public ?int $user_id,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $crop_type,
        public bool $active,
        protected ?Carbon $created_at,
        protected ?Carbon $updated_at,
    ) {}
}
