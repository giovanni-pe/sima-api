<?php

namespace Modules\Core\Domain\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Domain\BaseEntity;

interface IBaseRepository
{
    /**
     * @return BaseEntity[]
     */
    public function all(array $filters = [], array $relations = []): array;

    public function paginate(int $perPage = 15, array $filters = [], array $relations = []): LengthAwarePaginator;

    public function find(int $id, array $relations = []): ?BaseEntity;

    /** Crea o actualiza según si la entidad tiene id (upsert). */
    public function save(BaseEntity $entity): BaseEntity;

    public function delete(int $id): bool;

    public function forceDelete(int $id): bool;

    public function restore(int $id): bool;

    /**
     * @return BaseEntity[]
     */
    public function findAllActive(): array;
}
