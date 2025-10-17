<?php

namespace App\Policies;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoriaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('categorias.index');
    }

    /**
     * Determine whether the user can view the model.
     * Verificar multi-tenancy: usuario solo puede ver categorías de su empresa
     */
    public function view(User $user, Categoria $categoria): bool
    {
        return $user->can('categorias.show')
            && $categoria->empresa_id === $user->empresa_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('categorias.store');
    }

    /**
     * Determine whether the user can update the model.
     * Verificar multi-tenancy: usuario solo puede editar categorías de su empresa
     */
    public function update(User $user, Categoria $categoria): bool
    {
        return $user->can('categorias.update')
            && $categoria->empresa_id === $user->empresa_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Verificar multi-tenancy: usuario solo puede eliminar categorías de su empresa
     */
    public function delete(User $user, Categoria $categoria): bool
    {
        return $user->can('categorias.destroy')
            && $categoria->empresa_id === $user->empresa_id;
    }

    /**
     * Determine whether the user can restore the model.
     * Verificar multi-tenancy: usuario solo puede restaurar categorías de su empresa
     */
    public function restore(User $user, Categoria $categoria): bool
    {
        return $user->can('categorias.restore')
            && $categoria->empresa_id === $user->empresa_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Solo SuperAdmin puede hacer force delete
     */
    public function forceDelete(User $user, Categoria $categoria): bool
    {
        return $user->hasRole('SuperAdmin');
    }
}
