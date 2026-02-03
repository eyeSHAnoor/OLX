import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const user = computed(() => usePage().props.auth.user)
const isSuperAdmin = computed(() => user.value?.roles.includes('super_admin'));

export function hasRole(roles, isSuperAdminAllowed = true ) {
    if (isSuperAdminAllowed && isSuperAdmin.value ) return true;

    const rolesArray = Array.isArray(roles) ? roles : [roles]
    return rolesArray.some((item) => user.value?.roles?.includes(item))
}

export function hasPermissions(requiredPermissions, isSuperAdminAllowed = true) {
    if (isSuperAdminAllowed && isSuperAdmin.value) return true;

    if (!Array.isArray(requiredPermissions)) {
        return !!user.value?.permissions.includes(requiredPermissions);
    }

    return requiredPermissions.every((perm) =>
        user.value?.permissions.includes(perm)
    );
}


export default function usePermissions() {
    return { hasRole, hasPermissions }
}
