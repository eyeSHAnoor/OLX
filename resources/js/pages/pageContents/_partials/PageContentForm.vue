<!-- resources/js/Pages/page-contents/_partials/PageContentForm.vue -->
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import ContactForm from './ContactForm.vue';
import AboutForm from './AboutForm.vue';
import TeamForm from './TeamForm.vue';

const props = defineProps<{
    pageContent?: App.Data.PageContentData;
}>();

const form = useForm({
    page_key: props.pageContent?.page_key || '',
    title: props.pageContent?.title || '',
    subtitle: props.pageContent?.subtitle || '',
    content: props.pageContent?.content || {},
    is_active: props.pageContent?.is_active ?? true,
});

// Helper to update content when page_key changes
const updateContentStructure = (newPageKey: string) => {
    // Only reset content if it's empty or if the key changed and content doesn't match new structure
    if (!props.pageContent || props.pageContent.page_key !== newPageKey) {
        switch (newPageKey) {
            case 'contact':
                form.content = {
                    email: '',
                    phone: '',
                    address: '',
                    twitter: '',
                    instagram: '',
                    facebook: '',
                };
                break;
            case 'about':
                form.content = {
                    description: '',
                    mission: '',
                    vision: '',
                    values: [],
                };
                break;
            case 'team':
                form.content = {
                    members: [],
                };
                break;
            default:
                form.content = {};
        }
    }
};

// Watch page_key changes to restructure content
watch(() => form.page_key, (newKey) => {
    updateContentStructure(newKey);
}, { immediate: true });

function submit() {
    if (props.pageContent) {
        form.put(route('page-contents.update', props.pageContent.id));
    } else {
        form.post(route('page-contents.store'));
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Common fields -->
        <div>
            <Label for="page_key">Page Key *</Label>
            <Input id="page_key" v-model="form.page_key" placeholder="e.g., contact, about, team" required />
            <p class="text-xs text-muted-foreground mt-1">Choose a unique key – this determines the page type.</p>
            <div v-if="form.errors.page_key" class="text-sm text-red-600 mt-1">{{ form.errors.page_key }}</div>
        </div>

        <div>
            <Label for="title">Title</Label>
            <Input id="title" v-model="form.title" placeholder="Page title (optional)" />
        </div>

        <div>
            <Label for="subtitle">Subtitle</Label>
            <Input id="subtitle" v-model="form.subtitle" placeholder="Subtitle (optional)" />
        </div>

        <!-- Dynamic content based on page_key -->
        <div v-if="form.page_key === 'contact'">
            <ContactForm v-model="form.content" />
        </div>
        <div v-else-if="form.page_key === 'about'">
            <AboutForm v-model="form.content" />
        </div>
        <div v-else-if="form.page_key === 'team'">
            <TeamForm v-model="form.content" />
        </div>
        <div v-else-if="form.page_key && !['contact', 'about', 'team'].includes(form.page_key)">
            <div class="p-4 border rounded-md bg-yellow-50 text-yellow-800">
                ⚠️ No specific form defined for "{{ form.page_key }}". You can edit the raw JSON below.
                <textarea v-model="form.content" rows="6"
                    class="w-full mt-2 p-2 border rounded font-mono text-sm"></textarea>
            </div>
        </div>

        <!-- Active toggle -->
        <div class="flex items-center gap-2">
            <Checkbox id="is_active" v-model:checked="form.is_active" />
            <Label for="is_active">Active</Label>
        </div>

        <!-- Submit buttons -->
        <div class="flex justify-end gap-3">
            <Link :href="route('page-contents.index')">
            <Button type="button" variant="outline">Cancel</Button>
            </Link>
            <Button type="submit" :disabled="form.processing">
                {{ pageContent ? 'Update' : 'Create' }}
            </Button>
        </div>
    </form>
</template>