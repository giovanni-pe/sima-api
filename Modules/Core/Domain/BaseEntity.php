<?php

namespace Modules\Core\Domain;

use Carbon\Carbon;
use Modules\Core\Domain\Interfaces\IBaseEntity;

abstract class BaseEntity implements IBaseEntity
{
    protected ?int $id = null;
    protected ?Carbon $created_at = null;
    protected ?Carbon $updated_at = null;

    public function getId(): ?int        { return $this->id; }
    public function getCreatedAt(): ?Carbon { return $this->created_at; }
    public function getUpdatedAt(): ?Carbon { return $this->updated_at; }
}
