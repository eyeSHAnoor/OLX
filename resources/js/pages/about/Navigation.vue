<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import Layout from '@/layouts/PublicLayout.vue'

defineOptions({ layout: Layout })
</script>

<template>
    <div class="min-h-screen bg-gray-50">

        <Head title="Menu" />

        <div class="max-w-full md:max-w-10/12 mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="space-y-6">
                <!-- Navigation card -->
                <div class="overflow-hidden bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-800">Navigation</h2>
                    </div>

                    <div class="divide-y divide-gray-100">
                        <!-- Contact -->
                        <Link href="/page/contact"
                            class="flex items-center gap-3 px-4 py-4 hover:bg-gray-50/80 active:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <Icon icon="mdi:message-text-outline" class="text-xl text-primary" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Contact</p>
                                <p class="text-xs text-gray-500">Get in touch</p>
                            </div>
                            <Icon icon="mdi:chevron-right" class="text-gray-400 text-lg" />
                        </Link>

                        <!-- About -->
                        <Link href="/page/about"
                            class="flex items-center gap-3 px-4 py-4 hover:bg-gray-50/80 active:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <Icon icon="mdi:information-outline" class="text-xl text-primary" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">About</p>
                                <p class="text-xs text-gray-500">Our story &amp; mission</p>
                            </div>
                            <Icon icon="mdi:chevron-right" class="text-gray-400 text-lg" />
                        </Link>

                        <!-- Team -->
                        <Link href="/page/team"
                            class="flex items-center gap-3 px-4 py-4 hover:bg-gray-50/80 active:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <Icon icon="mdi:account-group-outline" class="text-xl text-primary" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Team</p>
                                <p class="text-xs text-gray-500">Meet the people</p>
                            </div>
                            <Icon icon="mdi:chevron-right" class="text-gray-400 text-lg" />
                        </Link>

                        <!-- Terms of Service -->
                        <Link href="/policy/terms"
                            class="flex items-center gap-3 px-4 py-4 hover:bg-gray-50/80 active:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <Icon icon="mdi:file-document-outline" class="text-xl text-primary" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Terms of Service</p>
                                <p class="text-xs text-gray-500">Our policies</p>
                            </div>
                            <Icon icon="mdi:chevron-right" class="text-gray-400 text-lg" />
                        </Link>
                    </div>
                </div>

                <!-- Optional: Home button -->
                <div class="text-center">
                    <Link href="/"
                        class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-primary/90 transition-colors">
                        <Icon icon="mdi:home-outline" class="text-lg" />
                        Back to Home
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>