<?php

namespace Modules\Core\Application\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Domain\BaseEntity;

interface IBaseService
{
    /** @return BaseEntity[] */
    public function all(array $filters = []): array;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?BaseEntity;

    /** Guarda (create/update) según si la entidad trae id o no. */
    public function save(BaseEntity $entity): BaseEntity;

    public function delete(int $id): bool;

    public function forceDelete(int $id): bool;

    public function restore(int $id): bool;

    /** @return BaseEntity[] */
    public function active(): array;
}
