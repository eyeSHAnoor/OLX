<script setup lang="ts">
import { CardContent } from '@/components/ui/card';

const props = defineProps<{
    form: any;
    role: App.Data.RoleData;
    permissions: App.Data.PermissionData[];
}>();

const emit = defineEmits(['update:form']);

const form = computed({
    get: () => props.form,
    set: (value) => emit('update:form', value),
});

const { titleCase, getImage } = useHelpers();

// Group permissions by group name
const groupedPermissions = computed(() => {
    const groups: Record<string, App.Data.PermissionData[]> = {};
    for (const perm of props.permissions) {
        const group = perm.group ?? 'General';
        if (!groups[group]) groups[group] = [];
        groups[group].push(perm);
    }
    return groups;
});

// Toggle permission in form
const togglePermission = (permissionId: number) => {
    const selected = new Set(form.value.permissions ?? []);
    if (selected.has(permissionId)) selected.delete(permissionId);
    else selected.add(permissionId);
    form.value.permissions = Array.from(selected);
};
</script>

<template>
    <section>
        <div class="grap-5 grid max-h-[20rem] !overflow-y-auto">
            <h3 class="">Permissions</h3>

            <!-- Permissions grouped by group -->
            <Card v-for="(perms, group) in groupedPermissions" :key="group" variant="secondary" class="my-2 !py-3">
                <CardContent class="!px-3">
                    <h3 class="mb-3 flex items-center gap-x-1 font-medium text-sm">
                        <Icon icon="icon-park-outline:protect" class="size-3.5" />
                        {{ titleCase(group) }}
                    </h3>

                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        <label v-for="permission in perms" :key="permission.id"
                            class="flex items-center gap-x-2 text-sm text-input-foreground">
                            <Checkbox :model-value="form.permissions?.includes(permission.id)"
                                @update:model-value="() => togglePermission(permission.id)" />
                            <span class="text-xs">{{ titleCase(permission.name) }}</span>
                        </label>
                    </div>
                </CardContent>
            </Card>
        </div>
    </section>
</template>
