<!-- resources/js/Pages/page-contents/_partials/AboutForm.vue -->
<script setup lang="ts">
import { ref } from 'vue';
const model = defineModel<Record<string, any>>({ required: true });

const newValue = ref('');

const addValue = () => {
    if (newValue.value.trim()) {
        if (!model.value.values) model.value.values = [];
        model.value.values.push(newValue.value.trim());
        newValue.value = '';
    }
};

const removeValue = (index: number) => {
    model.value.values.splice(index, 1);
};
</script>

<template>
    <div class="space-y-4 border rounded-lg p-4">
        <h3 class="font-medium text-lg">About Page Content</h3>

        <div>
            <Label>Description (Rich Text)</Label>
            <textarea v-model="model.description" rows="6" class="w-full border rounded-md p-2"
                placeholder="Write about your company..."></textarea>
        </div>

        <div>
            <Label>Mission</Label>
            <textarea v-model="model.mission" rows="3" class="w-full border rounded-md p-2"
                placeholder="Our mission..."></textarea>
        </div>

        <div>
            <Label>Vision</Label>
            <textarea v-model="model.vision" rows="3" class="w-full border rounded-md p-2"
                placeholder="Our vision..."></textarea>
        </div>

        <div>
            <Label>Core Values</Label>
            <div class="flex gap-2 mb-2">
                <Input v-model="newValue" placeholder="Add a value (e.g., Integrity)" @keyup.enter="addValue" />
                <Button type="button" @click="addValue">Add</Button>
            </div>
            <div class="flex flex-wrap gap-2">
                <div v-for="(val, idx) in model.values" :key="idx"
                    class="bg-primary/10 text-primary px-3 py-1 rounded-full flex items-center gap-1">
                    {{ val }}
                    <button @click="removeValue(idx)" type="button"
                        class="text-primary/70 hover:text-primary">×</button>
                </div>
            </div>
        </div>
    </div>
</template>