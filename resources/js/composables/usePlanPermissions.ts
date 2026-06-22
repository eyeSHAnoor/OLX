import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function usePlanPermissions(userOverride?: any) {
  const page = usePage()
  const authUser = computed(() => page.props.auth.user)
  const user = computed(() => userOverride ?? authUser.value)

  const planPermissions = computed(() => user.value?.plan_permissions || [])

  /**
   * Check if the user has a specific plan permission.
   * @param permission - The permission string to check.
   */
  const hasPlanPermission = (permission: string): boolean => {
    return planPermissions.value.includes(permission)
  }

  /**
   * Check if the user has any of the given plan permissions.
   * @param permissions - Array of permission strings.
   */
  const hasAnyPlanPermission = (permissions: string[]): boolean => {
    if (!Array.isArray(permissions)) return false
    return permissions.some((perm) => planPermissions.value.includes(perm))
  }

  /**
   * Check if the user has all of the given plan permissions.
   * @param permissions - Array of permission strings.
   */
  const hasAllPlanPermissions = (permissions: string[]): boolean => {
    if (!Array.isArray(permissions)) return false
    return permissions.every((perm) => planPermissions.value.includes(perm))
  }

  return {
    planPermissions,
    hasPlanPermission,
    hasAnyPlanPermission,
    hasAllPlanPermissions,
  }
}