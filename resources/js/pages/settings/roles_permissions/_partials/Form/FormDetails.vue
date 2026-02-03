<script setup lang="ts">
import { TableFilterItem } from '@/types';

const props = defineProps<{
    form: any;
}>();

const emit = defineEmits(['update:form']);

const form = computed({
    get: () => props.form,
    set: (value) => emit('update:form', value),
});

const { titleCase, getImage } = useHelpers();
</script>

<template>
    <section>
        <Card>
            <CardContent>
                <div class="grid gap-5">
                    <TextInput label="Role Name" id="name" v-model="form.name" :error="form.errors.name" />

                    <TextInput label="Role Description" id="description" v-model="form.description"
                        :error="form.errors.description" />
                </div>
            </CardContent>
        </Card>
    </section>
</template>
