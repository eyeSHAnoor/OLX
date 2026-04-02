<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header with optional search bar hiding -->
        <header class="sticky top-0 z-50 bg-white">
            <Header :hide-search-bar="hideSearchBar" />
        </header>

        <!-- Main Content - Add padding bottom for mobile to account for sticky footer -->
        <main class="flex-1 pb-16 md:pb-8">
            <ValidationErrors />
            <slot />
        </main>

        <!-- Footer - Hidden on mobile, shown on desktop -->
        <footer class="hidden md:block bg-gray-900 text-white">
            <Footer />
        </footer>

        <!-- Mobile Bottom Navigation - Sticky Footer -->
        <MobileBottomNav />

        <!-- <OrderPopup :order="popupOrder" @close="popupOrder = null" /> -->
        <div v-if="userId">
            <OrderPopup v-if="popupOrder" :order="popupOrder"
                :type="popupOrder?.buyer_id === page.props.auth.user.id ? 'buyer' : 'seller'"
                @close="popupOrder = null" />
            <TermsPopup v-if="page.props.auth.user.terms_accepted === 0" />
            <BroadcastPopup :message="popupBroadcast" @close="popupBroadcast = null" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

import Header from '../components/Header.vue'
import Footer from '../components/Footer.vue'
import MobileBottomNav from '../components/MobileBottomNav.vue'
import ValidationErrors from '@/components/ValidationErrors.vue'
import OrderPopup from '@/components/Popup.vue'
import TermsPopup from '@/components/TermsPopup.vue'
import BroadcastPopup from '@/components/BroadcastPopup.vue'

const props = defineProps({
    hideSearchBar: {
        type: Boolean,
        default: false
    }
})

const page = usePage()
const popupOrder = ref(null)
const popupBroadcast = ref(null)
const userId = page.props.auth?.user?.id

onMounted(() => {
    if (!userId) {
        return
    }

    // Seller channel: for orders where the user is the seller
    window.Echo.private(`seller.${userId}`)
        .subscribed(() => {
            console.log('Subscribed to seller channel')
        })
        .listen('OrderCreated', (e) => {
            console.log('Seller EVENT RECEIVED', e)
            popupOrder.value = e
        })

    // Buyer channel: for orders where the user is the buyer
    window.Echo.private(`buyer.${userId}`)
        .subscribed(() => {
            console.log('Subscribed to buyer channel')
        })
        .listen('OrderRequestSent', (e) => {
            console.log('Buyer EVENT RECEIVED', e)
            popupOrder.value = e
        })

    window.Echo.channel('admin-broadcast')
        .listen('BroadcastAdminMessage', (e) => {
            console.log('Admin Broadcast RECEIVED', e)
            popupBroadcast.value = e
        })
})
</script>