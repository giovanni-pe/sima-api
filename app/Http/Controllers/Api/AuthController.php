<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /* ---------------------------------------------------------
     *  LOGIN (sin cache)
     * ---------------------------------------------------------*/
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login'    => 'required|string',   // email o phone
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $loginField = $request->login;
            $user = filter_var($loginField, FILTER_VALIDATE_EMAIL)
                ? User::where('email', $loginField)->first()
                : User::where('phone', $loginField)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'Credenciales incorrectas'], 401);
            }

            if ($user->status !== 'active') {
                return response()->json(['success' => false, 'message' => 'Cuenta suspendida o inactiva'], 403);
            }

            $user->update(['last_activity' => now()]);

            // Passport
            $token = $user->createToken('ChasquiApp')->accessToken;

            // Perfil SIEMPRE fresco (sin cache)
            $profile = $this->makeProfile($user->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'data'    => [
                    'user'  => $profile,
                    'token' => $token,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error en login', 'error' => $e->getMessage()], 500);
        }
    }

    /* ---------------------------------------------------------
     *  PERFIL (siempre desde BD, sin cache)
     * ---------------------------------------------------------*/
    public function profile(Request $request)
    {
        try {
            $user = $request->user()->fresh();
            $profile = $this->makeProfile($user);

            return response()->json(['success' => true, 'data' => $profile]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener perfil', 'error' => $e->getMessage()], 500);
        }
    }

    /* ---------------------------------------------------------
     *  ACTUALIZAR PERFIL (sin invalidaciones de cache)
     * ---------------------------------------------------------*/
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:100',
            'last_name'  => 'sometimes|required|string|max:100',
            'email'      => 'sometimes|nullable|email|unique:users,email,' . $request->user()->id,
            'phone'      => 'sometimes|required|string|max:15|unique:users,phone,' . $request->user()->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $user = $request->user();
            $updates = $request->only(['first_name', 'last_name', 'email', 'phone']);

            if (!empty($updates)) {
                if (isset($updates['first_name']) || isset($updates['last_name'])) {
                    $updates['name'] = ($updates['first_name'] ?? $user->first_name) . ' ' . ($updates['last_name'] ?? $user->last_name);
                }
                $user->update($updates);
            }

            $profile = $this->makeProfile($user->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado',
                'data'    => $profile,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar perfil', 'error' => $e->getMessage()], 500);
        }
    }

    /* ---------------------------------------------------------
     *  Helper: payload consistente del perfil (sin cache)
     * ---------------------------------------------------------*/
    private function makeProfile(User $user): array
    {
        // Si usas relaciones, puedes precargar aquí, ej:
        // $user->load(['department', 'avatarMedia', ...]);

        return [
            'id'                => $user->id,
            'name'              => $user->name,
            'first_name'        => $user->first_name,
            'last_name'         => $user->last_name,
            'email'             => $user->email,
            'phone'             => $user->phone,
            'status'            => $user->status,
            'last_activity'     => $user->last_activity,
            'email_verified_at' => $user->email_verified_at,
            'created_at'        => $user->created_at,
            // Spatie Permission (opcional si lo usas)
            'roles'             => method_exists($user, 'getRoleNames') ? $user->getRoleNames()->values() : [],
            'permissions'       => method_exists($user, 'getAllPermissions') ? $user->getAllPermissions()->pluck('name')->values() : [],
            // 'avatar_url'     => optional($user->avatarMedia)->getUrl(), // ejemplo si usas media library
        ];
    }
}
