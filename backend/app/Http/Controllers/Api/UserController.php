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
use Illuminate\Validation\Rules\Password;
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
            ->forCurrentUser() // Multi-tenancy: Admin solo ve usuarios de su empresa
            ->with(['roles', 'gender', 'phone', 'additionalPhones', 'chatidPrimary', 'empresa'])
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
        $user = new User();

        $data = [
            'usuario' => $request->usuario,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender_id' => $request->gender_id,
            // Multi-tenancy: Admin solo puede crear usuarios para su empresa
            'empresa_id' => $user->getEmpresaIdForCreate($request->empresa_id),
            'activo' => $request->input('activo', true),
        ];

        // Manejar upload de avatar
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $extension = $avatar->getClientOriginalExtension();

            // Generar nombre profesional para el archivo
            $usuarioSlug = $this->sanitizeFilename($request->usuario);
            $timestamp = str_replace('.', '', microtime(true));
            $filename = "{$usuarioSlug}_avatar_{$timestamp}.{$extension}";

            $path = $avatar->storeAs('avatars', $filename, 'public');
            $data['avatar'] = $path;
        }

        $user = User::create($data);

        // Asignar roles si se proporcionan
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        // Manejar teléfonos: el primero como teléfono principal, el resto como adicionales
        if ($request->has('phones') && is_array($request->phones)) {
            $validPhones = array_filter($request->phones, function ($phoneData) {
                return !empty($phoneData['telefono']);
            });

            if (!empty($validPhones)) {
                // Crear el primer teléfono como principal
                $firstPhone = Phone::create([
                    'telefono' => array_values($validPhones)[0]['telefono'],
                ]);
                $user->update(['phone_id' => $firstPhone->id]);

                // Crear los teléfonos adicionales con relación polimórfica
                $additionalPhones = array_slice(array_values($validPhones), 1);
                foreach ($additionalPhones as $phoneData) {
                    $user->additionalPhones()->create([
                        'telefono' => $phoneData['telefono'],
                    ]);
                }
            }
        }

        // Manejar chat ID de Telegram: guardar como principal
        if ($request->filled('chatid')) {
            $chatid = Chatid::create([
                'idtelegram' => $request->chatid,
            ]);
            $user->update(['chatid_id' => $chatid->id]);
        }

        $user->load(['roles', 'gender', 'phone', 'additionalPhones', 'chatidPrimary', 'empresa']);

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
        $user = User::with(['roles', 'gender', 'phone', 'additionalPhones', 'chatidPrimary', 'empresa'])
            ->findOrFail($id);

        // Multi-tenancy: Validar acceso a la empresa
        $user->validateEmpresaAccess($user->empresa_id);

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

        // Multi-tenancy: Validar acceso a la empresa
        $user->validateEmpresaAccess($user->empresa_id);

        $data = [
            'usuario' => $request->usuario,
            'name' => $request->name,
            'email' => $request->email,
            'gender_id' => $request->gender_id,
            // Multi-tenancy: Admin no puede cambiar empresa_id del usuario
            'empresa_id' => $user->getEmpresaIdForCreate($request->empresa_id),
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

            // Generar nombre profesional para el archivo
            $usuarioSlug = $this->sanitizeFilename($request->usuario);
            $timestamp = str_replace('.', '', microtime(true));
            $filename = "{$usuarioSlug}_avatar_{$timestamp}.{$extension}";

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

        // Actualizar teléfonos: el primero como principal, el resto como adicionales
        if ($request->has('phones') && is_array($request->phones)) {
            $validPhones = array_filter($request->phones, function ($phoneData) {
                return !empty($phoneData['telefono']);
            });

            if (!empty($validPhones)) {
                // Actualizar o crear el teléfono principal
                $firstPhoneData = array_values($validPhones)[0];
                if ($user->phone_id) {
                    // Actualizar teléfono existente
                    $user->phone->update(['telefono' => $firstPhoneData['telefono']]);
                } else {
                    // Crear nuevo teléfono principal
                    $firstPhone = Phone::create([
                        'telefono' => $firstPhoneData['telefono'],
                    ]);
                    $user->update(['phone_id' => $firstPhone->id]);
                }

                // Eliminar teléfonos adicionales existentes
                $user->additionalPhones()->delete();

                // Crear nuevos teléfonos adicionales
                $additionalPhones = array_slice(array_values($validPhones), 1);
                foreach ($additionalPhones as $phoneData) {
                    $user->additionalPhones()->create([
                        'telefono' => $phoneData['telefono'],
                    ]);
                }
            } else {
                // Si no hay teléfonos válidos, eliminar relaciones
                if ($user->phone_id) {
                    $user->phone->delete();
                    $user->update(['phone_id' => null]);
                }
                $user->additionalPhones()->delete();
            }
        }

        // Actualizar chat ID de Telegram: guardar como principal
        if ($request->filled('chatid')) {
            if ($user->chatid_id) {
                // Actualizar chatid existente
                $user->chatidPrimary->update(['idtelegram' => $request->chatid]);
            } else {
                // Crear nuevo chatid principal
                $chatid = Chatid::create([
                    'idtelegram' => $request->chatid,
                ]);
                $user->update(['chatid_id' => $chatid->id]);
            }
        } elseif ($request->has('chatid') && empty($request->chatid)) {
            // Si se envía vacío, eliminar chat ID principal
            if ($user->chatid_id) {
                $user->chatidPrimary->delete();
                $user->update(['chatid_id' => null]);
            }
        }

        $user->load(['roles', 'gender', 'phone', 'additionalPhones', 'chatidPrimary', 'empresa']);

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

        // Multi-tenancy: Validar acceso a la empresa
        $user->validateEmpresaAccess($user->empresa_id);

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
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'current_password.required' => 'La contraseña actual es requerida',
            'password.required' => 'La nueva contraseña es requerida',
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

    /**
     * Cambiar estado de cuenta del usuario
     */
    public function updateAccountStatus(Request $request, int $id): JsonResponse
    {
        $user = User::with(['roles', 'empresa'])->findOrFail($id);

        // Multi-tenancy: Validar acceso a la empresa
        $user->validateEmpresaAccess($user->empresa_id);

        // Validar que no se pueda cambiar el estado de un SuperAdmin
        if ($user->hasRole('SuperAdmin')) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cambiar el estado de cuenta de un SuperAdmin',
            ], Response::HTTP_FORBIDDEN);
        }

        // Validación
        $request->validate([
            'cuenta' => 'required|in:activada,suspendida,cancelada',
            'razon_suspendida' => 'required_if:cuenta,suspendida,cancelada|nullable|string|max:500',
        ], [
            'cuenta.required' => 'El estado de cuenta es requerido',
            'cuenta.in' => 'El estado de cuenta debe ser: activada, suspendida o cancelada',
            'razon_suspendida.required_if' => 'La razón es requerida cuando se suspende o cancela una cuenta',
            'razon_suspendida.max' => 'La razón no puede exceder 500 caracteres',
        ]);

        $oldStatus = $user->cuenta;
        $newStatus = $request->cuenta;

        // Actualizar estado de cuenta
        $user->cuenta = $newStatus;

        // Si se suspende o cancela, guardar la razón
        if (in_array($newStatus, ['suspendida', 'cancelada'])) {
            $user->razon_suspendida = $request->razon_suspendida;
        } else {
            // Si se activa, limpiar la razón de suspensión
            $user->razon_suspendida = null;
        }

        $user->save();

        // Si el usuario tenía sesión activa y se suspende/cancela, invalidar sus tokens
        if ($oldStatus === 'activada' && in_array($newStatus, ['suspendida', 'cancelada'])) {
            try {
                // Invalidar todos los tokens del usuario
                // Nota: JWT no tiene una forma nativa de invalidar tokens
                // La forma más efectiva es usar una blacklist o caché de tokens invalidados
                // Por ahora, el middleware verificará el estado de cuenta en cada petición
            } catch (\Exception $e) {
                // Log error pero continuar
            }
        }

        // SUSPENSIÓN EN CASCADA: Si es Administrador de una empresa
        // y se suspende/cancela, suspender todos los usuarios de esa empresa
        $affectedUsersCount = 0;
        $cascadeMessage = '';

        if ($user->hasRole('Administrador') && $user->empresa_id) {
            if (in_array($newStatus, ['suspendida', 'cancelada'])) {
                // Suspender usuarios de la empresa
                $affectedUsersCount = $this->suspendCompanyUsers($user, $newStatus);
                $cascadeMessage = $affectedUsersCount > 0
                    ? " Además, se suspendieron {$affectedUsersCount} usuario(s) de la empresa."
                    : '';
            } elseif ($newStatus === 'activada' && in_array($oldStatus, ['suspendida', 'cancelada'])) {
                // Reactivar usuarios de la empresa
                $affectedUsersCount = $this->reactivateCompanyUsers($user);
                $cascadeMessage = $affectedUsersCount > 0
                    ? " Además, se reactivaron {$affectedUsersCount} usuario(s) de la empresa."
                    : '';
            }
        }

        $user->load(['roles', 'gender', 'phone', 'additionalPhones', 'chatidPrimary', 'empresa']);

        return response()->json([
            'success' => true,
            'message' => "Estado de cuenta cambiado exitosamente a: {$newStatus}.{$cascadeMessage}",
            'data' => new UserResource($user),
            'affected_users' => $affectedUsersCount,
        ]);
    }

    /**
     * Suspender todos los usuarios de una empresa cuando el Administrador es suspendido
     *
     * @param User $admin
     * @param string $status
     * @return int Número de usuarios afectados
     */
    private function suspendCompanyUsers(User $admin, string $status): int
    {
        // Obtener nombre de la empresa
        $empresaNombre = $admin->empresa->nombre ?? 'desconocida';

        // Razón por default para los usuarios de la empresa
        $razonDefault = "La cuenta de la empresa \"{$empresaNombre}\" ha sido suspendida/cancelada, por lo tanto ya no podrás iniciar sesión hasta que la empresa sea reactivada nuevamente.";

        // Buscar todos los usuarios de la misma empresa (excepto el Administrador)
        return User::where('empresa_id', $admin->empresa_id)
            ->where('id', '!=', $admin->id)
            ->where('cuenta', '!=', $status) // Solo actualizar los que no están ya en ese estado
            ->update([
                'cuenta' => $status,
                'razon_suspendida' => $razonDefault,
            ]);
    }

    /**
     * Reactivar todos los usuarios de una empresa cuando el Administrador es reactivado
     *
     * @param User $admin
     * @return int Número de usuarios afectados
     */
    private function reactivateCompanyUsers(User $admin): int
    {
        // Obtener nombre de la empresa
        $empresaNombre = $admin->empresa->nombre ?? 'desconocida';

        // Razón por default que se usó cuando se suspendió
        $razonDefault = "La cuenta de la empresa \"{$empresaNombre}\" ha sido suspendida/cancelada, por lo tanto ya no podrás iniciar sesión hasta que la empresa sea reactivada nuevamente.";

        // Buscar todos los usuarios de la misma empresa que tengan esta razón específica
        // (esto asegura que solo reactivamos usuarios suspendidos por la empresa, no por otras razones)
        return User::where('empresa_id', $admin->empresa_id)
            ->where('id', '!=', $admin->id)
            ->where('razon_suspendida', $razonDefault)
            ->update([
                'cuenta' => 'activada',
                'razon_suspendida' => null,
            ]);
    }

    /**
     * Sanitizar el nombre de archivo para hacerlo seguro y profesional
     *
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename(string $filename): string
    {
        // Convertir a minúsculas
        $filename = mb_strtolower($filename, 'UTF-8');

        // Reemplazar caracteres especiales con equivalentes ASCII
        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ñ' => 'n', 'ü' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
        ];
        $filename = strtr($filename, $replacements);

        // Reemplazar espacios y caracteres no alfanuméricos con guiones bajos
        $filename = preg_replace('/[^a-z0-9]+/', '_', $filename);

        // Eliminar guiones bajos múltiples consecutivos
        $filename = preg_replace('/_+/', '_', $filename);

        // Eliminar guiones bajos al inicio y al final
        $filename = trim($filename, '_');

        // Limitar longitud a 30 caracteres (más corto para avatares)
        $filename = substr($filename, 0, 30);

        // Si queda vacío después de sanitizar, usar 'user'
        if (empty($filename)) {
            $filename = 'user';
        }

        return $filename;
    }
}
