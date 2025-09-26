<?php

namespace Modules\Parcels\Application;

use Modules\Core\Application\BaseService;
use Modules\Parcels\Application\Contracts\IParcelsService;
use Modules\Parcels\Domain\Interfaces\IParcelsRepository;

class ParcelsService extends BaseService implements IParcelsService
{
    public function __construct( IParcelsRepository $repository)
    {
        parent::__construct($repository);
    }
}
