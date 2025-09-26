<?php

namespace Modules\ControlUnits\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Core\Http\BaseController;
use Modules\ControlUnits\Application\Contracts\IControlUnitsService;
use Modules\ControlUnits\Http\DTOs\ControlUnitDTO;
use Modules\ControlUnits\Http\Requests\CreateControlUnitRequest;
use Modules\ControlUnits\Http\Requests\UpdateControlUnitRequest;

class ControlUnitsController extends BaseController
{
    public function __construct(private readonly IControlUnitsService $service) {}

    public function index(Request $request)
    {
        return $this->paginated($this->service->paginate(
            perPage: $request->integer('per_page', 15),
            filters: $request->all()
        ));
    }

    public function store(CreateControlUnitRequest $request)
    {
        return $this->success(
            $this->service->save(ControlUnitDTO::fromCreateRequest($request)->toEntity()),
            'Created', 201
        );
    }

    public function show(int $id)
    {
        return $this->success($this->service->find($id));
    }

    public function update(UpdateControlUnitRequest $request, int $id)
    {
        return $this->success(
            $this->service->save(ControlUnitDTO::fromUpdateRequest($request)->toEntity($id)),
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