<?php

namespace Modules\ControlUnits\Http\DTOs;

use Modules\ControlUnits\Domain\Entities\ControlUnit;
use Modules\ControlUnits\Http\Requests\CreateControlUnitRequest;
use Modules\ControlUnits\Http\Requests\UpdateControlUnitRequest;

final class ControlUnitDTO
{
    public function __construct(
            public string $serial_code,
            public string $model,
            public ?string $installed_at,
            public string $status,
            public int $parcel_id,
            public string $mqtt_client_id,
            public ?string $mqtt_username,
            public ?string $mqtt_password_enc,
            public ?string $status_topic,
            public ?string $lwt_topic,
            public ?string $last_seen_at,
            public bool $active,
    ) {}


    public static function fromCreateRequest(CreateControlUnitRequest $r): self
    {
        $v = $r->validated();
        return new self(
            serial_code: ($v['serial_code'] ?? ''),
            model: ($v['model'] ?? ''),
            installed_at: ($v['installed_at'] ?? null),
            status: ($v['status'] ?? ''),
            parcel_id: isset($v['parcel_id']) ? (int)$v['parcel_id'] : 0,
            mqtt_client_id: ($v['mqtt_client_id'] ?? ''),
            mqtt_username: ($v['mqtt_username'] ?? null),
            mqtt_password_enc: ($v['mqtt_password_enc'] ?? null),
            status_topic: ($v['status_topic'] ?? null),
            lwt_topic: ($v['lwt_topic'] ?? null),
            last_seen_at: ($v['last_seen_at'] ?? null),
            active: isset($v['active']) ? (bool)$v['active'] : false,
        );
    }

    public static function fromUpdateRequest(UpdateControlUnitRequest $r): self
    {
        $v = $r->validated();
        return new self(
            serial_code: ($v['serial_code'] ?? ''),
            model: ($v['model'] ?? ''),
            installed_at: ($v['installed_at'] ?? null),
            status: ($v['status'] ?? ''),
            parcel_id: isset($v['parcel_id']) ? (int)$v['parcel_id'] : 0,
            mqtt_client_id: ($v['mqtt_client_id'] ?? ''),
            mqtt_username: ($v['mqtt_username'] ?? null),
            mqtt_password_enc: ($v['mqtt_password_enc'] ?? null),
            status_topic: ($v['status_topic'] ?? null),
            lwt_topic: ($v['lwt_topic'] ?? null),
            last_seen_at: ($v['last_seen_at'] ?? null),
            active: isset($v['active']) ? (bool)$v['active'] : false,
        );
    }

    public function toEntity(?int $id = null): ControlUnit
    {
        return new ControlUnit(
            id: $id,
            serial_code: $this->serial_code,
            model: $this->model,
            installed_at: $this->installed_at,
            status: $this->status,
            parcel_id: $this->parcel_id,
            mqtt_client_id: $this->mqtt_client_id,
            mqtt_username: $this->mqtt_username,
            mqtt_password_enc: $this->mqtt_password_enc,
            status_topic: $this->status_topic,
            lwt_topic: $this->lwt_topic,
            last_seen_at: $this->last_seen_at,
            active: $this->active,

            created_at: null,
            updated_at: null,
        );
    }
}