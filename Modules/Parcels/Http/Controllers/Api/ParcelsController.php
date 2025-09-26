<?php

namespace Modules\Parcels\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Core\Http\BaseController;
use Modules\Parcels\Application\Contracts\IParcelsService;
use Modules\Parcels\Http\DTOs\ParcelDTO;
use Modules\Parcels\Http\Requests\CreateParcelRequest;
use Modules\Parcels\Http\Requests\UpdateParcelRequest;

class ParcelsController extends BaseController
{
    public function __construct(private readonly IParcelsService $service) {}

    public function index(Request $request)
    {
        return $this->paginated($this->service->paginate(
            perPage: $request->integer('per_page', 15),
            filters: $request->all()
        ));
    }

    public function store(CreateParcelRequest $request)
    {
        return $this->success(
            $this->service->save(ParcelDTO::fromCreateRequest($request)->toEntity()),
            'Created', 201
        );
    }

    public function show(int $id)
    {
        return $this->success($this->service->find($id));
    }

    public function update(UpdateParcelRequest $request, int $id)
    {
        return $this->success(
            $this->service->save(ParcelDTO::fromUpdateRequest($request)->toEntity($id)),
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
