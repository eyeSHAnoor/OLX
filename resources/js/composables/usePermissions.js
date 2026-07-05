import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const user = computed(() => usePage().props.auth?.user ?? null)
const userRoles = computed(() => Array.isArray(user.value?.roles) ? user.value.roles : [])
const userPermissions = computed(() => Array.isArray(user.value?.permissions) ? user.value.permissions : [])
const isSuperAdmin = computed(() => userRoles.value.includes('super_admin'));

export function hasRole(roles, isSuperAdminAllowed = true ) {
    if (isSuperAdminAllowed && isSuperAdmin.value ) return true;

    const rolesArray = Array.isArray(roles) ? roles : [roles]
    return rolesArray.some((item) => userRoles.value.includes(item))
}

export function hasPermissions(requiredPermissions, isSuperAdminAllowed = true) {
    if (isSuperAdminAllowed && isSuperAdmin.value) return true;

    if (!Array.isArray(requiredPermissions)) {
        return userPermissions.value.includes(requiredPermissions);
    }

    return requiredPermissions.every((perm) =>
        userPermissions.value.includes(perm)
    );
}


export default function usePermissions() {
    return { hasRole, hasPermissions }
}
