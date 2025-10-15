<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Login con usuario O email (DUAL)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // La validación ya fue manejada por LoginRequest

        // Buscar usuario por email O por usuario
        $user = null;

        if ($request->has('email')) {
            $user = User::where('email', $request->email)->first();
        } elseif ($request->has('usuario')) {
            $user = User::where('usuario', $request->usuario)->first();
        }

        // Verificar si el usuario existe y la contraseña es correcta
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Las credenciales son incorrectas.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Verificar si el usuario está activo
        if (!$user->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta está suspendida. Razón: ' . ($user->razon_suspendida ?? 'No especificada')
            ], Response::HTTP_FORBIDDEN);
        }

        // Generar token JWT
        $token = auth('api')->login($user);

        return $this->respondWithToken($token, $user);
    }

    /**
     * Registrar un nuevo usuario
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // La validación ya fue manejada por RegisterRequest

        // Crear usuario
        $user = User::create([
            'usuario' => $request->usuario,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sexo_id' => $request->sexo_id,
            'telefono_id' => $request->telefono_id,
            'chatid_id' => $request->chatid_id,
            'empresa_id' => $request->empresa_id,
            'activo' => true,
        ]);

        // Asignar rol por defecto "Usuario"
        $user->assignRole('Usuario');

        // Generar token
        $token = auth('api')->login($user);

        return $this->respondWithToken($token, $user, Response::HTTP_CREATED);
    }

    /**
     * Obtener el usuario autenticado
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        // Cargar relaciones
        $user->load(['sex', 'telefono', 'chatid', 'empresa', 'roles']);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Cerrar sesión (invalidar token)
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }

    /**
     * Refrescar el token
     */
    public function refresh(): JsonResponse
    {
        $token = auth('api')->refresh();
        $user = auth('api')->user();

        return $this->respondWithToken($token, $user);
    }

    /**
     * Obtener la estructura del token.
     */
    protected function respondWithToken(string $token, User $user, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        // Cargar roles si no están cargados
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'usuario' => $user->usuario,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'guard_name' => $role->guard_name,
                    ];
                }),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]
        ], $statusCode);
    }
}
