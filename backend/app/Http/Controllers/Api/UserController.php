<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Listar usuarios con paginación y búsqueda
     * Búsqueda por: usuario, nombre, email
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $users = User::query()
            ->with(['roles', 'sex', 'empresa'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('usuario', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Crear nuevo usuario
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = [
            'usuario' => $request->usuario,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sexo_id' => $request->sexo_id,
            'telefono' => $request->telefono,
            'chatid' => $request->chatid,
            'empresa_id' => $request->empresa_id,
            'activo' => $request->input('activo', true),
        ];

        // Manejar upload de avatar
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $extension = $avatar->getClientOriginalExtension();

            // Guardar con el nombre del usuario + microtime para evitar caché
            $filename = $request->usuario . '.' . microtime(true) . '.' . $extension;
            $path = $avatar->storeAs('avatars', $filename, 'public');
            $data['avatar'] = $path;
        }

        $user = User::create($data);

        // Asignar roles si se proporcionan
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        $user->load(['roles', 'sex', 'empresa']);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => new UserResource($user),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un usuario específico
     */
    public function show(int $id): JsonResponse
    {
        $user = User::with(['roles', 'sex', 'empresa'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Actualizar usuario
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $data = [
            'usuario' => $request->usuario,
            'name' => $request->name,
            'email' => $request->email,
            'sexo_id' => $request->sexo_id,
            'telefono' => $request->telefono,
            'chatid' => $request->chatid,
            'empresa_id' => $request->empresa_id,
            'activo' => $request->activo,
        ];

        // Manejar upload de avatar
        if ($request->hasFile('avatar')) {
            // Eliminar avatar anterior si existe
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $avatar = $request->file('avatar');
            $extension = $avatar->getClientOriginalExtension();

            // Guardar con el nombre del usuario + microtime para evitar caché
            $filename = $request->usuario . '.' . microtime(true) . '.' . $extension;
            $path = $avatar->storeAs('avatars', $filename, 'public');
            $data['avatar'] = $path;
        }

        // Solo actualizar contraseña si se proporciona
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Actualizar roles si se proporcionan
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        $user->load(['roles', 'sex', 'empresa']);

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Eliminar un usuario
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevenir eliminar al usuario autenticado
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminarte a ti mismo',
            ], Response::HTTP_FORBIDDEN);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples usuarios (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:users,id',
        ]);

        $ids = $request->input('ids');

        // Prevenir eliminar al usuario autenticado
        if (in_array(auth()->id(), $ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminarte a ti mismo',
            ], Response::HTTP_FORBIDDEN);
        }

        $deleted = User::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} usuario(s) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }

    /**
     * Eliminar avatar del usuario
     */
    public function deleteAvatar(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Verificar que el usuario solo puede eliminar su propio avatar
        if ($user->id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este avatar',
            ], Response::HTTP_FORBIDDEN);
        }

        // Eliminar archivo de avatar si existe
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Actualizar registro en la base de datos
        $user->update(['avatar' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar eliminado exitosamente',
        ]);
    }

    /**
     * Cambiar contraseña del usuario
     */
    public function updatePassword(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Verificar que el usuario solo puede cambiar su propia contraseña
        if ($user->id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para cambiar esta contraseña',
            ], Response::HTTP_FORBIDDEN);
        }

        // Validar datos
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'La contraseña actual es requerida',
            'password.required' => 'La nueva contraseña es requerida',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        // Verificar que la contraseña actual es correcta
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Actualizar contraseña
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente',
        ]);
    }
}
