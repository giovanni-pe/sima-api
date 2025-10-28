<?php

namespace Modules\Sensors\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Core\Http\BaseController;
use Modules\Sensors\Application\Contracts\ISensorsService;
use Modules\Sensors\Http\DTOs\SensorDTO;
use Modules\Sensors\Http\Requests\CreateSensorRequest;
use Modules\Sensors\Http\Requests\UpdateSensorRequest;

class SensorsController extends BaseController
{
    public function __construct(private readonly ISensorsService $service) {}

    public function index(Request $request)
    {
        return $this->respond(fn() =>
            $this->service->paginate(
                perPage: $request->integer('per_page', 15),
                filters: $request->all()
            )
        );
    }

    public function store(CreateSensorRequest $request)
    {
        return $this->respond(
            fn() => $this->service->save(
                SensorDTO::fromCreateRequest($request)->toEntity()
            ),
            'Created',
            201
        );
    }

    public function show(int $id)
    {
        return $this->respond(fn() => $this->service->find($id));
    }

    public function update(UpdateSensorRequest $request, int $id)
    {
        return $this->respond(
            fn() => $this->service->save(
                SensorDTO::fromUpdateRequest($request)->toEntity($id)
            ),
            'Updated'
        );
    }

    public function destroy(int $id)
    {
        return $this->respond(fn() => $this->service->delete($id), 'Deleted');
    }

    public function active()
    {
        return $this->respond(fn() => $this->service->active());
    }
}
