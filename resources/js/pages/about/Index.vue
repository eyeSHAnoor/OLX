<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="max-w-10/12mx-auto text-center mb-12">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                {{ page.title || 'About Us' }}
            </h1>
            <p class="text-base md:text-lg text-gray-600 max-w-2xl mx-auto">
                {{ page.subtitle || 'Learn more about who we are and what we stand for.' }}
            </p>
        </div>

        <div class="max-w-10/12 mx-auto space-y-10">
            <!-- Who We Are (description) -->
            <div v-if="content.description" class="bg-white rounded-2xl shadow-md p-6 md:p-8">
                <div class="flex flex-col md:flex-row gap-6 items-center">
                    <div class="flex-1">
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">Who We Are</h2>
                        <div class="text-gray-600 leading-relaxed prose max-w-none" v-html="formattedDescription"></div>
                    </div>
                </div>
            </div>

            <!-- Focus Items (values array) -->
            <div v-if="content.values && content.values.length" class="bg-white rounded-2xl shadow-md p-6 md:p-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">What We Focus On</h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div v-for="(value, idx) in content.values" :key="idx">
                        <h3 class="font-medium text-gray-800 mb-1">{{ typeof value === 'string' ? value : value.title }}
                        </h3>
                        <p v-if="typeof value !== 'string' && value.description" class="text-sm text-gray-500">
                            {{ value.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Membership Benefits (dynamic if exists) -->
            <div v-if="content.membership"
                class="bg-gradient-to-r from-brand-blue/5 to-brand-teal/5 rounded-2xl shadow-md p-6 md:p-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ content.membership.title || 'Membership Options'
                    }}</h2>
                <p class="text-gray-600 mb-4">{{ content.membership.description }}</p>
                <div class="flex flex-wrap gap-3">
                    <span v-for="(benefit, idx) in content.membership.benefits" :key="idx"
                        class="px-3 py-1 bg-white rounded-full text-xs font-medium shadow-sm"
                        :class="getBenefitColor(idx)">
                        {{ benefit }}
                    </span>
                </div>
            </div>

            <!-- Mission & Vision Row -->
            <div class="grid md:grid-cols-2 gap-6">
                <div v-if="content.vision" class="bg-white rounded-2xl shadow-md p-6 text-center">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Our Vision</h2>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ content.vision }}</p>
                </div>
                <div v-if="content.mission" class="bg-white rounded-2xl shadow-md p-6 text-center">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Our Mission</h2>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ content.mission }}</p>
                </div>
            </div>

            <!-- Our Promise -->
            <div v-if="content.promise" class="bg-white rounded-2xl shadow-md p-6 md:p-8">
                <div class="flex flex-col md:flex-row gap-6 items-center">
                    <div class="flex-1 order-2 md:order-1">
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">Our Promise</h2>
                        <p class="text-gray-600 leading-relaxed">{{ content.promise.text }}</p>
                        <p v-if="content.promise.cta" class="text-gray-600 mt-3 font-medium">
                            {{ content.promise.cta }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div v-if="content.cta" class="text-center pt-6 pb-12">
                <Link :href="content.cta.link || '/'"
                    class="inline-flex items-center gap-2 bg-brand-blue text-white px-8 py-3 rounded-lg font-medium hover:bg-brand-blue/80 transition-all duration-200 active:scale-[0.98]">
                    {{ content.cta.text || 'Join Amo Mercatus Today' }}
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import Layout from '@/layouts/PublicLayout.vue';
import { computed } from 'vue';

defineOptions({ layout: Layout });

const props = defineProps({
    page: {
        type: Object,
        required: true
    }
});

const content = props.page.content || {};
console.log('Page content:', content);
// Convert plain text to HTML with line breaks and basic markdown
const formattedDescription = computed(() => {
    if (!content.description) return '';
    // Convert line breaks to <br> and optionally handle **bold**
    return content.description
        .replace(/\n/g, '<br>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
});

// Optional: dynamic colors for benefit badges
const getBenefitColor = (index) => {
    const colors = ['text-brand-blue', 'text-brand-teal', 'text-brand-orange'];
    return colors[index % colors.length];
};
</script>