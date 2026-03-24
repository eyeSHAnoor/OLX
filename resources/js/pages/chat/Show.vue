<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import { Icon } from '@iconify/vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { useAlertDialog } from '@/composables/useAlertDialog'

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
const contextMenu = ref({
    show: false,
    x: 0,
    y: 0,
    message: null
})

// Alert dialog composable
const { isOpen, options, show, onConfirm, onCancel } = useAlertDialog()

// Dummy messages for quick replies
const dummyMessages = [
    { text: "Hi, is this still available?", icon: "lucide:help-circle" },
    { text: "What's your best price?", icon: "lucide:tag" },
    { text: "Can you deliver?", icon: "lucide:truck" },
    { text: "I'd like to see it in person", icon: "lucide:eye" },
    { text: "Is the price negotiable?", icon: "lucide:coins" },
    { text: "When can we meet?", icon: "lucide:calendar" }
]

useForceTheme('light');

// Scroll to bottom
const scrollToBottom = () => {
    setTimeout(() => {
        if (messagesEnd.value) {
            messagesEnd.value.scrollIntoView({ behavior: 'smooth' })
        }
    }, 100)
}

// Listen for new messages and deleted messages
onMounted(() => {
    if (props.conversation) {
        window.Echo.private(`conversation.${props.conversation.id}`)
            .listen('.message.sent', (e) => {
                messagesList.value.push(e.message)
                scrollToBottom()
            })
            .listen('.message.deleted', (e) => {
                // Remove deleted message from the list
                messagesList.value = messagesList.value.filter(m => m.id !== e.messageId)
            })

        // On mobile, show chat when conversation is loaded
        if (window.innerWidth < 768) {
            showMobileChat.value = true
            showMobileSidebar.value = false
        }
    }

    scrollToBottom()

    // Handle resize
    const handleResize = () => {
        if (window.innerWidth >= 768) {
            showMobileSidebar.value = false
            showMobileChat.value = false
        }

        // Hide context menu on resize
        contextMenu.value.show = false
    }
    window.addEventListener('resize', handleResize)

    // Click outside to close context menu
    const handleClickOutside = () => {
        contextMenu.value.show = false
    }
    window.addEventListener('click', handleClickOutside)

    return () => {
        window.removeEventListener('resize', handleResize)
        window.removeEventListener('click', handleClickOutside)
    }
})

// Cleanup
onUnmounted(() => {
    if (props.conversation) {
        window.Echo.leave(`conversation.${props.conversation.id}`)
    }
})

// Watch for conversation changes (when switching chats)
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
            .listen('.message.deleted', (e) => {
                // Remove deleted message from the list
                messagesList.value = messagesList.value.filter(m => m.id !== e.messageId)
            })
        messagesList.value = props.messages || []
        scrollToBottom()

        // On mobile, switch to chat view
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

const sendDummyMessage = (messageText) => {
    if (!props.conversation) return

    router.post('/chat/send', {
        conversation_id: props.conversation.id,
        body: messageText
    }, {
        preserveScroll: true,
        preserveState: true
    })
}

const handleRightClick = (event, message) => {
    event.preventDefault()

    // Only allow deleting own messages
    if (message.sender_id !== page.props.auth.user.id) return

    // Calculate position to show context menu
    const x = event.clientX
    const y = event.clientY

    // Adjust if menu would go off screen
    const menuWidth = 160
    const menuHeight = 40
    const windowWidth = window.innerWidth
    const windowHeight = window.innerHeight

    contextMenu.value = {
        show: true,
        x: x + menuWidth > windowWidth ? x - menuWidth : x,
        y: y + menuHeight > windowHeight ? y - menuHeight : y,
        message: message
    }
}

const deleteMessage = async () => {
    if (!contextMenu.value.message) return

    // Show confirmation dialog
    const confirmed = await show({
        type: 'confirm',
        title: 'Delete Message',
        description: 'Are you sure you want to delete this message? This action cannot be undone.',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        icon: 'lucide:trash-2'
    })

    if (confirmed) {

        router.delete(route('chat.message.delete', contextMenu.value.message.id), {
            preserveScroll: true,
        });
    }

    // Hide context menu
    contextMenu.value.show = false
}

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const formatDate = (date) => {
    const messageDate = new Date(date)
    const today = new Date()

    if (messageDate.toDateString() === today.toDateString()) {
        return 'Today'
    }
    const yesterday = new Date(today)
    yesterday.setDate(yesterday.getDate() - 1)
    if (messageDate.toDateString() === yesterday.toDateString()) {
        return 'Yesterday'
    }
    return messageDate.toLocaleDateString()
}

const otherUser = computed(() => {
    if (!props.conversation) return null

    return props.conversation.seller_id === page.props.auth.user.id
        ? props.conversation.buyer
        : props.conversation.seller
})

const goBackToSidebar = () => {
    showMobileSidebar.value = true
    showMobileChat.value = false
}

const getLastMessage = (conversation) => {
    if (!conversation.messages || !conversation.messages.length) {
        return null
    }
    return conversation.messages[conversation.messages.length - 1]
}
</script>

<template>
    <OlxLayout>
        <div class="h-[calc(100vh-64px)] max-w-9/11 mx-auto px-6 sm:px-4 py-2 sm:py-4">
            <!-- Alert Dialog Component -->
            <div v-if="isOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl max-w-md w-full p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <Icon v-if="options.icon" :icon="options.icon" class="size-6 text-red-500" />
                        <h3 class="text-lg font-semibold">{{ options.title }}</h3>
                    </div>
                    <p class="text-muted-foreground mb-6">{{ options.description }}</p>
                    <div class="flex gap-3 justify-end">
                        <button @click="onCancel" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                            {{ options.cancelText }}
                        </button>
                        <button @click="onConfirm" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                            {{ options.confirmText }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Custom Context Menu -->
            <div v-if="contextMenu.show" class="fixed z-50 bg-white rounded-lg shadow-lg border py-1 min-w-[160px]"
                :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }">
                <button @click="deleteMessage"
                    class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 text-red-600">
                    <Icon icon="lucide:trash-2" class="size-4" />
                    Delete Message
                </button>
            </div>

            <!-- Desktop Layout (md and above) -->
            <div class="hidden md:flex gap-6 h-full">
                <!-- Conversations Sidebar -->
                <div class="w-80 bg-white rounded-xl shadow-sm flex flex-col">
                    <div class="p-4 border-b">
                        <h2 class="font-semibold text-lg">All Conversations</h2>
                    </div>

                    <div class="flex-1 overflow-y-auto">
                        <div v-if="!conversationsList.length" class="p-4 text-center text-muted-foreground">
                            No conversations yet
                        </div>

                        <Link v-for="conv in conversationsList" :key="conv.id" :href="`/chat/${conv.id}`"
                            class="flex items-center gap-3 p-4 hover:bg-gray-50 transition-colors border-b"
                            :class="conv.id === conversation?.id ? 'bg-primary/5' : ''">

                            <div
                                class="size-12 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                                <Icon icon="lucide:user" class="size-6 text-primary" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-medium truncate">
                                        {{ conv.seller_id === $page.props.auth.user.id ? conv.buyer.name :
                                            conv.seller.name
                                        }}
                                    </h3>
                                    <span class="text-xs text-muted-foreground whitespace-nowrap ml-2">
                                        {{ formatTime(getLastMessage(conv)?.created_at || conv.created_at) }}
                                    </span>
                                </div>

                                <p class="text-sm text-muted-foreground truncate">
                                    {{ getLastMessage(conv)?.body || 'No messages yet' }}
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="flex-1 bg-white rounded-xl shadow-sm flex flex-col">
                    <!-- Chat Header -->
                    <div v-if="conversation" class="border-b p-4 flex items-center gap-3">
                        <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center">
                            <Icon icon="lucide:user" class="size-6 text-primary" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">{{ otherUser?.name }}</h3>
                            <p class="text-sm text-muted-foreground">Re: {{ conversation.product?.ad_title }}</p>
                        </div>
                    </div>
                    <div v-else class="border-b p-4">
                        <h3 class="font-semibold text-lg text-muted-foreground">Select a conversation</h3>
                    </div>

                    <!-- Quick Reply Dummy Messages -->
                    <div v-if="messagesList.length === 0" class="px-3 py-2 border-b bg-gray-50 overflow-x-auto">
                        <p class="text-xs text-muted-foreground mb-2">Quick replies to start:</p>
                        <div class="grid grid-col-2 gap-2">
                            <button v-for="(dummy, index) in dummyMessages" :key="index"
                                @click="sendDummyMessage(dummy.text)"
                                class="flex items-center gap-1 px-3 py-1.5 bg-white border rounded-full text-xs whitespace-nowrap hover:bg-primary hover:text-white transition-colors">
                                <Icon :icon="dummy.icon" class="size-3" />
                                <span>{{ dummy.text }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div v-if="conversation" class="flex-1 overflow-y-auto p-4 space-y-4">
                        <div v-for="message in messagesList" :key="message.id"
                            :class="['flex group', message.sender_id === $page.props.auth.user.id ? 'justify-end' : 'justify-start']">

                            <div @contextmenu.prevent="handleRightClick($event, message)" :class="['max-w-[70%] md:max-w-[60%] cursor-context-menu',
                                message.sender_id === $page.props.auth.user.id ? 'order-2' : 'order-1']">
                                <div :class="['rounded-2xl p-3 break-words',
                                    message.sender_id === $page.props.auth.user.id
                                        ? 'bg-brand-teal text-white rounded-br-none'
                                        : 'bg-gray-100 rounded-bl-none']">
                                    <p class="text-sm md:text-base">{{ message.body }}</p>
                                </div>
                                <div :class="['text-xs text-muted-foreground mt-1 flex items-center gap-1',
                                    message.sender_id === $page.props.auth.user.id ? 'justify-end' : 'justify-start']">
                                    {{ formatTime(message.created_at) }}
                                    <Icon v-if="message.sender_id === $page.props.auth.user.id"
                                        icon="lucide:check-check"
                                        :class="message.is_read ? 'text-green-700' : 'text-gray-400'" class="size-3" />
                                </div>
                            </div>
                        </div>
                        <div ref="messagesEnd"></div>
                    </div>
                    <div v-else class="flex-1 flex items-center justify-center p-4 text-center text-muted-foreground">
                        <div>
                            <Icon icon="lucide:message-circle" class="size-16 mx-auto mb-4 text-gray-300" />
                            <p class="text-lg">Select a conversation to start chatting</p>
                        </div>
                    </div>

                    <!-- Input -->
                    <div v-if="conversation" class="border-t p-4">
                        <form @submit.prevent="sendMessage" class="flex gap-2">
                            <input v-model="newMessage" type="text" placeholder="Type your message..."
                                class="flex-1 px-4 py-3 border rounded-full focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm md:text-base">
                            <button type="submit"
                                class="px-6 py-3 bg-brand-blue text-white rounded-full hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                :disabled="!newMessage.trim()">
                                <span class="hidden sm:inline">Send</span>
                                <Icon icon="lucide:send" class="size-4 sm:size-5" />
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile Layout (below md) -->
            <div class="md:hidden h-full">
                <!-- Mobile Conversations List -->
                <div v-if="showMobileSidebar || (!showMobileChat && !conversation)"
                    class="h-full bg-white rounded-xl shadow-sm flex flex-col">
                    <div class="p-4 border-b flex items-center justify-between">
                        <h2 class="font-semibold text-lg">All Conversations</h2>
                        <span class="text-sm text-muted-foreground">{{ conversationsList.length }} chats</span>
                    </div>

                    <div class="flex-1 overflow-y-auto">
                        <div v-if="!conversationsList.length" class="p-4 text-center text-muted-foreground">
                            No conversations yet
                        </div>

                        <button v-for="conv in conversationsList" :key="conv.id"
                            @click="router.visit(`/chat/${conv.id}`)"
                            class="w-full flex items-center gap-3 p-4 hover:bg-gray-50 transition-colors border-b text-left">

                            <div
                                class="size-12 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 relative">
                                <Icon icon="lucide:user" class="size-6 text-primary" />
                                <!-- Online indicator (optional) -->
                                <div
                                    class="absolute bottom-0 right-0 size-3 bg-green-500 rounded-full border-2 border-white">
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-medium truncate">
                                        {{ conv.seller_id === $page.props.auth.user.id ? conv.buyer.name :
                                            conv.seller.name }}
                                    </h3>
                                    <span class="text-xs text-muted-foreground whitespace-nowrap ml-2">
                                        {{ formatTime(conv.last_message_at || conv.created_at) }}
                                    </span>
                                </div>

                                <p class="text-sm text-muted-foreground truncate">
                                    {{ conv.last_message?.body || 'No messages yet' }}
                                </p>

                                <p class="text-xs text-gray-400 truncate mt-1 flex items-center gap-1">
                                    <Icon icon="lucide:package" class="size-3" />
                                    {{ conv.product?.ad_title }}
                                </p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Mobile Chat View -->
                <div v-else-if="showMobileChat && conversation"
                    class="h-full bg-white rounded-xl shadow-sm flex flex-col">
                    <!-- Mobile Chat Header -->
                    <div class="border-b p-3 flex items-center gap-2">
                        <button @click="goBackToSidebar" class="p-2 hover:bg-gray-100 rounded-full">
                            <Icon icon="lucide:arrow-left" class="size-5" />
                        </button>

                        <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                            <Icon icon="lucide:user" class="size-5 text-primary" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold truncate">{{ otherUser?.name }}</h3>
                            <p class="text-xs text-muted-foreground truncate">Re: {{ conversation.product?.ad_title }}
                            </p>
                        </div>
                    </div>

                    <!-- Mobile Quick Replies -->
                    <div class="px-3 py-2 border-b bg-gray-50 overflow-x-auto">
                        <div class="flex gap-2">
                            <button v-for="(dummy, index) in dummyMessages" :key="index"
                                @click="sendDummyMessage(dummy.text)"
                                class="flex items-center gap-1 px-3 py-1.5 bg-white border rounded-full text-xs whitespace-nowrap hover:bg-primary hover:text-white transition-colors">
                                <Icon :icon="dummy.icon" class="size-3" />
                                <span>{{ dummy.text }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Messages -->
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <div v-for="message in messagesList" :key="message.id"
                            :class="['flex group', message.sender_id === $page.props.auth.user.id ? 'justify-end' : 'justify-start']">

                            <div @contextmenu.prevent="handleRightClick($event, message)"
                                class="relative max-w-[85%] cursor-context-menu">
                                <div :class="['rounded-2xl p-3 break-words text-sm',
                                    message.sender_id === $page.props.auth.user.id
                                        ? 'bg-primary text-white rounded-br-none'
                                        : 'bg-gray-100 rounded-bl-none']">
                                    <p>{{ message.body }}</p>
                                </div>
                                <div :class="['text-xs text-muted-foreground mt-1 flex items-center gap-1',
                                    message.sender_id === $page.props.auth.user.id ? 'justify-end' : 'justify-start']">
                                    {{ formatTime(message.created_at) }}
                                    <Icon v-if="message.sender_id === $page.props.auth.user.id"
                                        icon="lucide:check-check"
                                        :class="message.is_read ? 'text-green-500' : 'text-gray-400'" class="size-3" />
                                </div>
                            </div>
                        </div>
                        <div ref="messagesEnd"></div>
                    </div>

                    <!-- Mobile Input -->
                    <div class="border-t p-3">
                        <form @submit.prevent="sendMessage" class="flex gap-2">
                            <input v-model="newMessage" type="text" placeholder="Type a message..."
                                class="flex-1 px-4 py-2.5 border rounded-full focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm">
                            <button type="submit"
                                class="size-11 bg-primary text-white rounded-full hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                :disabled="!newMessage.trim()">
                                <Icon icon="lucide:send" class="size-4" />
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Mobile Empty State (no conversation selected) -->
                <div v-else
                    class="h-full bg-white rounded-xl shadow-sm flex flex-col items-center justify-center p-4 text-center">
                    <Icon icon="lucide:message-circle" class="size-20 mb-4 text-gray-300" />
                    <p class="text-lg font-medium mb-2">No conversation selected</p>
                    <p class="text-sm text-muted-foreground mb-4">Choose a conversation from the list to start chatting
                    </p>
                    <button @click="showMobileSidebar = true"
                        class="px-6 py-3 bg-primary text-white rounded-full hover:bg-primary/90">
                        View Conversations
                    </button>
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
    background: #888;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .max-w-9\/11 {
        max-width: 100%;
    }
}

/* Quick replies scroll on mobile */
.overflow-x-auto {
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.overflow-x-auto::-webkit-scrollbar {
    display: none;
}

.cursor-context-menu {
    cursor: context-menu;
}
</style>