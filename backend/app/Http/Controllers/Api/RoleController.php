<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    /**
     * Listar todos los roles con sus permisos
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $roles = Role::query()
            ->with('permissions')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $roles->items(),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ],
        ]);
    }

    /**
     * Mostrar un rol específico con sus permisos
     */
    public function show(int $id): JsonResponse
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $role,
        ]);
    }

    /**
     * Crear un nuevo rol
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ], [
            'name.required' => 'El nombre del rol es requerido',
            'name.unique' => 'Ya existe un rol con este nombre',
            'permissions.array' => 'Los permisos deben ser un array',
            'permissions.*.exists' => 'Uno o más permisos no son válidos',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'api',
        ]);

        // Asignar permisos si se proporcionan
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Rol creado exitosamente',
            'data' => $role,
        ], Response::HTTP_CREATED);
    }

    /**
     * Actualizar un rol
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        // Prevenir modificación de SuperAdmin
        if ($role->name === 'SuperAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede modificar el rol SuperAdmin',
            ], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ], [
            'name.required' => 'El nombre del rol es requerido',
            'name.unique' => 'Ya existe un rol con este nombre',
            'permissions.array' => 'Los permisos deben ser un array',
            'permissions.*.exists' => 'Uno o más permisos no son válidos',
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        // Actualizar permisos
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado exitosamente',
            'data' => $role,
        ]);
    }

    /**
     * Eliminar un rol
     */
    public function destroy(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        // Prevenir eliminación de roles del sistema
        $systemRoles = ['SuperAdmin', 'Administrador', 'Supervisor', 'Vendedor', 'Usuario', 'Contabilidad'];

        if (in_array($role->name, $systemRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden eliminar los roles del sistema',
            ], Response::HTTP_FORBIDDEN);
        }

        // Verificar si hay usuarios con este rol
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el rol porque hay usuarios asignados',
            ], Response::HTTP_CONFLICT);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rol eliminado exitosamente',
        ]);
    }

    /**
     * Obtener todos los permisos disponibles (para asignar a roles)
     */
    public function allPermissions(): JsonResponse
    {
        $permissions = Permission::orderBy('name', 'asc')->get();

        // Agrupar permisos por módulo
        $grouped = $permissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return response()->json([
            'success' => true,
            'data' => $grouped,
            'total' => $permissions->count(),
        ]);
    }
}
