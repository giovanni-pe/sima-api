<?php

namespace Modules\ControlUnits\Domain\Entities;

use Modules\Core\Domain\BaseEntity;

class ControlUnit extends BaseEntity
{
    public function __construct(
        public ?int $id,
        protected ?\Carbon\Carbon $created_at,
        protected ?\Carbon\Carbon $updated_at,
        public string $serial_code,
        public string $model,
        public string $installed_at,
        public string $status,
        public int $parcel_id,
        public string $mqtt_client_id,
        public string $mqtt_username,
        public string $mqtt_password_enc,
        public string $status_topic,
        public string $lwt_topic,
        public string $last_seen_at,
        public bool $active,
    ) {}
}