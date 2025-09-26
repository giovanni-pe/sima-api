<?php

namespace Modules\SensorReadings\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Core\Http\BaseController;
use Modules\SensorReadings\Application\Contracts\ISensorReadingsService;
use Modules\SensorReadings\Http\DTOs\SensorReadingDTO;
use Modules\SensorReadings\Http\Requests\CreateSensorReadingRequest;
use Modules\SensorReadings\Http\Requests\UpdateSensorReadingRequest;

class SensorReadingsController extends BaseController
{
    public function __construct(private readonly ISensorReadingsService $service) {}

    public function index(Request $request)
    {
        return $this->paginated($this->service->paginate(
            perPage: $request->integer('per_page', 15),
            filters: $request->all()
        ));
    }

    public function store(CreateSensorReadingRequest $request)
    {
        return $this->success(
            $this->service->save(SensorReadingDTO::fromCreateRequest($request)->toEntity()),
            'Created', 201
        );
    }

    public function show(int $id)
    {
        return $this->success($this->service->find($id));
    }

    public function update(UpdateSensorReadingRequest $request, int $id)
    {
        return $this->success(
            $this->service->save(SensorReadingDTO::fromUpdateRequest($request)->toEntity($id)),
            'Updated'
        );
    }

    public function destroy(int $id)
    {
        return $this->success($this->service->delete($id), 'Deleted');
    }

    public function active()
    {
        return $this->success($this->service->active());
    }
}