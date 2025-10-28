<?php

namespace Modules\Core\Http;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseController extends Controller
{
    protected function success(mixed $data = null, string $message = 'OK', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function error(string $message = 'Error', int $code = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function paginated(LengthAwarePaginator $paginator, string $message = 'OK', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ], $code);
    }

    /**
     * Ejecuta una acción y gestiona respuestas/errores de forma uniforme.
     * Si la acción devuelve un LengthAwarePaginator, responde con formato paginado.
     */
    protected function respond(callable $action, string $okMessage = 'OK', int $okCode = 200): JsonResponse
    {
        try {
            $result = $action();

            if ($result instanceof LengthAwarePaginator) {
                return $this->paginated($result, $okMessage, $okCode);
            }

            return $this->success($result, $okMessage, $okCode);
        }
        catch (ValidationException $e) {
            // Errores de validación (422)
            return $this->error('Errores de validación', 422, $e->errors());
        }
        catch (ModelNotFoundException $e) {
            // Recurso no encontrado (404)
            return $this->error('Recurso no encontrado', 404);
        }
        catch (HttpExceptionInterface $e) {
            // Excepciones HTTP explícitas (usar su código)
            $msg = $e->getMessage() ?: 'Error HTTP';
            return $this->error($msg, $e->getStatusCode());
        }
        catch (QueryException $e) {
            // Errores de base de datos (500) + log con SQL y bindings
            Log::error('Database query exception', [
                'sql'       => $e->getSql(),
                'bindings'  => $e->getBindings(),
                'exception' => $e,
            ]);
            $msg = config('app.debug') ? $e->getMessage() : 'Error de base de datos';
            return $this->error($msg, 500);
        }
        catch (Throwable $e) {
            // Cualquier otro error (500) + log
            Log::error('Unhandled exception', ['exception' => $e]);
            $msg = config('app.debug') ? $e->getMessage() : 'Error interno del servidor';
            return $this->error($msg, 500);
        }
    }
}
