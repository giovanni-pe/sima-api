<?php

namespace Modules\Core\Infrastructure;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Modules\Core\Domain\BaseEntity;
use Modules\Core\Domain\Interfaces\IBaseRepository;
use Modules\Core\Infrastructure\Exceptions\RepositoryException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;

abstract class BaseRepository implements IBaseRepository
{
    protected Model $model;

    /** @var class-string<BaseEntity> */
    protected string $entityClass;

    /**
     * Inyecta Modelo Eloquent (Infra) + clase de Entidad (Dominio).
     * Ej: parent::__construct(new ParcelEloquent(), Parcel::class)
     */
    public function __construct(Model $model, string $entityClass)
    {
        $this->model       = $model;
        $this->entityClass = $entityClass;
    }

    /**
     * @return BaseEntity[]
     * @throws RepositoryException
     */
    public function all(array $filters = [], array $relations = []): array
    {
        return $this->wrap('all', compact('filters', 'relations'), function () use ($filters, $relations) {
            return $this->query($filters, $relations)
                ->get()
                ->map(fn (Model $m) => $this->toEntity($m))
                ->all();
        });
    }

    /**
     * @throws RepositoryException
     */
    public function paginate(int $perPage = 15, array $filters = [], array $relations = []): LengthAwarePaginator
    {
        return $this->wrap('paginate', compact('perPage', 'filters', 'relations'), function () use ($perPage, $filters, $relations) {
            $p = $this->query($filters, $relations)->paginate($perPage);
            $p->getCollection()->transform(fn (Model $m) => $this->toEntity($m));
            return $p;
        });
    }

    /**
     * @throws RepositoryException
     */
    public function find(int $id, array $relations = []): ?BaseEntity
    {
        return $this->wrap('find', compact('id', 'relations'), function () use ($id, $relations) {
            $m = $this->model->newQuery()->with($relations)->find($id);
            return $m ? $this->toEntity($m) : null;
        });
    }

    /**
     * Crea o actualiza según si la entidad trae id (upsert).
     * @throws RepositoryException
     */
    public function save(BaseEntity $entity): BaseEntity
    {
        return $this->wrap('save', ['entity' => get_class($entity), 'id' => $entity->getId()], function () use ($entity) {
            $id = $entity->getId();

            if ($id) {
                $m = $this->model->newQuery()->findOrFail($id);
            } else {
                $m = $this->model->newInstance();
            }

            $attributes = $this->entityToArray($entity);
            $this->fillModel($m, $attributes)->save();

            return $this->toEntity($m->fresh());
        });
    }

    /**
     * @throws RepositoryException
     */
    public function delete(int $id): bool
    {
        return $this->wrap('delete', compact('id'), function () use ($id) {
            $m = $this->model->newQuery()->findOrFail($id);
            return (bool) $m->delete();
        });
    }

    /**
     * @throws RepositoryException
     */
    public function forceDelete(int $id): bool
    {
        return $this->wrap('forceDelete', compact('id'), function () use ($id) {
            if (!$this->usesSoftDeletes()) {
                return $this->delete($id);
            }
            $m = $this->model->newQuery()->withTrashed()->findOrFail($id);
            return (bool) $m->forceDelete();
        });
    }

    /**
     * @throws RepositoryException
     */
    public function restore(int $id): bool
    {
        return $this->wrap('restore', compact('id'), function () use ($id) {
            if (!$this->usesSoftDeletes()) {
                return false;
            }
            $m = $this->model->newQuery()->withTrashed()->findOrFail($id);
            return (bool) $m->restore();
        });
    }

    /**
     * @return BaseEntity[]
     * @throws RepositoryException
     */
    public function findAllActive(): array
    {
        return $this->wrap('findAllActive', [], function () {
            return $this->model->newQuery()
                ->where('active', true)
                ->get()
                ->map(fn (Model $m) => $this->toEntity($m))
                ->all();
        });
    }

    /** -------------------- Mapeo automático Model/array ⇄ Entity -------------------- */

    /**
     * @throws RepositoryException
     */
    protected function toEntity(Model|array $source): BaseEntity
    {
        return $this->wrap('toEntity', ['sourceType' => is_array($source) ? 'array' : get_class($source)], function () use ($source) {
            $data = $source instanceof Model ? $this->modelToArray($source) : $source;
            return $this->arrayToEntity($data);
        });
    }

    /**
     * @throws RepositoryException
     */
    protected function arrayToEntity(array $data): BaseEntity
    {
        return $this->wrap('arrayToEntity', ['entityClass' => $this->entityClass], function () use ($data) {
            $rc   = new ReflectionClass($this->entityClass);
            $ctor = $rc->getConstructor();

            if (!$ctor) {
                return $rc->newInstance();
            }

            $bag  = $this->normalizedBag($data);
            $args = [];

            foreach ($ctor->getParameters() as $param) {
                $args[] = $this->valueForParam($param, $bag);
            }

            return $rc->newInstanceArgs($args);
        });
    }

    /** Convierte Entidad → array de atributos (snake_case) para fill() */
    protected function entityToArray(BaseEntity $entity): array
    {
        $rc = new ReflectionClass($entity);
        $props = $rc->getProperties();

        $out = [];
        foreach ($props as $p) {
            $p->setAccessible(true);
            $name  = $p->getName();

            // omitir id y timestamps
            if (in_array($name, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }

            $value = $p->getValue($entity);

            // Carbon → string
            if ($value instanceof Carbon) {
                $value = $value->toDateTimeString();
            }

            $out[$this->toSnake($name)] = $value;
        }

        return $out;
    }

    protected function modelToArray(Model $m): array
    {
        $arr = $m->toArray();
        $arr['id']         = $m->getKey();
        $arr['created_at'] = $m->getAttribute('created_at');
        $arr['updated_at'] = $m->getAttribute('updated_at');
        return $arr;
    }

    protected function normalizedBag(array $data): array
    {
        $bag = [];
        foreach ($data as $k => $v) {
            $bag[$k] = $v;
            $snake   = $this->toSnake($k);
            $camel   = $this->toCamel($k);
            $flat    = strtolower(str_replace(['_', '-'], '', $k));
            $bag[$snake] = $v;
            $bag[$camel] = $v;
            $bag[$flat]  = $v;
        }
        return $bag;
    }

    protected function valueForParam(ReflectionParameter $p, array $bag): mixed
    {
        $name = $p->getName();
        $keys = [
            $name,
            $this->toSnake($name),
            $this->toCamel($name),
            strtolower(str_replace(['_', '-'], '', $name)),
        ];

        $found = null;
        foreach ($keys as $key) {
            if (array_key_exists($key, $bag)) {
                $found = $bag[$key];
                break;
            }
        }

        $type = $p->getType();
        if ($type instanceof ReflectionNamedType) {
            $tn = $type->getName();

            if ($tn === Carbon::class) {
                if ($found instanceof Carbon) return $found;
                return $found ? Carbon::parse($found) : ($p->allowsNull() ? null : Carbon::now());
            }

            if (in_array($tn, ['int', 'float', 'string', 'bool'], true)) {
                if ($found === null) {
                    if ($p->allowsNull()) return null;
                    return match ($tn) {
                        'int' => 0,
                        'float' => 0.0,
                        'string' => '',
                        'bool' => false,
                    };
                }
                return match ($tn) {
                    'int' => (int) $found,
                    'float' => (float) $found,
                    'string' => (string) $found,
                    'bool' => (bool) $found,
                };
            }
        }

        if ($found !== null) return $found;
        if ($p->isDefaultValueAvailable()) return $p->getDefaultValue();
        if ($p->allowsNull()) return null;
        return null;
    }

    /** Query con filtros comunes (sobreescribe si necesitas) */
    protected function query(array $filters = [], array $relations = []): Builder
    {
        $q = $this->model->newQuery()->with($relations);

        if (array_key_exists('active', $filters)) {
            $q->where('active', (bool) $filters['active']);
        }

        if (!empty($filters['search']) && !empty($filters['search_fields']) && is_array($filters['search_fields'])) {
            $term = (string) $filters['search'];
            $q->where(function ($sub) use ($filters, $term) {
                foreach ($filters['search_fields'] as $field) {
                    $sub->orWhere($field, 'LIKE', "%{$term}%");
                }
            });
        }

        if (!empty($filters['sort_by'])) {
            $dir = !empty($filters['sort_dir']) && in_array(strtolower($filters['sort_dir']), ['asc','desc'], true)
                ? strtolower($filters['sort_dir']) : 'asc';
            $q->orderBy($filters['sort_by'], $dir);
        }

        return $q;
    }

    protected function fillModel(Model $model, array $data): Model
    {
        $model->fill($data);
        return $model;
    }

    protected function usesSoftDeletes(): bool
    {
        return in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', class_uses_recursive($this->model));
    }

    protected function toSnake(string $name): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($name)));
    }

    protected function toCamel(string $name): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name))));
    }

    /**
     * Envuelve la ejecución con try/catch y logging; re-lanza RepositoryException.
     * @template T
     * @param  string   $op
     * @param  array    $context
     * @param  callable():mixed $fn
     * @return mixed
     * @throws RepositoryException
     */
    protected function wrap(string $op, array $context, callable $fn)
    {
        try {
            return $fn();
        } catch (Throwable $e) {
            Log::error('[Repository] Operation failed', [
                'repository' => static::class,
                'operation'  => $op,
                'context'    => $context,
                'exception'  => [
                    'type'    => get_class($e),
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ],
            ]);

            throw new RepositoryException("Error on {$op} operation", (int)($e->getCode() ?: 0), $e);
        }
    }
}
