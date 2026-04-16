<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/layouts/PublicLayout.vue';
import { Icon } from '@iconify/vue';
import { computed } from 'vue';

defineOptions({ layout: Layout });

const props = defineProps<{
    page: App.Data.PageContentData;
}>();

const page = props.page;
const content = page.content || {};

// Compute contact methods dynamically to avoid undefined entries
const contactMethods = computed(() => {
    const methods = [];

    if (content.email) {
        methods.push({
            label: 'Email',
            value: content.email,
            icon: 'lucide:mail',
            href: `mailto:${content.email}`
        });
    }
    if (content.phone) {
        methods.push({
            label: 'Phone',
            value: content.phone,
            icon: 'lucide:phone',
            href: `tel:${content.phone}`
        });
    }
    if (content.address) {
        methods.push({
            label: 'Address',
            value: content.address,
            icon: 'lucide:map-pin',
            href: null
        });
    }
    if (content.twitter) {
        methods.push({
            label: 'Twitter',
            value: content.twitter,
            icon: 'lucide:twitter',
            href: content.twitter
        });
    }
    if (content.instagram) {
        methods.push({
            label: 'Instagram',
            value: content.instagram,
            icon: 'lucide:instagram',
            href: content.instagram
        });
    }
    if (content.facebook) {
        methods.push({
            label: 'Facebook',
            value: content.facebook,
            icon: 'lucide:facebook',
            href: content.facebook
        });
    }

    return methods;
});
</script>

<template>

    <Head :title="page.title || page.page_key" />

    <div class="bg-gray-50 min-h-screen">
        <!-- Hero / Header -->

        <div class="max-w-10/12 mx-auto text-center my-12">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ page.title || page.page_key }}</h1>
            <p class="text-base md:text-lg text-gray-600 max-w-2xl mx-auto">
                {{ page.subtitle }}
            </p>
        </div>

        <!-- Main Content -->
        <div class="max-w-10/12 mx-auto px-4 py-8 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 sm:p-8">
                    <!-- Conditional rendering based on page_key -->
                    <div v-if="page.page_key === 'contact'">
                        <div class="prose max-w-none">
                            <p class="text-lg">We'd love to hear from you. Reach out through any of the channels below:
                            </p>
                        </div>
                        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div v-for="method in contactMethods" :key="method.label"
                                class="flex items-start space-x-3">
                                <Icon :icon="method.icon" class="size-5 text-primary mt-0.5" />
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ method.label }}</h3>
                                    <a v-if="method.href" :href="method.href" target="_blank"
                                        class="text-primary hover:underline break-all">
                                        {{ method.value }}
                                    </a>
                                    <p v-else class="text-gray-600">{{ method.value }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="page.page_key === 'about'">
                        <div class="prose max-w-none">
                            <div v-if="content.description" class="mb-8">
                                <h2 class="text-xl font-semibold mb-2">About Us</h2>
                                <p class="whitespace-pre-line">{{ content.description }}</p>
                            </div>
                            <div v-if="content.mission" class="mb-8">
                                <h2 class="text-xl font-semibold mb-2">Our Mission</h2>
                                <p class="whitespace-pre-line">{{ content.mission }}</p>
                            </div>
                            <div v-if="content.vision" class="mb-8">
                                <h2 class="text-xl font-semibold mb-2">Our Vision</h2>
                                <p class="whitespace-pre-line">{{ content.vision }}</p>
                            </div>
                            <div v-if="content.values && content.values.length">
                                <h2 class="text-xl font-semibold mb-2">Core Values</h2>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li v-for="val in content.values" :key="val">{{ val }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="page.page_key === 'team'">
                        <div class="prose max-w-none mb-8">
                            <p>Meet the people behind {{ $page.props.appName || 'amomercatus' }}.</p>
                        </div>
                        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                            <div v-for="(member, idx) in (content.members || [])" :key="idx"
                                class="bg-gray-50 rounded-lg p-6 text-center">
                                <div v-if="member.photo_url || member.photo_preview"
                                    class="mx-auto size-24 rounded-full overflow-hidden mb-4">
                                    <img :src="member.photo_url || member.photo_preview"
                                        class="w-full h-full object-cover" />
                                </div>
                                <div v-else
                                    class="mx-auto size-24 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                                    <Icon icon="lucide:user" class="size-10 text-primary" />
                                </div>
                                <h3 class="font-semibold text-lg">{{ member.name }}</h3>
                                <p class="text-sm text-gray-600">{{ member.designation }}</p>
                                <a v-if="member.email" :href="`mailto:${member.email}`"
                                    class="text-sm text-primary mt-2 inline-block">
                                    {{ member.email }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Fallback for any other page_key -->
                    <div v-else>
                        <div class="prose max-w-none">
                            <p v-if="typeof content === 'string'">{{ content }}</p>
                            <pre v-else
                                class="bg-gray-100 p-4 rounded text-sm overflow-auto">{{ JSON.stringify(content, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back to home link -->
            <div class="mt-8 text-center">
                <Link href="/" class="text-primary hover:underline">
                    ← Back to Home
                </Link>
            </div>
        </div>
    </div>
</template>