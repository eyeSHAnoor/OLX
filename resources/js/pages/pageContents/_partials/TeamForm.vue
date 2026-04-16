<!-- resources/js/Pages/page-contents/_partials/TeamForm.vue -->
<script setup lang="ts">
import { ref } from 'vue';
import { Icon } from '@iconify/vue';

const model = defineModel<{ members: any[] }>({ required: true });

// Ensure members array exists
if (!model.value.members) model.value.members = [];

const addMember = () => {
    model.value.members.push({
        name: '',
        designation: '',
        email: '',
        photo: null, // will be a File or URL
        photo_preview: null,
    });
};

const removeMember = (index: number) => {
    model.value.members.splice(index, 1);
};

const handlePhotoUpload = (event: Event, index: number) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            model.value.members[index].photo = file;
            model.value.members[index].photo_preview = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};
</script>

<template>
    <div class="space-y-4 border rounded-lg p-4">
        <div class="flex justify-between items-center">
            <h3 class="font-medium text-lg">Team Members</h3>
            <Button type="button" variant="outline" size="sm" @click="addMember">
                <Icon icon="lucide:plus" class="size-4 mr-1" /> Add Member
            </Button>
        </div>

        <div v-if="model.members.length === 0" class="text-center text-muted-foreground py-8">
            No team members added yet. Click "Add Member" to start.
        </div>

        <div v-for="(member, idx) in model.members" :key="idx" class="border rounded-lg p-4 relative">
            <button type="button" @click="removeMember(idx)"
                class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                <Icon icon="lucide:x" class="size-4" />
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <TextInput label="Full Name" v-model="member.name" placeholder="John Doe" required />
                <TextInput label="Designation" v-model="member.designation" placeholder="CEO, Founder" />
                <TextInput label="Email" v-model="member.email" placeholder="john@example.com" type="email" />
                <div>
                    <Label>Photo</Label>
                    <div class="flex items-center gap-3">
                        <div v-if="member.photo_preview" class="size-16 rounded-full overflow-hidden border">
                            <img :src="member.photo_preview" class="w-full h-full object-cover" />
                        </div>
                        <Input type="file" accept="image/*" @change="(e) => handlePhotoUpload(e, idx)" />
                    </div>
                </div>
            </div>
        </div>

        <p class="text-xs text-muted-foreground">Note: Photos will be stored as base64 or you can extend to upload to
            server.</p>
    </div>
</template>