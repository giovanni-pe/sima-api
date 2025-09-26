<?php

namespace Modules\Core\Domain\Interfaces;

use Carbon\Carbon;

interface IBaseEntity
{
    public function getId(): ?int;
    public function getCreatedAt(): ?Carbon;
    public function getUpdatedAt(): ?Carbon;
}
