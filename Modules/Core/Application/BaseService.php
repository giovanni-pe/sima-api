<?php

namespace Modules\Core\Application;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Application\Contracts\IBaseService;
use Modules\Core\Application\Exceptions\ServiceException;
use Modules\Core\Domain\BaseEntity;
use Modules\Core\Domain\Interfaces\IBaseRepository;
use Throwable;

abstract class BaseService implements IBaseService
{
    protected IBaseRepository $repository;

    public function __construct(IBaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /** @return BaseEntity[] */
    public function all(array $filters = []): array
    {
        return $this->wrap(function () use ($filters) {
            return $this->repository->all($filters);
        }, 'all', ['filters' => $filters]);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->wrap(function () use ($perPage, $filters) {
            return $this->repository->paginate($perPage, $filters);
        }, 'paginate', ['perPage' => $perPage, 'filters' => $filters]);
    }

    public function find(int $id): ?BaseEntity
    {
        return $this->wrap(function () use ($id) {
            return $this->repository->find($id);
        }, 'find', ['id' => $id]);
    }

    public function save(BaseEntity $entity): BaseEntity
    {
        return $this->wrap(function () use ($entity) {
            return DB::transaction(function () use ($entity) {
                return $this->repository->save($entity);
            });
        }, 'save', ['entity' => get_class($entity), 'id' => $entity->getId()]);
    }

    public function delete(int $id): bool
    {
        return $this->wrap(function () use ($id) {
            return DB::transaction(function () use ($id) {
                return $this->repository->delete($id);
            });
        }, 'delete', ['id' => $id]);
    }

    public function forceDelete(int $id): bool
    {
        return $this->wrap(function () use ($id) {
            return DB::transaction(function () use ($id) {
                return $this->repository->forceDelete($id);
            });
        }, 'forceDelete', ['id' => $id]);
    }

    public function restore(int $id): bool
    {
        return $this->wrap(function () use ($id) {
            return DB::transaction(function () use ($id) {
                return $this->repository->restore($id);
            });
        }, 'restore', ['id' => $id]);
    }

    /** @return BaseEntity[] */
    public function active(): array
    {
        return $this->wrap(function () {
            return $this->repository->findAllActive();
        }, 'active');
    }

    /**
     * Envuelve la ejecución con try/catch y logging consistente.
     * Lanza ServiceException con la excepción original como "previous".
     */
    protected function wrap(callable $fn, string $op, array $context = [])
    {
        try {
            return $fn();
        } catch (Throwable $e) {
            Log::error('[Service] Operation failed', [
                'service' => static::class,
                'operation' => $op,
                'context' => $context,
                'exception' => [
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ]);

            // Reempaqueta en una excepción de aplicación
            throw new ServiceException(
                message: "Error on {$op} operation",
                code: $e->getCode() ?: 0,
                previous: $e
            );
        }
    }
}
