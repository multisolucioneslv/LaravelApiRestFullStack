import { clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

/**
 * Utilidad para combinar clases de Tailwind CSS sin conflictos
 * Usa clsx para manejar condicionales y twMerge para resolver conflictos de clases
 *
 * @example
 * cn('bg-red-500', isActive && 'bg-blue-500') // Si isActive es true, devuelve 'bg-blue-500'
 * cn('px-2 py-1', 'px-4') // Devuelve 'py-1 px-4'
 */
export function cn(...inputs) {
  return twMerge(clsx(inputs))
}
