<template>
    <Transition name="modal">
        <div v-if="show" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg max-h-[85vh] flex flex-col shadow-xl">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Select Product</h3>
                    <button @click="close"
                        class="p-1 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                        <Icon icon="lucide:x" class="size-5" />
                    </button>
                </div>

                <!-- Search (optional, improves UX) -->
                <div class="px-5 pt-4 pb-2">
                    <div class="relative">
                        <Icon icon="lucide:search"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 size-4" />
                        <input v-model="search" type="text" placeholder="Search your products..."
                            class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal" />
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto px-5 py-2">
                    <!-- Loading skeleton -->
                    <div v-if="loading" class="space-y-3">
                        <div v-for="i in 3" :key="i" class="flex gap-3 animate-pulse">
                            <div class="w-16 h-16 bg-gray-200 rounded-lg"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else-if="filteredAds.length === 0" class="text-center py-10">
                        <Icon icon="lucide:shopping-bag" class="size-10 text-gray-300 mx-auto mb-3" />
                        <p class="text-sm text-gray-500">
                            {{ search ? 'No products match your search' : 'You don’t have any active ads' }}
                        </p>
                        <Link v-if="!search" :href="route('ads.create')"
                            class="inline-flex items-center gap-1 mt-3 text-sm text-brand-teal hover:underline">
                            Create a new ad
                            <Icon icon="lucide:arrow-right" class="size-3" />
                        </Link>
                    </div>

                    <!-- Product list -->
                    <div v-else class="divide-y divide-gray-100">
                        <div v-for="ad in filteredAds" :key="ad.id" @click="selectAd(ad)"
                            class="group flex gap-3 py-3 cursor-pointer transition-all hover:bg-gray-50 active:bg-gray-100 rounded-lg px-2 -mx-2">
                            <img :src="ad.thumbnail" class="w-16 h-16 rounded-lg object-cover flex-shrink-0 bg-gray-100"
                                :alt="ad.title" />
                            <div class="flex-1 min-w-0">
                                <p
                                    class="font-medium text-gray-900 truncate group-hover:text-brand-teal transition-colors">
                                    {{ ad.title }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">{{ ad.price }}</p>
                            </div>
                            <div class="flex items-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <Icon icon="lucide:chevron-right" class="size-4 text-gray-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer (optional, for clarity) -->
                <div class="px-5 py-3 border-t border-gray-100 text-xs text-gray-500 text-center">
                    Select a product to send as a request
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import axios from 'axios'
import { Icon } from '@iconify/vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    modelValue: Boolean,
    conversationId: Number
})

const emit = defineEmits(['update:modelValue', 'select'])

const show = ref(false)
const ads = ref([])
const loading = ref(false)
const search = ref('')

const filteredAds = computed(() => {
    if (!search.value) return ads.value
    const term = search.value.toLowerCase()
    return ads.value.filter(ad => ad.title.toLowerCase().includes(term))
})

watch(() => props.modelValue, async (val) => {
    show.value = val
    if (val && !ads.value.length) {
        await loadAds()
    }
    if (!val) {
        // reset search when closed
        search.value = ''
    }
})

watch(show, (val) => {
    emit('update:modelValue', val)
})

const loadAds = async () => {
    loading.value = true
    try {
        const res = await axios.get('/chat/my-ads')
        console.log('Loaded ads:', res.data)
        ads.value = res.data.map(ad => ({
            id: ad.id,
            title: ad.ad_title || ad.title,
            price: ad.price,
            thumbnail: '/storage/' + (
                ad.images?.find(img => img.is_primary)?.path ||
                ad.images?.[0]?.path ||
                ad.thumbnail ||
                'placeholder.jpg'
            )
        }))
    } catch (err) {
        console.error('Failed to load ads', err)
    } finally {
        loading.value = false
    }
}

const selectAd = async (ad) => {
    if (!props.conversationId) {
        console.error('conversationId is required!')
        return
    }

    try {
        await axios.post('/chat/send-product', {
            conversation_id: props.conversationId,
            body: `Requested product: ${ad.title} (Qty: 1)`,
            is_order_request: true,
            order_data: {
                ad_id: ad.id,
                qty: 1,
                delivery_option: 'pickup',
                contact_number: '',
                delivery_address: '',
                notes: ''
            }
        })
        close()
    } catch (err) {
        console.error('Failed to send product message', err)
        alert('Failed to send product request.')
    }
}

const close = () => {
    show.value = false
}
</script>

<style scoped>
/* Modal fade transition */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active>div,
.modal-leave-active>div {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-enter-from>div,
.modal-leave-to>div {
    transform: scale(0.95);
    opacity: 0;
}
</style>