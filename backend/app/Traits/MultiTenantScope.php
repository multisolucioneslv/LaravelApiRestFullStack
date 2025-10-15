<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait MultiTenantScope
 *
 * Aplica filtros de multi-tenancy para que los administradores solo vean
 * datos de su empresa y SuperAdmin vea todo.
 */
trait MultiTenantScope
{
    /**
     * Aplicar scope de empresa al query
     *
     * Si el usuario es SuperAdmin: ve todos los registros
     * Si el usuario es Admin: solo ve registros de su empresa
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeForCurrentUser(Builder $query): Builder
    {
        $user = auth()->user();

        if (!$user) {
            // Si no hay usuario autenticado, no retornar nada
            return $query->whereRaw('1 = 0');
        }

        // SuperAdmin puede ver todo
        if ($user->hasRole('SuperAdmin')) {
            return $query;
        }

        // Admin y otros roles solo ven su empresa
        if ($user->empresa_id) {
            return $query->where('empresa_id', $user->empresa_id);
        }

        // Si el usuario no tiene empresa asignada, no ve nada
        return $query->whereRaw('1 = 0');
    }

    /**
     * Validar que el usuario tenga permiso para acceder a un registro
     *
     * @param int|null $empresaId
     * @return bool
     */
    public function canAccessEmpresa(?int $empresaId): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // SuperAdmin puede acceder a cualquier empresa
        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        // Admin solo puede acceder a su propia empresa
        return $user->empresa_id === $empresaId;
    }

    /**
     * Validar que el ID de empresa sea válido para el usuario actual
     * Lanza excepción si no tiene permiso
     *
     * @param int|null $empresaId
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function validateEmpresaAccess(?int $empresaId): void
    {
        if (!$this->canAccessEmpresa($empresaId)) {
            abort(403, 'No tienes permiso para acceder a datos de esta empresa');
        }
    }

    /**
     * Obtener el ID de empresa que debe usarse al crear registros
     *
     * - SuperAdmin: puede elegir cualquier empresa (o null)
     * - Admin: solo su empresa
     *
     * @param int|null $requestedEmpresaId
     * @return int|null
     */
    public function getEmpresaIdForCreate(?int $requestedEmpresaId): ?int
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        // SuperAdmin puede asignar cualquier empresa
        if ($user->hasRole('SuperAdmin')) {
            return $requestedEmpresaId;
        }

        // Admin solo puede crear para su empresa
        return $user->empresa_id;
    }
}
