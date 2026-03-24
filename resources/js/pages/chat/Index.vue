<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import { Icon } from '@iconify/vue'
import { usePage } from '@inertiajs/vue3'

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

useForceTheme('light');
// Scroll to bottom
const scrollToBottom = () => {
    setTimeout(() => {
        if (messagesEnd.value) {
            messagesEnd.value.scrollIntoView({ behavior: 'smooth' })
        }
    }, 100)
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
</script>

<template>
    <OlxLayout>
        <div class="h-[calc(100vh-73px)] bg-gray-50">
            <div class="h-full max-w-8/10 mx-auto px-6 sm:px-4 py-2 sm:py-4">
                <!-- Desktop Layout -->
                <div class="hidden md:flex h-full bg-white rounded-xl shadow-sm overflow-hidden">
                    <!-- Conversations Sidebar -->
                    <div class="w-80 border-r flex flex-col">
                        <div class="p-4 border-b bg-gray-50">
                            <h2 class="font-semibold text-gray-800">Messages</h2>
                            <p class="text-sm text-gray-500">{{ conversationsList.length }} conversations</p>
                        </div>

                        <div class="flex-1 overflow-y-auto">
                            <div v-if="!conversationsList.length" class="p-8 text-center">
                                <Icon icon="lucide:inbox" class="size-12 text-gray-300 mx-auto mb-3" />
                                <p class="text-gray-500">No conversations yet</p>
                            </div>

                            <Link v-for="conv in conversationsList" :key="conv.id" :href="`/chat/${conv.id}`"
                                class="flex items-start gap-3 p-4 hover:bg-gray-50 transition-colors border-b"
                                :class="conv.id === conversation?.id ? 'bg-blue-50' : ''">

                                <!-- Avatar -->
                                <div class="relative flex-shrink-0">
                                    <div class="size-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 
                                                flex items-center justify-center text-white font-medium">
                                        {{ (conv.seller_id === page.props.auth.user.id
                                            ? conv.buyer.name.charAt(0)
                                            : conv.seller.name.charAt(0)).toUpperCase() }}
                                    </div>
                                    <div class="absolute bottom-0 right-0 size-3 bg-green-500 rounded-full 
                                                border-2 border-white"></div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-medium text-gray-900 truncate">
                                            {{ conv.seller_id === page.props.auth.user.id
                                                ? conv.buyer.name
                                                : conv.seller.name }}
                                        </h3>
                                        <span class="text-xs text-gray-500 whitespace-nowrap ml-2">
                                            {{ formatTime(getLastMessage(conv)?.created_at || conv.created_at) }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-gray-600 truncate">
                                        {{ getLastMessage(conv)?.body || 'No messages yet' }}
                                    </p>

                                    <div class="flex items-center justify-between mt-2">
                                        <span
                                            class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full truncate max-w-[150px]">
                                            {{ conv.product?.ad_title || 'Product' }}
                                        </span>
                                        <span v-if="getUnreadCount(conv) > 0"
                                            class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                            {{ getUnreadCount(conv) }}
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <!-- Chat Area -->
                    <div class="flex-1 flex flex-col bg-white">
                        <!-- Chat Header -->
                        <div v-if="conversation" class="border-b p-4 flex items-center gap-3 bg-gray-50">
                            <div class="size-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 
                                        flex items-center justify-center text-white font-medium text-lg">
                                {{ otherUser?.name?.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ otherUser?.name }}</h3>
                                <p class="text-sm text-gray-500 flex items-center gap-1">
                                    <Icon icon="lucide:package" class="size-4" />
                                    {{ conversation.product?.ad_title }}
                                </p>
                            </div>
                        </div>
                        <div v-else class="border-b p-4 bg-gray-50">
                            <h3 class="font-semibold text-gray-500">Select a conversation</h3>
                        </div>

                        <!-- Messages -->
                        <div v-if="conversation" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                            <div v-for="message in messagesList" :key="message.id"
                                :class="['flex', message.sender_id === page.props.auth.user.id ? 'justify-end' : 'justify-start']">

                                <div class="max-w-[70%]">
                                    <div :class="['rounded-2xl p-3 break-words',
                                        message.sender_id === page.props.auth.user.id
                                            ? 'bg-blue-500 text-white rounded-br-none'
                                            : 'bg-white text-gray-800 rounded-bl-none shadow-sm']">
                                        <p class="text-sm">{{ message.body }}</p>
                                    </div>
                                    <div
                                        :class="['text-xs text-gray-500 mt-1 flex items-center gap-1',
                                            message.sender_id === page.props.auth.user.id ? 'justify-end' : 'justify-start']">
                                        {{ formatTime(message.created_at) }}
                                        <Icon v-if="message.sender_id === page.props.auth.user.id"
                                            icon="lucide:check-check"
                                            :class="message.is_read ? 'text-blue-500' : 'text-gray-400'"
                                            class="size-3" />
                                    </div>
                                </div>
                            </div>
                            <div ref="messagesEnd"></div>
                        </div>
                        <div v-else class="flex-1 flex items-center justify-center bg-gray-50">
                            <div class="text-center">
                                <Icon icon="lucide:message-circle" class="size-16 mx-auto mb-4 text-gray-300" />
                                <p class="text-gray-500">Select a conversation to start chatting</p>
                            </div>
                        </div>

                        <!-- Input -->
                        <div v-if="conversation" class="border-t p-4 bg-white">
                            <form @submit.prevent="sendMessage" class="flex gap-2">
                                <input v-model="newMessage" type="text" placeholder="Type your message..." class="flex-1 px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 
                                           focus:ring-blue-500/50 text-sm">
                                <button type="submit" class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 
                                           transition-colors disabled:opacity-50 disabled:cursor-not-allowed 
                                           flex items-center gap-2" :disabled="!newMessage.trim()">
                                    <span>Send</span>
                                    <Icon icon="lucide:send" class="size-4" />
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Mobile Layout -->
                <div class="md:hidden h-full">
                    <!-- Mobile Conversations List -->
                    <div v-if="showMobileSidebar || (!showMobileChat && !conversation)"
                        class="h-full bg-white rounded-xl shadow-sm flex flex-col">
                        <div class="p-4 border-b bg-gray-50">
                            <h2 class="font-semibold text-gray-800">Messages</h2>
                            <p class="text-sm text-gray-500">{{ conversationsList.length }} conversations</p>
                        </div>

                        <div class="flex-1 overflow-y-auto">
                            <div v-if="!conversationsList.length" class="p-8 text-center">
                                <Icon icon="lucide:inbox" class="size-12 text-gray-300 mx-auto mb-3" />
                                <p class="text-gray-500">No conversations yet</p>
                            </div>

                            <button v-for="conv in conversationsList" :key="conv.id"
                                @click="router.visit(`/chat/${conv.id}`)"
                                class="w-full flex items-start gap-3 p-4 hover:bg-gray-50 transition-colors border-b text-left">

                                <div class="relative flex-shrink-0">
                                    <div class="size-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 
                                                flex items-center justify-center text-white font-medium">
                                        {{ (conv.seller_id === page.props.auth.user.id
                                            ? conv.buyer.name.charAt(0)
                                            : conv.seller.name.charAt(0)).toUpperCase() }}
                                    </div>
                                    <div class="absolute bottom-0 right-0 size-3 bg-green-500 rounded-full 
                                                border-2 border-white"></div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-medium text-gray-900 truncate">
                                            {{ conv.seller_id === page.props.auth.user.id
                                                ? conv.buyer.name
                                                : conv.seller.name }}
                                        </h3>
                                        <span class="text-xs text-gray-500 whitespace-nowrap ml-2">
                                            {{ formatTime(conv.last_message_at || conv.created_at) }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-gray-600 truncate">
                                        {{ conv.last_message?.body || 'No messages yet' }}
                                    </p>

                                    <div class="flex items-center justify-between mt-2">
                                        <span
                                            class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full truncate max-w-[150px]">
                                            {{ conv.product?.ad_title }}
                                        </span>
                                        <span v-if="getUnreadCount(conv) > 0"
                                            class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                            {{ getUnreadCount(conv) }}
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Chat View -->
                    <div v-else-if="showMobileChat && conversation"
                        class="h-full bg-white rounded-xl shadow-sm flex flex-col">
                        <!-- Mobile Chat Header -->
                        <div class="border-b p-3 flex items-center gap-2 bg-gray-50">
                            <button @click="goBackToSidebar" class="p-2 hover:bg-gray-200 rounded-full">
                                <Icon icon="lucide:arrow-left" class="size-5" />
                            </button>

                            <div class="size-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 
                                        flex items-center justify-center text-white font-medium">
                                {{ otherUser?.name?.charAt(0).toUpperCase() }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 truncate">{{ otherUser?.name }}</h3>
                                <p class="text-xs text-gray-500 truncate flex items-center gap-1">
                                    <Icon icon="lucide:package" class="size-3" />
                                    {{ conversation.product?.ad_title }}
                                </p>
                            </div>
                        </div>

                        <!-- Mobile Messages -->
                        <div class="flex-1 overflow-y-auto p-3 space-y-3 bg-gray-50">
                            <div v-for="message in messagesList" :key="message.id"
                                :class="['flex', message.sender_id === page.props.auth.user.id ? 'justify-end' : 'justify-start']">

                                <div class="max-w-[85%]">
                                    <div :class="['rounded-2xl p-3 break-words text-sm',
                                        message.sender_id === page.props.auth.user.id
                                            ? 'bg-blue-500 text-white rounded-br-none'
                                            : 'bg-white text-gray-800 rounded-bl-none shadow-sm']">
                                        <p>{{ message.body }}</p>
                                    </div>
                                    <div
                                        :class="['text-xs text-gray-500 mt-1 flex items-center gap-1',
                                            message.sender_id === page.props.auth.user.id ? 'justify-end' : 'justify-start']">
                                        {{ formatTime(message.created_at) }}
                                        <Icon v-if="message.sender_id === page.props.auth.user.id"
                                            icon="lucide:check-check"
                                            :class="message.is_read ? 'text-blue-500' : 'text-gray-400'"
                                            class="size-3" />
                                    </div>
                                </div>
                            </div>
                            <div ref="messagesEnd"></div>
                        </div>

                        <!-- Mobile Input -->
                        <div class="border-t p-3 bg-white">
                            <form @submit.prevent="sendMessage" class="flex gap-2">
                                <input v-model="newMessage" type="text" placeholder="Type a message..." class="flex-1 px-4 py-2.5 border rounded-lg focus:outline-none 
                                           focus:ring-2 focus:ring-blue-500/50 text-sm">
                                <button type="submit" class="size-11 bg-blue-500 text-white rounded-lg hover:bg-blue-600 
                                           transition-colors disabled:opacity-50 disabled:cursor-not-allowed 
                                           flex items-center justify-center" :disabled="!newMessage.trim()">
                                    <Icon icon="lucide:send" class="size-4" />
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Mobile Empty State -->
                    <div v-else
                        class="h-full bg-white rounded-xl shadow-sm flex flex-col items-center justify-center p-4">
                        <Icon icon="lucide:message-circle" class="size-20 mb-4 text-gray-300" />
                        <p class="text-lg font-medium text-gray-700 mb-2">No conversation selected</p>
                        <p class="text-sm text-gray-500 mb-6 text-center">
                            Choose a conversation from the list to start chatting
                        </p>
                        <button @click="showMobileSidebar = true"
                            class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 font-medium">
                            View Conversations
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </OlxLayout>
</template>

<style scoped>
/* Smooth transitions */
.flex {
    transition: all 0.3s ease;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Message bubbles */
.bg-blue-500 {
    background-color: #3b82f6;
}

.hover\:bg-blue-600:hover {
    background-color: #2563eb;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .max-w-7xl {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
}
</style>