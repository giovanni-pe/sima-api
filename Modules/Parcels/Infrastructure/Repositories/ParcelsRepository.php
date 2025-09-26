<?php

namespace Modules\Parcels\Infrastructure\Repositories;

use Modules\Core\Infrastructure\BaseRepository;
use Modules\Parcels\Domain\Entities\Parcel;
use Modules\Parcels\Domain\Interfaces\IParcelsRepository;
use Modules\Parcels\Infrastructure\Persistence\ParcelEloquent;

class ParcelsRepository extends BaseRepository implements IParcelsRepository
{
    public function __construct(ParcelEloquent $model)
    {
        parent::__construct($model, Parcel::class);
    }
}
