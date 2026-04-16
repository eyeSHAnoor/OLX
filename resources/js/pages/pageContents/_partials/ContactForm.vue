<!-- resources/js/Pages/page-contents/_partials/ContactForm.vue -->
<script setup lang="ts">
const model = defineModel<Record<string, any>>({ required: true });
</script>

<template>
    <div class="space-y-4 border rounded-lg p-4">
        <h3 class="font-medium text-lg">Contact Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <TextInput label="Email" v-model="model.email" placeholder="contact@example.com" />
            <TextInput label="Phone" v-model="model.phone" placeholder="+1 234 567 890" />
            <TextInput label="Address" v-model="model.address" placeholder="Street, City, Country" />
            <TextInput label="Twitter URL" v-model="model.twitter" placeholder="https://twitter.com/..." />
            <TextInput label="Instagram URL" v-model="model.instagram" placeholder="https://instagram.com/..." />
            <TextInput label="Facebook URL" v-model="model.facebook" placeholder="https://facebook.com/..." />
        </div>
    </div>
</template>