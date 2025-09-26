<?php

namespace Modules\Parcels\Http\DTOs;

use Modules\Parcels\Domain\Entities\Parcel;
use Modules\Parcels\Http\Requests\CreateParcelRequest;
use Modules\Parcels\Http\Requests\UpdateParcelRequest;

final class ParcelDTO
{
    public function __construct(
        public string $name,
        public ?string $location,
        public float $area_m2,
        public ?int $user_id,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $crop_type,
        public bool $active = true,
    ) {}

    public static function fromCreateRequest(CreateParcelRequest $r): self
    {
        $v = $r->validated();
        return new self(
            name:       $v['name'],
            location:   $v['location'] ?? null,
            area_m2:    (float)$v['area_m2'],
            user_id:    $v['user_id'] ?? null,
            latitude:   isset($v['latitude']) ? (float)$v['latitude'] : null,
            longitude:  isset($v['longitude']) ? (float)$v['longitude'] : null,
            crop_type:  $v['crop_type'] ?? null,
            active:     (bool)($v['active'] ?? true),
        );
    }

    public static function fromUpdateRequest(UpdateParcelRequest $r): self
    {
        $v = $r->validated();
        return new self(
            name:       $v['name'] ?? '',
            location:   $v['location'] ?? null,
            area_m2:    isset($v['area_m2']) ? (float)$v['area_m2'] : 0.0,
            user_id:    $v['user_id'] ?? null,
            latitude:   isset($v['latitude']) ? (float)$v['latitude'] : null,
            longitude:  isset($v['longitude']) ? (float)$v['longitude'] : null,
            crop_type:  $v['crop_type'] ?? null,
            active:     (bool)($v['active'] ?? true),
        );
    }

    public function toEntity(?int $id = null): Parcel
    {
        return new Parcel(
            id: $id,
            name: $this->name,
            location: $this->location,
            area_m2: $this->area_m2,
            user_id: $this->user_id,
            latitude: $this->latitude,
            longitude: $this->longitude,
            crop_type: $this->crop_type,
            active: $this->active,
            created_at: null,
            updated_at: null,
        );
    }
}
