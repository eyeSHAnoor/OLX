<script setup>
import { ref, onMounted, onUnmounted, watch, computed, nextTick } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import { Icon } from '@iconify/vue'
import { usePage } from '@inertiajs/vue3'
import { useAlertDialog } from '@/composables/useAlertDialog'
import AdPickerModal from './_partials/AdPickerModal.vue'
import axios from 'axios'
import { useTheme } from '@/Composables/useTheme'

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
const contextMenu = ref({
    show: false,
    x: 0,
    y: 0,
    message: null
})
const searchQuery = ref('')
const isTyping = ref(false)
const selectedImage = ref(null)
const showEmojiPicker = ref(false)

// Selection mode
const selectionMode = ref(false)
const selectedConversations = ref([])

// File upload
const selectedFiles = ref([])
const previewUrls = ref([])
const uploading = ref(false)
const uploadProgress = ref({})
const showPreviewModal = ref(false)
const previewItem = ref(null)

// Alert dialog
const { isOpen, options, show, onConfirm, onCancel } = useAlertDialog()

// Sending state
const isSending = ref(false)

// Quick replies
const dummyMessages = [
    { text: "Hi, is this still available?", icon: "lucide:help-circle", color: "brand-blue-light" },
    { text: "What's your best price?", icon: "lucide:tag", color: "brand-blue-light" },
    { text: "Can you deliver?", icon: "lucide:truck", color: "brand-blue-light" },
    { text: "I'd like to see it in person", icon: "lucide:eye", color: "brand-blue-light" },
    { text: "Is the price negotiable?", icon: "lucide:coins", color: "brand-blue-light" },
    { text: "When can we meet?", icon: "lucide:calendar", color: "brand-blue-light" }
]

useForceTheme('light');

// Helpers
const getLatestMessageTimestamp = (conv) => {
    if (conv.last_message && conv.last_message.created_at) {
        return new Date(conv.last_message.created_at).getTime()
    }
    if (conv.messages && conv.messages.length) {
        return new Date(conv.messages[conv.messages.length - 1].created_at).getTime()
    }
    return new Date(conv.created_at || conv.updated_at).getTime()
}

const getLastMessage = (conversation) => {
    if (conversation.last_message) return conversation.last_message
    if (conversation.messages && conversation.messages.length) {
        return conversation.messages[conversation.messages.length - 1]
    }
    return null
}

const sortedConversations = computed(() => {
    const convs = [...conversationsList.value]
    return convs.sort((a, b) => {
        const timeA = getLatestMessageTimestamp(a)
        const timeB = getLatestMessageTimestamp(b)
        return timeB - timeA
    })
})

const filteredConversations = computed(() => {
    if (!searchQuery.value) return sortedConversations.value
    return sortedConversations.value.filter(conv => {
        const name = conv.seller_id === page.props.auth.user.id
            ? conv.buyer.name
            : conv.seller.name
        return name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            conv.product?.ad_title?.toLowerCase().includes(searchQuery.value.toLowerCase())
    })
})

// Selection functions
const toggleSelectionMode = () => {
    selectionMode.value = !selectionMode.value
    if (!selectionMode.value) selectedConversations.value = []
}
const toggleSelectConversation = (convId) => {
    if (selectedConversations.value.includes(convId)) {
        selectedConversations.value = selectedConversations.value.filter(id => id !== convId)
    } else {
        selectedConversations.value.push(convId)
    }
}
const deleteSelectedConversations = async () => {
    if (selectedConversations.value.length === 0) return
    const confirmed = await show({
        type: 'confirm',
        title: 'Delete Conversations',
        description: `Are you sure you want to delete ${selectedConversations.value.length} conversation(s)? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        icon: 'lucide:trash-2'
    })
    if (!confirmed) return
    for (const convId of selectedConversations.value) {
        await router.delete(route('chat.conversation.destroy', convId), {
            preserveState: true,
            preserveScroll: true,
            onError: (error) => console.error('Failed to delete conversation', error)
        })
    }
    conversationsList.value = conversationsList.value.filter(conv => !selectedConversations.value.includes(conv.id))
    if (props.conversation && selectedConversations.value.includes(props.conversation.id)) {
        router.visit(route('chat.index'))
    }
    toggleSelectionMode()
}
const cancelSelection = () => toggleSelectionMode()

// Scroll to bottom of messages
const scrollToBottom = async () => {
    await nextTick()
    if (messagesEnd.value) {
        messagesEnd.value.scrollIntoView({ behavior: 'smooth', block: 'end' })
    }
}

// Mobile: bring input above keyboard and scroll messages to bottom
const bringInputAboveKeyboard = () => {
    if (window.innerWidth >= 768) return
    if (messageInputRef.value) {
        messageInputRef.value.scrollIntoView({ behavior: 'smooth', block: 'center' })
        scrollToBottom()
    }
}

// Focus input and reposition
const focusMessageInput = () => {
    if (messageInputRef.value) {
        messageInputRef.value.focus()
        bringInputAboveKeyboard()
    }
}

// Update conversation list when a new message arrives
const updateConversationOnNewMessage = (message) => {
    const convIndex = conversationsList.value.findIndex(c => c.id === message.conversation_id)
    if (convIndex !== -1) {
        const updatedConv = { ...conversationsList.value[convIndex] }
        updatedConv.last_message = message
        updatedConv.updated_at = message.created_at
        if (updatedConv.messages) {
            updatedConv.messages.push(message)
        } else {
            updatedConv.messages = [message]
        }
        conversationsList.value.splice(convIndex, 1, updatedConv)
        conversationsList.value = [...conversationsList.value]
    }
}

// Echo handlers
const onMessageSent = (e) => {
    messagesList.value.push(e.message)
    scrollToBottom()
    updateConversationOnNewMessage(e.message)
    // If input is focused on mobile, keep it above keyboard (same as after sending)
    if (window.innerWidth < 768 && document.activeElement === messageInputRef.value) {
        bringInputAboveKeyboard()
    }
}
const onMessageDeleted = (e) => {
    messagesList.value = messagesList.value.filter(m => m.id !== e.messageId)
    const conv = conversationsList.value.find(c => c.id === e.conversation_id)
    if (conv && conv.last_message && conv.last_message.id === e.messageId) {
        const remainingMessages = messagesList.value.filter(m => m.conversation_id === e.conversation_id)
        const newLast = remainingMessages.length ? remainingMessages[remainingMessages.length - 1] : null
        conv.last_message = newLast
        conversationsList.value = [...conversationsList.value]
    }
}

const setupEchoListeners = (conversationId) => {
    window.Echo.private(`conversation.${conversationId}`)
        .listen('.message.sent', onMessageSent)
        .listen('.message.deleted', onMessageDeleted)
}
const teardownEchoListeners = (conversationId) => {
    window.Echo.leave(`conversation.${conversationId}`)
}

// Input ref and focus event
const messageInputRef = ref(null)
const scrollInputIntoView = (event) => {
    if (window.innerWidth < 768) {
        bringInputAboveKeyboard()
    }
}

// Watch conversation changes
watch(() => props.conversation, (newConv, oldConv) => {
    if (oldConv) teardownEchoListeners(oldConv.id)
    if (newConv) {
        setupEchoListeners(newConv.id)
        messagesList.value = props.messages || []
        scrollToBottom()
        if (window.innerWidth < 768) {
            showMobileChat.value = true
            showMobileSidebar.value = false
        }
    }
})

// Handle keyboard resize
const handleKeyboardResize = () => {
    if (window.innerWidth >= 768) return
    if (document.activeElement === messageInputRef.value) {
        bringInputAboveKeyboard()
    }
}

onMounted(() => {
    if (props.conversation) {
        setupEchoListeners(props.conversation.id)
        scrollToBottom()
        if (window.innerWidth < 768) {
            showMobileChat.value = true
            showMobileSidebar.value = false
        }
    }
    nextTick(() => {
        if (messageInputRef.value) {
            messageInputRef.value.addEventListener('focus', scrollInputIntoView)
        }
    })
    window.addEventListener('resize', handleKeyboardResize)

    const handleResize = () => {
        if (window.innerWidth >= 768) {
            showMobileSidebar.value = false
            showMobileChat.value = false
        }
        contextMenu.value.show = false
    }
    const handleClickOutside = () => {
        contextMenu.value.show = false
        showEmojiPicker.value = false
    }
    window.addEventListener('resize', handleResize)
    window.addEventListener('click', handleClickOutside)

    return () => {
        if (props.conversation) teardownEchoListeners(props.conversation.id)
        if (messageInputRef.value) {
            messageInputRef.value.removeEventListener('focus', scrollInputIntoView)
        }
        window.removeEventListener('resize', handleResize)
        window.removeEventListener('click', handleClickOutside)
        window.removeEventListener('resize', handleKeyboardResize)
    }
})

onUnmounted(() => {
    if (props.conversation) teardownEchoListeners(props.conversation.id)
})

// ---------- SEND MESSAGE WITH AXIOS ----------
const sendMessage = async () => {
    if (!props.conversation) return
    if (!newMessage.value.trim()) return
    if (isSending.value) return

    const messageToSend = newMessage.value.trim()
    isSending.value = true

    // Optimistic clear
    newMessage.value = ''

    try {
        const response = await axios.post('/chat/send', {
            conversation_id: props.conversation.id,
            body: messageToSend
        }, { timeout: 10000 })

        const newMsg = response.data.message || response.data
        if (newMsg && newMsg.id) {
            messagesList.value.push(newMsg)
            scrollToBottom()
            updateConversationOnNewMessage(newMsg)
        }
        // Keep focus and input above keyboard
        focusMessageInput()
    } catch (error) {
        console.error('Send failed', error)
        newMessage.value = messageToSend
        focusMessageInput()
        let errorMsg = 'Could not send message. Please try again.'
        if (error.response?.data?.message) errorMsg = error.response.data.message
        await show({
            type: 'error',
            title: 'Failed to send',
            description: errorMsg,
            confirmText: 'OK'
        })
    } finally {
        isSending.value = false
    }
}

const sendDummyMessage = async (messageText) => {
    if (!props.conversation) return
    if (isSending.value) return

    isSending.value = true
    try {
        const response = await axios.post('/chat/send', {
            conversation_id: props.conversation.id,
            body: messageText
        }, { timeout: 10000 })
        const newMsg = response.data.message || response.data
        if (newMsg && newMsg.id) {
            messagesList.value.push(newMsg)
            scrollToBottom()
            updateConversationOnNewMessage(newMsg)
        }
        focusMessageInput()
    } catch (error) {
        console.error('Quick reply failed', error)
        let errorMsg = 'Could not send quick reply. Please try again.'
        if (error.response?.data?.message) errorMsg = error.response.data.message
        await show({
            type: 'error',
            title: 'Send failed',
            description: errorMsg,
            confirmText: 'OK'
        })
    } finally {
        isSending.value = false
    }
}

// Context menu, delete message
const handleRightClick = (event, message) => {
    event.preventDefault()
    if (message.sender_id !== page.props.auth.user.id) return
    const x = event.clientX
    const y = event.clientY
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
    const confirmed = await show({
        type: 'confirm',
        title: 'Delete Message',
        description: 'Are you sure you want to delete this message? This action cannot be undone.',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        icon: 'lucide:trash-2'
    })
    if (confirmed) {
        router.delete(route('chat.message.delete', contextMenu.value.message.id), { preserveScroll: true })
    }
    contextMenu.value.show = false
}

// Formatting
const formatTime = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
const formatDate = (date) => {
    const messageDate = new Date(date)
    const today = new Date()
    const yesterday = new Date(today)
    yesterday.setDate(yesterday.getDate() - 1)
    if (messageDate.toDateString() === today.toDateString()) return 'Today'
    if (messageDate.toDateString() === yesterday.toDateString()) return 'Yesterday'
    return messageDate.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: messageDate.getFullYear() !== today.getFullYear() ? 'numeric' : undefined
    })
}

const otherUser = computed(() => {
    if (!props.conversation) return null
    return props.conversation.seller_id === page.props.auth.user.id ? props.conversation.buyer : props.conversation.seller
})

const goBackToSidebar = () => {
    showMobileSidebar.value = true
    showMobileChat.value = false
}

const getUnreadCount = (conversation) => {
    if (!conversation.messages) return 0
    return conversation.messages.filter(m => m.sender_id !== page.props.auth.user.id && !m.is_read).length
}

const getAvatarColor = () => 'bg-brand-blue/80'
const getInitials = (name) => name?.charAt(0).toUpperCase() || '?'

const groupMessagesByDate = (messages) => {
    const groups = {}
    messages.forEach(message => {
        const date = formatDate(message.created_at)
        if (!groups[date]) groups[date] = []
        groups[date].push(message)
    })
    return groups
}

// File upload
const triggerFileSelect = (type) => {
    const input = document.createElement('input')
    input.type = 'file'
    if (type === 'image') input.accept = 'image/*'
    else if (type === 'video') input.accept = 'video/*'
    else if (type === 'document') input.accept = '.pdf,.doc,.docx,.txt,.xls,.xlsx,.ppt,.pptx'
    input.multiple = true
    input.onchange = (e) => handleFileSelect(e.target.files)
    input.click()
}
const handleFileSelect = (files) => {
    for (let file of files) {
        const MAX_SIZE = 10 * 1024 * 1024;
        if (file.size > MAX_SIZE) {
            alert(`File ${file.name} exceeds 10 MB limit.`);
            continue;
        }
        selectedFiles.value.push(file)
        if (file.type.startsWith('image/')) {
            const url = URL.createObjectURL(file)
            previewUrls.value.push({ url, type: 'image', file, name: file.name })
        } else if (file.type.startsWith('video/')) {
            const url = URL.createObjectURL(file)
            previewUrls.value.push({ url, type: 'video', file, name: file.name })
        } else {
            previewUrls.value.push({ url: null, type: 'document', file, name: file.name })
        }
    }
}
const removeFile = (index) => {
    if (previewUrls.value[index]?.url) URL.revokeObjectURL(previewUrls.value[index].url)
    selectedFiles.value.splice(index, 1)
    previewUrls.value.splice(index, 1)
    const newProgress = {}
    selectedFiles.value.forEach(file => {
        if (uploadProgress.value[file.name]) newProgress[file.name] = uploadProgress.value[file.name]
    })
    uploadProgress.value = newProgress
}
const sendFiles = async () => {
    if (selectedFiles.value.length === 0) return
    uploading.value = true
    for (let i = 0; i < selectedFiles.value.length; i++) {
        const file = selectedFiles.value[i]
        const formData = new FormData()
        formData.append('file', file)
        formData.append('conversation_id', props.conversation.id)
        try {
            await axios.post('/chat/upload', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
                onUploadProgress: (progressEvent) => {
                    const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total)
                    uploadProgress.value[file.name] = percent
                }
            })
        } catch (error) {
            console.error('Upload failed', error)
        }
    }
    selectedFiles.value = []
    previewUrls.value = []
    uploadProgress.value = {}
    uploading.value = false
}
const openPreview = (item) => {
    previewItem.value = item
    showPreviewModal.value = true
}
const closePreview = () => {
    showPreviewModal.value = false
    previewItem.value = null
}
const getFileUrl = (messageId) => `/chat/file/${messageId}`

// Attach menu & ad picker
const showAttachMenu = ref(false)
const showAdPicker = ref(false)
const ads = ref([])
const loadingAds = ref(false)

const openAdPicker = async () => {
    showAdPicker.value = true
    loadingAds.value = true
    try {
        const res = await axios.get('/chat/my-ads')
        ads.value = res.data
    } catch (e) { console.error(e) }
    loadingAds.value = false
}
const sendAd = (ad) => {
    axios.post('/chat/send', {
        conversation_id: props.conversation.id,
        type: 'ad',
        ad_id: ad.id
    }).then(() => {
        showAdPicker.value = false
    }).catch(err => console.error(err))
}
</script>

<template>
    <OlxLayout :hide-search-bar="true">
        <div class="h-[calc(100dvh-73px)] sm:h-[calc(100dvh-73px)] sm:max-w-5xl mx-auto overflow-hidden w-full max-w-full"
            :class="theme.bg">
            <div class="h-full max-w-[1600px] mx-auto w-full">
                <!-- Desktop Layout -->
                <div class="hidden md:flex h-full shadow-xl w-full" :class="theme.card">
                    <!-- Conversations Sidebar -->
                    <div class="w-96 border-r flex flex-col" :class="[theme.border, theme.card]">
                        <div class="p-5 border-b" :class="[theme.border, theme.card]">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-2xl font-bold" :class="theme.text">Chats</h2>
                                <div class="flex items-center gap-2">
                                    <button v-if="selectionMode" @click="deleteSelectedConversations"
                                        :disabled="selectedConversations.length === 0"
                                        class="p-2 rounded-full transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="theme.hover">
                                        <Icon icon="lucide:trash-2" class="size-5 text-red-500" />
                                    </button>
                                    <button v-if="selectionMode" @click="cancelSelection"
                                        class="p-2 rounded-full transition-colors" :class="theme.hover">
                                        <Icon icon="lucide:x" class="size-5" :class="theme.textMuted" />
                                    </button>
                                    <button v-else @click="toggleSelectionMode"
                                        class="p-2 rounded-full transition-colors" :class="theme.hover">
                                        <Icon icon="lucide:edit-2" class="size-5" :class="theme.textMuted" />
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <Icon icon="lucide:search"
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 size-4"
                                    :class="theme.textMuted" />
                                <input v-model="searchQuery" type="text" placeholder="Search or start new chat"
                                    class="w-full pl-9 pr-4 py-2.5 border-0 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/20 transition-all"
                                    :class="theme.input" />
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto">
                            <div v-if="!filteredConversations.length"
                                class="flex flex-col items-center justify-center h-full p-8">
                                <Icon icon="lucide:inbox" class="size-12 mb-3" :class="theme.textMuted" />
                                <p class="text-sm" :class="theme.textMuted">No conversations found</p>
                            </div>
                            <div v-for="conv in filteredConversations" :key="conv.id"
                                @click="selectionMode ? toggleSelectConversation(conv.id) : router.visit(`/chat/${conv.id}`)"
                                class="group flex items-start gap-3 px-5 py-3 transition-all cursor-pointer border-b"
                                :class="[
                                    conv.id === conversation?.id && !selectionMode ? theme.bgLight : theme.hover,
                                    theme.border,
                                    selectionMode && selectedConversations.includes(conv.id) ? theme.bgLight : ''
                                ]">
                                <div v-if="selectionMode" class="flex-shrink-0 pt-2">
                                    <input type="checkbox" :checked="selectedConversations.includes(conv.id)"
                                        @click.stop @change="toggleSelectConversation(conv.id)"
                                        class="w-4 h-4 rounded border-gray-300 text-brand-blue focus:ring-brand-blue" />
                                </div>
                                <div class="relative flex-shrink-0">
                                    <div :class="[
                                        'size-12 rounded-full flex items-center justify-center text-white font-semibold text-lg shadow-sm',
                                        getAvatarColor()
                                    ]">
                                        {{ getInitials(conv.seller_id === page.props.auth.user.id ? conv.buyer.name :
                                            conv.seller.name) }}
                                    </div>
                                    <div class="absolute bottom-0 right-0 size-3 bg-green-500 rounded-full border-2"
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
                                            {{ formatTime(getLastMessage(conv)?.created_at || conv.created_at) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm truncate flex-1" :class="theme.textMuted">
                                            <span v-if="getLastMessage(conv)?.sender_id === page.props.auth.user.id"
                                                :class="theme.textMuted">You:</span>
                                            {{ getLastMessage(conv)?.body || 'No messages yet' }}
                                        </p>
                                        <span v-if="getUnreadCount(conv) > 0"
                                            class="text-white text-xs font-medium px-2 py-0.5 rounded-full ml-2"
                                            :class="theme.button">
                                            {{ getUnreadCount(conv) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Area Desktop -->
                    <div class="flex-1 flex flex-col w-full min-w-0" :class="theme.bgLight">
                        <div v-if="conversation" class="border-b px-6 py-4 flex items-center justify-between shadow-sm"
                            :class="[theme.border, theme.card]">
                            <Link :href="route('user.profile', otherUser?.id)"
                                class="flex items-center cursor-pointer gap-3">
                                <div :class="[
                                    'size-12 rounded-full flex items-center justify-center text-white font-semibold text-lg shadow-sm',
                                    getAvatarColor()
                                ]">
                                    {{ otherUser?.name?.charAt(0).toUpperCase() || '?' }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg" :class="theme.text">{{ otherUser?.name }}</h3>
                                    <p class="text-xs flex items-center gap-1" :class="theme.textMuted">
                                        <span class="inline-block size-2 bg-green-500 rounded-full"></span>
                                        Online
                                    </p>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="border-b px-6 py-5" :class="[theme.border, theme.card]">
                            <h3 class="text-center" :class="theme.textMuted">Select a conversation to start messaging
                            </h3>
                        </div>

                        <div v-if="conversation" class="flex-1 overflow-y-auto px-6 py-6">
                            <div v-for="(messages, date) in groupMessagesByDate(messagesList)" :key="date">
                                <div class="flex justify-center mb-4">
                                    <span class="text-xs px-3 py-1 rounded-full"
                                        :class="[theme.bgLight, theme.textMuted]">{{ date
                                        }}</span>
                                </div>
                                <div v-for="message in messages" :key="message.id"
                                    :class="['flex mb-4', message.sender_id === page.props.auth.user.id ? 'justify-end' : 'justify-start']">
                                    <div class="flex items-end gap-2 max-w-[70%]">
                                        <div v-if="message.sender_id !== page.props.auth.user.id"
                                            :class="['size-8 rounded-full flex-shrink-0', getAvatarColor()]"></div>
                                        <div @contextmenu.prevent="handleRightClick($event, message)"
                                            class="relative cursor-context-menu">
                                            <div :class="[
                                                'rounded-2xl px-4 py-2.5 break-words shadow-sm',
                                                message.type === 'text'
                                                    ? (message.sender_id === page.props.auth.user.id
                                                        ? `${theme.button} text-white rounded-br-sm`
                                                        : `${theme.card} ${theme.text} rounded-bl-sm ${theme.border}`)
                                                    : ''
                                            ]">
                                                <template v-if="message.type === 'file'">
                                                    <img v-if="message.body.match(/\.(jpg|jpeg|png|gif|webp)$/i)"
                                                        :src="getFileUrl(message.id)"
                                                        class="max-w-[200px] rounded-lg cursor-pointer hover:opacity-90 transition shadow-sm border"
                                                        :class="theme.border"
                                                        @click="openPreview({ url: getFileUrl(message.id), type: 'image', name: message.body.split('/').pop() })" />
                                                    <video v-else-if="message.body.match(/\.(mp4|mov|avi|webm)$/i)"
                                                        controls class="max-w-[250px] rounded-lg shadow-sm border"
                                                        :class="theme.border">
                                                        <source :src="getFileUrl(message.id)" />
                                                    </video>
                                                    <div v-else class="flex items-center gap-2 p-2 rounded-lg"
                                                        :class="theme.bgLight">
                                                        <Icon icon="lucide:file-text" class="size-6"
                                                            :class="theme.textMuted" />
                                                        <span class="text-sm truncate max-w-[150px]"
                                                            :class="theme.text">{{
                                                                message.body.split('/').pop() }}</span>
                                                        <a :href="getFileUrl(message.id)" target="_blank"
                                                            class="hover:underline text-xs ml-2"
                                                            :class="theme.textAccent">Download</a>
                                                    </div>
                                                </template>
                                                <p v-else class="text-sm leading-relaxed" :class="theme.message">
                                                    {{ message.body }}</p>
                                            </div>
                                            <div :class="[
                                                'text-xs mt-1 flex items-center gap-1',
                                                message.sender_id === page.props.auth.user.id ? 'justify-end' : 'justify-start',
                                                theme.textMuted
                                            ]">
                                                {{ formatTime(message.created_at) }}
                                                <Icon v-if="message.sender_id === page.props.auth.user.id"
                                                    :icon="message.is_read ? 'lucide:check-check' : 'lucide:check'"
                                                    :class="message.is_read ? theme.textAccent : theme.textMuted"
                                                    class="size-3" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div ref="messagesEnd"></div>
                        </div>

                        <div v-else class="flex-1 flex items-center justify-center">
                            <div class="text-center">
                                <div class="size-24 rounded-full flex items-center justify-center mx-auto mb-4"
                                    :class="theme.bgLight">
                                    <Icon icon="lucide:message-circle" class="size-12" :class="theme.textMuted" />
                                </div>
                                <p class="font-medium" :class="theme.text">No conversation selected</p>
                                <p class="text-sm mt-1" :class="theme.textMuted">Choose a chat from the sidebar to start
                                    messaging
                                </p>
                            </div>
                        </div>

                        <!-- Input Area Desktop -->
                        <div v-if="conversation" class="border-t p-4" :class="[theme.border, theme.card]">
                            <div v-if="previewUrls.length" class="mb-3 flex flex-wrap gap-2">
                                <div v-for="(item, idx) in previewUrls" :key="idx"
                                    class="relative group rounded-lg overflow-hidden border shadow-sm"
                                    :class="[theme.border, theme.bgLight]">
                                    <div v-if="item.type === 'image'" class="w-20 h-20 bg-cover bg-center"
                                        :style="{ backgroundImage: `url(${item.url})` }"></div>
                                    <div v-else-if="item.type === 'video'"
                                        class="w-20 h-20 bg-gray-800 flex items-center justify-center">
                                        <Icon icon="lucide:video" class="size-8 text-white" />
                                    </div>
                                    <div v-else class="w-20 h-20 flex flex-col items-center justify-center p-2"
                                        :class="theme.bgLight">
                                        <Icon icon="lucide:file-text" class="size-8" :class="theme.textMuted" />
                                        <span class="text-xs truncate w-full text-center" :class="theme.textMuted">{{
                                            item.name.slice(0, 10) }}</span>
                                    </div>
                                    <button @click="removeFile(idx)"
                                        class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition">
                                        <Icon icon="lucide:x" class="size-3" />
                                    </button>
                                    <div v-if="uploadProgress[item.file.name]"
                                        class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                        <span class="text-white text-xs">{{ uploadProgress[item.file.name] }}%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-2 mb-3 overflow-x-auto pb-2">
                                <button v-for="(dummy, index) in dummyMessages.slice(0, 4)" :key="index"
                                    @click="sendDummyMessage(dummy.text)" :disabled="isSending"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-all hover:scale-105 disabled:opacity-50"
                                    :class="[theme.bgLight, theme.textAccent]">
                                    <Icon :icon="dummy.icon" class="size-3" />
                                    <span>{{ dummy.text }}</span>
                                </button>
                            </div>

                            <div class="flex flex-col gap-2">
                                <div class="flex gap-2 items-center">
                                    <div class="relative">
                                        <button type="button" @click="showAttachMenu = !showAttachMenu"
                                            class="p-2 rounded-full transition-colors" title="Attach"
                                            :class="theme.hover">
                                            <Icon icon="lucide:paperclip" class="size-5" :class="theme.textMuted" />
                                        </button>
                                        <div v-if="showAttachMenu"
                                            class="absolute bottom-12 left-0 shadow-lg border rounded-xl p-2 w-40 z-50"
                                            :class="[theme.card, theme.border]">
                                            <button @click="triggerFileSelect('image'); showAttachMenu = false"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm rounded-lg"
                                                :class="theme.hover">
                                                <Icon icon="lucide:image" class="size-4" :class="theme.icon" /> Image
                                            </button>
                                            <button @click="triggerFileSelect('video'); showAttachMenu = false"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm rounded-lg"
                                                :class="theme.hover">
                                                <Icon icon="lucide:video" class="size-4" :class="theme.icon" /> Video
                                            </button>
                                            <button @click="triggerFileSelect('document'); showAttachMenu = false"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm rounded-lg"
                                                :class="theme.hover">
                                                <Icon icon="lucide:file-text" class="size-4" :class="theme.icon" />
                                                Document
                                            </button>
                                            <button @click="openAdPicker(); showAttachMenu = false"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm rounded-lg"
                                                :class="theme.hover">
                                                <Icon icon="lucide:shopping-bag" class="size-4" :class="theme.icon" />
                                                Product
                                            </button>
                                        </div>
                                    </div>

                                    <button type="button" class="p-2 rounded-full transition-colors"
                                        :class="theme.hover">
                                        <Icon icon="lucide:smile" class="size-5" :class="theme.textMuted" />
                                    </button>

                                    <div class="flex-1 flex gap-2">
                                        <input ref="messageInputRef" v-model="newMessage" type="text"
                                            placeholder="Type a message..."
                                            class="flex-1 px-4 py-2.5 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-brand-blue/20 transition-all text-base"
                                            :class="theme.input" @keyup.enter="sendMessage" />
                                        <button type="submit" @click="sendMessage"
                                            class="size-10 text-white rounded-full transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center shadow-sm"
                                            :class="theme.button" :disabled="!newMessage.trim() || isSending">
                                            <Icon v-if="!isSending" icon="lucide:send" class="size-4" />
                                            <Icon v-else icon="lucide:loader-2" class="size-4 animate-spin" />
                                        </button>
                                    </div>
                                </div>
                                <button v-if="selectedFiles.length" @click="sendFiles" :disabled="uploading"
                                    class="text-white rounded-xl py-2 text-sm font-medium transition disabled:opacity-50"
                                    :class="theme.button">
                                    <span v-if="uploading">Uploading...</span>
                                    <span v-else>Send {{ selectedFiles.length }} file(s)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Layout -->
                <div class="md:hidden h-full w-full overflow-hidden">
                    <!-- Conversations Sidebar (Mobile) -->
                    <div v-if="showMobileSidebar || (!showMobileChat && !conversation)"
                        class="h-full w-full flex flex-col overflow-hidden" :class="theme.card">
                        <div class="p-4 border-b" :class="[theme.border, theme.card]">
                            <div class="flex items-center justify-between mb-3">
                                <h2 class="text-2xl font-bold" :class="theme.text">Chats</h2>
                                <div class="flex items-center gap-2">
                                    <button v-if="selectionMode" @click="deleteSelectedConversations"
                                        :disabled="selectedConversations.length === 0"
                                        class="p-2 rounded-full transition-colors disabled:opacity-50"
                                        :class="theme.hover">
                                        <Icon icon="lucide:trash-2" class="size-5 text-red-500" />
                                    </button>
                                    <button v-if="selectionMode" @click="cancelSelection"
                                        class="p-2 rounded-full transition-colors" :class="theme.hover">
                                        <Icon icon="lucide:x" class="size-5" :class="theme.textMuted" />
                                    </button>
                                    <button v-else @click="toggleSelectionMode"
                                        class="p-2 rounded-full transition-colors" :class="theme.hover">
                                        <Icon icon="lucide:edit-2" class="size-5" :class="theme.textMuted" />
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <Icon icon="lucide:search"
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 size-4"
                                    :class="theme.textMuted" />
                                <input v-model="searchQuery" type="text" placeholder="Search"
                                    class="w-full pl-9 pr-4 py-2.5 border-0 rounded-xl text-sm focus:outline-none"
                                    :class="theme.input" />
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto">
                            <div v-if="!filteredConversations.length"
                                class="flex flex-col items-center justify-center h-full p-8">
                                <Icon icon="lucide:inbox" class="size-12 mb-3" :class="theme.textMuted" />
                                <p class="text-sm" :class="theme.textMuted">No conversations</p>
                            </div>
                            <button v-for="conv in filteredConversations" :key="conv.id"
                                @click="selectionMode ? toggleSelectConversation(conv.id) : router.visit(`/chat/${conv.id}`)"
                                class="w-full flex items-start gap-3 px-4 py-3 transition-all border-b text-left"
                                :class="[theme.hover, theme.border, selectionMode && selectedConversations.includes(conv.id) ? theme.bgLight : '']">
                                <div v-if="selectionMode" class="flex-shrink-0 pt-2">
                                    <input type="checkbox" :checked="selectedConversations.includes(conv.id)"
                                        @click.stop @change="toggleSelectConversation(conv.id)"
                                        class="w-4 h-4 rounded border-gray-300 text-brand-blue focus:ring-brand-blue" />
                                </div>
                                <div class="relative flex-shrink-0">
                                    <div :class="[
                                        'size-12 rounded-full flex items-center justify-center text-white font-semibold text-lg',
                                        getAvatarColor()
                                    ]">
                                        {{ getInitials(conv.seller_id === page.props.auth.user.id ? conv.buyer.name :
                                            conv.seller.name)
                                        }}
                                    </div>
                                    <div class="absolute bottom-0 right-0 size-3 bg-green-500 rounded-full border-2"
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
                                            {{ formatTime(conv.last_message?.created_at || conv.created_at) }}
                                        </span>
                                    </div>
                                    <p class="text-sm truncate" :class="theme.textMuted">
                                        {{ conv.last_message?.body || 'No messages yet' }}
                                    </p>
                                </div>
                                <span v-if="getUnreadCount(conv) > 0"
                                    class="text-white text-xs font-medium px-2 py-0.5 rounded-full self-center"
                                    :class="theme.button">
                                    {{ getUnreadCount(conv) }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Chat Area (Mobile) -->
                    <div v-else-if="showMobileChat && conversation" class="h-full w-full flex flex-col overflow-hidden"
                        :class="theme.bgLight">
                        <!-- Header -->
                        <div class="border-b px-4 py-3 flex items-center gap-3 flex-shrink-0"
                            :class="[theme.border, theme.card]">
                            <button @click="goBackToSidebar" class="p-1 -ml-1">
                                <Icon icon="lucide:arrow-left" class="size-6" :class="theme.textMuted" />
                            </button>
                            <Link :href="route('user.profile', otherUser?.id)"
                                class="flex items-center cursor-pointer gap-3">
                                <div :class="[
                                    'size-10 rounded-full flex items-center justify-center text-white font-semibold',
                                    getAvatarColor()
                                ]">
                                    {{ otherUser?.name?.charAt(0).toUpperCase() || '?' }}
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold" :class="theme.text">{{ otherUser?.name }}</h3>
                                    <p class="text-xs text-green-500">Online</p>
                                </div>
                            </Link>
                        </div>

                        <!-- Messages container (scrollable) -->
                        <div class="flex-1 overflow-y-auto p-4 space-y-4">
                            <div v-for="message in messagesList" :key="message.id"
                                :class="['flex', message.sender_id === page.props.auth.user.id ? 'justify-end' : 'justify-start']">
                                <div class="max-w-[85%]">
                                    <div :class="[
                                        'rounded-2xl px-4 py-2.5 break-words shadow-sm',
                                        message.type === 'text'
                                            ? (message.sender_id === page.props.auth.user.id
                                                ? `${theme.button} text-white rounded-br-sm`
                                                : `${theme.card} ${theme.text} rounded-bl-sm ${theme.border}`)
                                            : ''
                                    ]">
                                        <template v-if="message.type === 'file'">
                                            <img v-if="message.body.match(/\.(jpg|jpeg|png|gif|webp)$/i)"
                                                :src="getFileUrl(message.id)"
                                                class="max-w-[200px] rounded-lg cursor-pointer shadow-sm border"
                                                :class="theme.border"
                                                @click="openPreview({ url: getFileUrl(message.id), type: 'image', name: message.body.split('/').pop() })" />
                                            <video v-else-if="message.body.match(/\.(mp4|mov|avi|webm)$/i)" controls
                                                class="max-w-[250px] rounded-lg shadow-sm border" :class="theme.border">
                                                <source :src="getFileUrl(message.id)" />
                                            </video>
                                            <div v-else class="flex items-center gap-2">
                                                <Icon icon="lucide:file-text" class="size-5" :class="theme.textMuted" />
                                                <span class="text-sm truncate" :class="theme.text">{{
                                                    message.body.split('/').pop()
                                                }}</span>
                                                <a :href="getFileUrl(message.id)" target="_blank"
                                                    class="underline text-xs" :class="theme.textAccent">Download</a>
                                            </div>
                                        </template>
                                        <p v-else :class="theme.message">{{ message.body }}</p>
                                    </div>
                                    <div :class="[
                                        'text-xs mt-1 flex items-center gap-1',
                                        message.sender_id === page.props.auth.user.id ? 'justify-end' : 'justify-start',
                                        theme.textMuted
                                    ]">
                                        {{ formatTime(message.created_at) }}
                                    </div>
                                </div>
                            </div>
                            <div ref="messagesEnd"></div>
                        </div>

                        <!-- Input area (fixed at bottom, above keyboard) -->
                        <div class="border-t p-3 flex-shrink-0" :class="[theme.border, theme.card]">
                            <div class="flex gap-2 mb-3 overflow-x-auto pb-2">
                                <button v-for="(dummy, index) in dummyMessages.slice(0, 4)" :key="index"
                                    @click="sendDummyMessage(dummy.text)" :disabled="isSending"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-all hover:scale-105 disabled:opacity-50"
                                    :class="[theme.bgLight, theme.textAccent]">
                                    <Icon :icon="dummy.icon" class="size-3" />
                                    <span>{{ dummy.text }}</span>
                                </button>
                            </div>

                            <div v-if="previewUrls.length" class="mb-2 flex flex-wrap gap-2">
                                <div v-for="(item, idx) in previewUrls" :key="idx"
                                    class="relative w-16 h-16 rounded-lg overflow-hidden border shadow-sm"
                                    :class="theme.border">
                                    <img v-if="item.type === 'image'" :src="item.url"
                                        class="w-full h-full object-cover" />
                                    <div v-else-if="item.type === 'video'"
                                        class="w-full h-full bg-gray-800 flex items-center justify-center">
                                        <Icon icon="lucide:video" class="size-6 text-white" />
                                    </div>
                                    <div v-else class="w-full h-full flex items-center justify-center"
                                        :class="theme.bgLight">
                                        <Icon icon="lucide:file-text" class="size-6" :class="theme.textMuted" />
                                    </div>
                                    <button @click="removeFile(idx)"
                                        class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-0.5">
                                        <Icon icon="lucide:x" class="size-3" />
                                    </button>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <div class="relative">
                                    <button type="button" @click="showAttachMenu = !showAttachMenu"
                                        class="p-2 rounded-full transition-colors" title="Attach" :class="theme.hover">
                                        <Icon icon="lucide:paperclip" class="size-5" :class="theme.textMuted" />
                                    </button>
                                    <div v-if="showAttachMenu"
                                        class="absolute bottom-12 left-0 shadow-lg border rounded-xl p-2 w-40 z-50"
                                        :class="[theme.card, theme.border]">
                                        <button @click="triggerFileSelect('image'); showAttachMenu = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm rounded-lg"
                                            :class="theme.hover">
                                            <Icon icon="lucide:image" class="size-4" :class="theme.icon" /> Image
                                        </button>
                                        <button @click="triggerFileSelect('video'); showAttachMenu = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm rounded-lg"
                                            :class="theme.hover">
                                            <Icon icon="lucide:video" class="size-4" :class="theme.icon" /> Video
                                        </button>
                                        <button @click="triggerFileSelect('document'); showAttachMenu = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm rounded-lg"
                                            :class="theme.hover">
                                            <Icon icon="lucide:file-text" class="size-4" :class="theme.icon" /> Document
                                        </button>
                                        <button @click="openAdPicker(); showAttachMenu = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm rounded-lg"
                                            :class="theme.hover">
                                            <Icon icon="lucide:shopping-bag" class="size-4" :class="theme.icon" />
                                            Product
                                        </button>
                                    </div>
                                </div>
                                <input ref="messageInputRef" v-model="newMessage" type="text"
                                    placeholder="Type a message..."
                                    class="flex-1 px-4 py-2.5 rounded-2xl focus:outline-none text-base"
                                    :class="theme.input" @keyup.enter="sendMessage" />
                                <button type="submit" @click="sendMessage"
                                    class="size-11 text-white rounded-full disabled:opacity-50 flex items-center justify-center"
                                    :class="theme.button" :disabled="!newMessage.trim() || isSending">
                                    <Icon v-if="!isSending" icon="lucide:send" class="size-4" />
                                    <Icon v-else icon="lucide:loader-2" class="size-4 animate-spin" />
                                </button>
                            </div>
                            <button v-if="selectedFiles.length" @click="sendFiles" :disabled="uploading"
                                class="mt-2 w-full text-white rounded-xl py-2 text-sm font-medium"
                                :class="theme.button">
                                Send {{ selectedFiles.length }} file(s)
                            </button>
                        </div>
                    </div>

                    <!-- No conversation selected state -->
                    <div v-else class="h-full w-full flex flex-col items-center justify-center p-6" :class="theme.card">
                        <div class="size-24 rounded-full flex items-center justify-center mb-4" :class="theme.bgLight">
                            <Icon icon="lucide:message-circle" class="size-12" :class="theme.textMuted" />
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

        <!-- Modals -->
        <div v-if="contextMenu.show" class="fixed z-50 rounded-lg shadow-xl border py-1 min-w-[180px]"
            :class="[theme.card, theme.border]" :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }">
            <button @click="deleteMessage"
                class="w-full px-4 py-2.5 text-left text-sm flex items-center gap-2 text-red-600" :class="theme.hover">
                <Icon icon="lucide:trash-2" class="size-4" />
                Delete Message
            </button>
        </div>

        <div v-if="isOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="rounded-2xl max-w-md w-full p-6 shadow-xl" :class="theme.card">
                <div class="flex items-center gap-3 mb-4">
                    <Icon v-if="options.icon" :icon="options.icon" class="size-6 text-red-500" />
                    <h3 class="text-lg font-semibold" :class="theme.text">{{ options.title }}</h3>
                </div>
                <p class="mb-6" :class="theme.textMuted">{{ options.description }}</p>
                <div class="flex gap-3 justify-end">
                    <button @click="onCancel" class="px-4 py-2 border rounded-xl transition-colors"
                        :class="[theme.border, theme.hover]">
                        {{ options.cancelText }}
                    </button>
                    <button @click="onConfirm"
                        class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors">
                        {{ options.confirmText }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="showPreviewModal" class="fixed inset-0 bg-black/90 flex items-center justify-center z-50 p-4"
            @click.self="closePreview">
            <div class="relative max-w-full max-h-full">
                <button @click="closePreview" class="absolute -top-10 right-0 text-white hover:text-gray-300">
                    <Icon icon="lucide:x" class="size-8" />
                </button>
                <img v-if="previewItem?.type === 'image'" :src="previewItem.url"
                    class="max-w-full max-h-[90vh] object-contain" />
                <video v-else-if="previewItem?.type === 'video'" controls class="max-w-full max-h-[90vh]"
                    :src="previewItem.url"></video>
                <div v-else class="p-6 rounded-lg" :class="theme.card">
                    <p class="text-lg" :class="theme.text">Document: {{ previewItem?.name }}</p>
                    <a :href="previewItem.url" target="_blank" class="underline" :class="theme.textAccent">Open</a>
                </div>
            </div>
        </div>
    </OlxLayout>
    <AdPickerModal v-model="showAdPicker" :conversation-id="conversation.id" />
</template>

<style scoped>
/* Force 16px on all mobile inputs to prevent zoom */
@media (max-width: 768px) {

    input,
    textarea,
    .chat-input {
        font-size: 16px !important;
    }
}

/* Prevent horizontal overflow on mobile */
@media (max-width: 768px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .overflow-x-auto::-webkit-scrollbar {
        display: none;
    }

    * {
        max-width: 100%;
        box-sizing: border-box;
    }

    /* Ensure flex children can shrink */
    .flex-col {
        min-height: 0;
    }
}

/* Custom scrollbar */
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

/* Smooth animations */
* {
    transition: all 0.2s ease;
}

@keyframes slideIn {
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
    animation: slideIn 0.2s ease-out;
}

.cursor-context-menu {
    cursor: context-menu;
}
</style>