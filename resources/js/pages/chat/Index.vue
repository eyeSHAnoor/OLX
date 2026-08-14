<script setup>
import { ref, onMounted, onUnmounted, watch, computed, nextTick } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import { Icon } from '@iconify/vue'
import { usePage } from '@inertiajs/vue3'
import { useTheme } from '@/composables/useTheme'

const { theme } = useTheme()

const props = defineProps({
    conversation: Object,
    messages: Array,
    conversations: Array
})

const page = usePage()
const newMessage = ref('')
const messagesList = ref(props.messages || [])
const messagesEnd = ref(null)
const conversationsList = ref(props.conversations || [])
const showMobileSidebar = ref(false)
const showMobileChat = ref(false)
const isTyping = ref(false)
const searchQuery = ref('')
const selectedFilter = ref('all') // all, unread
const editingMessage = ref(null)

useForceTheme('light');

// Filter conversations
const filteredConversations = computed(() => {
    let filtered = conversationsList.value

    // Search filter
    if (searchQuery.value) {
        filtered = filtered.filter(conv => {
            const name = conv.seller_id === page.props.auth.user.id
                ? conv.buyer.name
                : conv.seller.name
            return name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                conv.product?.ad_title?.toLowerCase().includes(searchQuery.value.toLowerCase())
        })
    }

    // Unread filter
    if (selectedFilter.value === 'unread') {
        filtered = filtered.filter(conv => getUnreadCount(conv) > 0)
    }

    return filtered
})

// Scroll to bottom
const scrollToBottom = async () => {
    await nextTick()
    if (messagesEnd.value) {
        messagesEnd.value.scrollIntoView({ behavior: 'smooth', block: 'end' })
    }
}

// Listen for new messages
onMounted(() => {
    if (props.conversation) {
        window.Echo.private(`conversation.${props.conversation.id}`)
            .listen('.message.sent', (e) => {
                messagesList.value.push(e.message)
                scrollToBottom()
            })
    }

    scrollToBottom()

    // Handle resize
    const handleResize = () => {
        if (window.innerWidth >= 768) {
            showMobileSidebar.value = false
            showMobileChat.value = false
        }
    }
    window.addEventListener('resize', handleResize)

    return () => window.removeEventListener('resize', handleResize)
})

// Cleanup
onUnmounted(() => {
    if (props.conversation) {
        window.Echo.leave(`conversation.${props.conversation.id}`)
    }
})

// Watch for conversation changes
watch(() => props.conversation, (newConv, oldConv) => {
    if (oldConv) {
        window.Echo.leave(`conversation.${oldConv.id}`)
    }
    if (newConv) {
        window.Echo.private(`conversation.${newConv.id}`)
            .listen('.message.sent', (e) => {
                messagesList.value.push(e.message)
                scrollToBottom()
            })
        messagesList.value = props.messages || []
        scrollToBottom()

        if (window.innerWidth < 768) {
            showMobileChat.value = true
            showMobileSidebar.value = false
        }
    }
})

const sendMessage = () => {
    if (!newMessage.value.trim()) return

    router.post('/chat/send', {
        conversation_id: props.conversation.id,
        body: newMessage.value
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            newMessage.value = ''
        }
    })
}

const formatTime = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const formatDate = (date) => {
    const messageDate = new Date(date)
    const today = new Date()
    const yesterday = new Date(today)
    yesterday.setDate(yesterday.getDate() - 1)

    if (messageDate.toDateString() === today.toDateString()) {
        return 'Today'
    }
    if (messageDate.toDateString() === yesterday.toDateString()) {
        return 'Yesterday'
    }
    return messageDate.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: messageDate.getFullYear() !== today.getFullYear() ? 'numeric' : undefined
    })
}

const otherUser = computed(() => {
    if (!props.conversation) return null
    return props.conversation.seller_id === page.props.auth.user.id
        ? props.conversation.buyer
        : props.conversation.seller
})

const getLastMessage = (conversation) => {
    if (!conversation.messages || !conversation.messages.length) {
        return conversation.last_message || null
    }
    return conversation.messages[conversation.messages.length - 1]
}

const getUnreadCount = (conversation) => {
    if (!conversation.messages) return 0
    return conversation.messages.filter(
        m => m.sender_id !== page.props.auth.user.id && !m.is_read
    ).length
}

const goBackToSidebar = () => {
    showMobileSidebar.value = true
    showMobileChat.value = false
}

const getInitials = (name) => {
    return name?.charAt(0).toUpperCase() || '?'
}

const getAvatarColor = (id) => {
    const colors = [
        'from-brand-blue/90 to-brand-blue',
    ]
    return colors[id % colors.length]
}
</script>

<template>
    <OlxLayout :hide-search-bar="true">
        <div class="h-[calc(100vh-73px)] sm:max-w-5xl mx-auto rounded-2xl shadow-lg overflow-hidden" :class="theme.bg">
            <div class="h-full max-w-[1600px] mx-auto px-4 py-4">
                <!-- Desktop Layout -->
                <div class="hidden md:flex h-full overflow-hidden">
                    <!-- Conversations Sidebar -->
                    <div class="w-96 border-r flex flex-col" :class="[theme.border, theme.card]">
                        <!-- Sidebar Header -->
                        <div class="p-5 border-b" :class="[theme.border, theme.card]">
                            <h2 class="text-xl font-semibold mb-3" :class="theme.text">Messages</h2>

                            <!-- Search Bar -->
                            <div class="relative">
                                <Icon icon="lucide:search"
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 size-4"
                                    :class="theme.textMuted" />
                                <input v-model="searchQuery" type="text" placeholder="Search conversations..."
                                    class="w-full pl-9 pr-4 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                    :class="[theme.input, theme.border]" />
                            </div>

                            <!-- Filter Tabs -->
                            <div class="flex gap-2 mt-3">
                                <button @click="selectedFilter = 'all'" :class="[
                                    'flex-1 px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
                                    selectedFilter === 'all'
                                        ? theme.button
                                        : `${theme.bgLight} ${theme.textMuted} ${theme.hover}`
                                ]">
                                    All
                                </button>
                                <button @click="selectedFilter = 'unread'" :class="[
                                    'flex-1 px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
                                    selectedFilter === 'unread'
                                        ? theme.button
                                        : `${theme.bgLight} ${theme.textMuted} ${theme.hover}`
                                ]">
                                    Unread
                                </button>
                            </div>
                        </div>

                        <!-- Conversations List -->
                        <div class="flex-1 overflow-y-auto">
                            <div v-if="!filteredConversations.length"
                                class="flex flex-col items-center justify-center h-full p-8">
                                <Icon icon="lucide:inbox" class="size-12 mb-3" :class="theme.textMuted" />
                                <p class="text-sm" :class="theme.textMuted">No conversations found</p>
                            </div>

                            <Link v-for="conv in filteredConversations" :key="conv.id" :href="`/chat/${conv.id}`"
                                class="group flex items-start gap-3 p-4 transition-all cursor-pointer border-b" :class="[
                                    conv.id === conversation?.id ? theme.bgLight : theme.hover,
                                    theme.border
                                ]">

                                <!-- Avatar -->
                                <div class="relative flex-shrink-0">
                                    <div :class="[
                                        'size-12 rounded-full bg-gradient-to-br flex items-center justify-center text-white font-semibold text-lg shadow-sm',
                                        getAvatarColor(conv.id)
                                    ]">
                                        {{ getInitials(conv.seller_id === page.props.auth.user.id ? conv.buyer.name :
                                            conv.seller.name) }}
                                    </div>
                                    <div class="absolute bottom-0 right-0 size-3 bg-green-500 rounded-full border-2"
                                        :class="theme.card">
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-semibold truncate" :class="theme.text">
                                            {{ conv.seller_id === page.props.auth.user.id ? conv.buyer.name :
                                                conv.seller.name }}
                                        </h3>
                                        <span class="text-xs whitespace-nowrap ml-2" :class="theme.textMuted">
                                            {{ formatTime(getLastMessage(conv)?.created_at || conv.created_at) }}
                                        </span>
                                    </div>

                                    <p class="text-sm truncate" :class="theme.textMuted">
                                        {{ getLastMessage(conv)?.body || 'No messages yet' }}
                                    </p>

                                    <div class="flex items-center justify-between mt-1.5">
                                        <span class="text-xs truncate max-w-[180px] flex items-center gap-1"
                                            :class="theme.textMuted">
                                            <Icon icon="lucide:package" class="size-3" />
                                            {{ conv.product?.ad_title || 'Product' }}
                                        </span>
                                        <span v-if="getUnreadCount(conv) > 0"
                                            class="text-white text-xs font-medium px-2 py-0.5 rounded-full shadow-sm"
                                            :class="theme.button">
                                            {{ getUnreadCount(conv) }}
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Mobile Layout -->
                <div class="md:hidden h-full">
                    <!-- Mobile Conversations List -->
                    <div v-if="showMobileSidebar || (!showMobileChat && !conversation)"
                        class="h-full rounded-2xl shadow-xl flex flex-col overflow-hidden" :class="theme.card">
                        <div class="p-4 border-b" :class="[theme.border, theme.card]">
                            <h2 class="text-xl font-semibold mb-3" :class="theme.text">Messages</h2>
                            <div class="relative">
                                <Icon icon="lucide:search"
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 size-4"
                                    :class="theme.textMuted" />
                                <input v-model="searchQuery" type="text" placeholder="Search..."
                                    class="w-full pl-9 pr-4 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                    :class="[theme.input, theme.border]" />
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto">
                            <div v-if="!filteredConversations.length"
                                class="flex flex-col items-center justify-center h-full p-8">
                                <Icon icon="lucide:inbox" class="size-12 mb-3" :class="theme.textMuted" />
                                <p class="text-sm" :class="theme.textMuted">No conversations</p>
                            </div>

                            <button v-for="conv in filteredConversations" :key="conv.id"
                                @click="router.visit(`/chat/${conv.id}`)"
                                class="w-full flex items-start gap-3 p-4 transition-all border-b text-left"
                                :class="[theme.hover, theme.border]">

                                <div class="relative flex-shrink-0">
                                    <div :class="[
                                        'size-12 rounded-full bg-gradient-to-br flex items-center justify-center text-white font-semibold text-lg',
                                        getAvatarColor(conv.id)
                                    ]">
                                        {{ getInitials(conv.seller_id === page.props.auth.user.id ? conv.buyer.name :
                                            conv.seller.name) }}
                                    </div>
                                    <div class="absolute bottom-0 right-0 size-3 bg-brand-teal rounded-full border-2"
                                        :class="theme.card">
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-semibold truncate" :class="theme.text">
                                            {{ conv.seller_id === page.props.auth.user.id ? conv.buyer.name :
                                                conv.seller.name }}
                                        </h3>
                                        <span class="text-xs whitespace-nowrap ml-2" :class="theme.textMuted">
                                            {{ formatTime(conv.last_message_at || conv.created_at) }}
                                        </span>
                                    </div>
                                    <p class="text-sm truncate" :class="theme.textMuted">
                                        {{ conv.last_message?.body || 'No messages yet' }}
                                    </p>
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-xs truncate max-w-[150px] flex items-center gap-1"
                                            :class="theme.textMuted">
                                            <Icon icon="lucide:package" class="size-3" />
                                            {{ conv.product?.ad_title }}
                                        </span>
                                        <span v-if="getUnreadCount(conv) > 0"
                                            class="text-white text-xs font-medium px-2 py-0.5 rounded-full"
                                            :class="theme.button">
                                            {{ getUnreadCount(conv) }}
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Empty State -->
                    <div v-else class="h-full rounded-2xl shadow-xl flex flex-col items-center justify-center p-6"
                        :class="theme.card">
                        <div class="size-20 rounded-full flex items-center justify-center mb-4" :class="theme.bgLight">
                            <Icon icon="lucide:message-circle" class="size-10" :class="theme.textMuted" />
                        </div>
                        <p class="text-lg font-medium mb-2" :class="theme.text">No conversation selected</p>
                        <p class="text-sm mb-6 text-center" :class="theme.textMuted">Choose a conversation to start
                            messaging</p>
                        <button @click="showMobileSidebar = true"
                            class="px-6 py-3 text-white rounded-xl font-medium shadow-sm" :class="theme.button">
                            View Conversations
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </OlxLayout>
</template>

<style scoped>
/* Custom scrollbar - modern thin style */
.overflow-y-auto::-webkit-scrollbar {
    width: 5px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Smooth transitions */
* {
    transition: all 0.2s ease;
}

/* Message bubble animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.flex>div {
    animation: fadeInUp 0.2s ease-out;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .px-6 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}
</style>