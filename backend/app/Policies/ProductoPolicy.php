<?php

namespace App\Policies;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductoPolicy
{
    /**
     * Determine whether the user can view any models.
     * Verificar que el usuario tenga permiso y solo vea productos de su empresa
     */
    public function viewAny(User $user): bool
    {
        return $user->can('productos.index');
    }

    /**
     * Determine whether the user can view the model.
     * Verificar multi-tenancy: usuario solo puede ver productos de su empresa
     */
    public function view(User $user, Producto $producto): bool
    {
        return $user->can('productos.show')
            && $producto->empresa_id === $user->empresa_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('productos.store');
    }

    /**
     * Determine whether the user can update the model.
     * Verificar multi-tenancy: usuario solo puede editar productos de su empresa
     */
    public function update(User $user, Producto $producto): bool
    {
        return $user->can('productos.update')
            && $producto->empresa_id === $user->empresa_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Verificar multi-tenancy: usuario solo puede eliminar productos de su empresa
     */
    public function delete(User $user, Producto $producto): bool
    {
        return $user->can('productos.destroy')
            && $producto->empresa_id === $user->empresa_id;
    }

    /**
     * Determine whether the user can restore the model.
     * Verificar multi-tenancy: usuario solo puede restaurar productos de su empresa
     */
    public function restore(User $user, Producto $producto): bool
    {
        return $user->can('productos.restore')
            && $producto->empresa_id === $user->empresa_id;
    }

    /**
     * Determine whether the user can update stock of the model.
     * Verificar multi-tenancy: usuario solo puede actualizar stock de productos de su empresa
     */
    public function updateStock(User $user, Producto $producto): bool
    {
        return $user->can('productos.stock')
            && $producto->empresa_id === $user->empresa_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Solo SuperAdmin puede hacer force delete
     */
    public function forceDelete(User $user, Producto $producto): bool
    {
        return $user->hasRole('SuperAdmin');
    }
}
