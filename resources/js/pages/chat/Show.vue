<script setup>
import { ref, onMounted, onUnmounted, watch, computed, nextTick } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import { Icon } from '@iconify/vue'
import { usePage } from '@inertiajs/vue3'
import { useAlertDialog } from '@/composables/useAlertDialog'
import AdPickerModal from './_partials/AdPickerModal.vue'
import axios from 'axios'

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

// Selection mode state
const selectionMode = ref(false)
const selectedConversations = ref([])

// File upload states
const selectedFiles = ref([])
const previewUrls = ref([])
const uploading = ref(false)
const uploadProgress = ref({})
const showPreviewModal = ref(false)
const previewItem = ref(null)

// Alert dialog composable
const { isOpen, options, show, onConfirm, onCancel } = useAlertDialog()

// Dummy messages for quick replies
const dummyMessages = [
    { text: "Hi, is this still available?", icon: "lucide:help-circle", color: "brand-blue-light" },
    { text: "What's your best price?", icon: "lucide:tag", color: "brand-blue-light" },
    { text: "Can you deliver?", icon: "lucide:truck", color: "brand-blue-light" },
    { text: "I'd like to see it in person", icon: "lucide:eye", color: "brand-blue-light" },
    { text: "Is the price negotiable?", icon: "lucide:coins", color: "brand-blue-light" },
    { text: "When can we meet?", icon: "lucide:calendar", color: "brand-blue-light" }
]

useForceTheme('light');

// Filter conversations
const filteredConversations = computed(() => {
    if (!searchQuery.value) return conversationsList.value
    return conversationsList.value.filter(conv => {
        const name = conv.seller_id === page.props.auth.user.id
            ? conv.buyer.name
            : conv.seller.name
        return name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            conv.product?.ad_title?.toLowerCase().includes(searchQuery.value.toLowerCase())
    })
})

// Selection mode functions
const toggleSelectionMode = () => {
    selectionMode.value = !selectionMode.value
    if (!selectionMode.value) {
        selectedConversations.value = []
    }
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

    // Delete each conversation
    for (const convId of selectedConversations.value) {
        await router.delete(route('chat.conversation.destroy', convId), {
            preserveState: true,
            preserveScroll: true,
            onError: (error) => {
                console.error('Failed to delete conversation', error)
            }
        })
    }

    // Update conversations list by filtering out deleted ones
    conversationsList.value = conversationsList.value.filter(
        conv => !selectedConversations.value.includes(conv.id)
    )

    // If the current conversation was deleted, reset
    if (props.conversation && selectedConversations.value.includes(props.conversation.id)) {
        // Navigate to chat index
        router.visit(route('chat.index'))
    }

    // Exit selection mode
    toggleSelectionMode()
}

const cancelSelection = () => {
    toggleSelectionMode()
}

// Scroll to bottom
const scrollToBottom = async () => {
    await nextTick()
    if (messagesEnd.value) {
        messagesEnd.value.scrollIntoView({ behavior: 'smooth', block: 'end' })
    }
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
                messagesList.value = messagesList.value.filter(m => m.id !== e.messageId)
            })

        if (window.innerWidth < 768) {
            showMobileChat.value = true
            showMobileSidebar.value = false
        }
    }

    scrollToBottom()

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
            .listen('.message.deleted', (e) => {
                messagesList.value = messagesList.value.filter(m => m.id !== e.messageId)
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
        router.delete(route('chat.message.delete', contextMenu.value.message.id), {
            preserveScroll: true,
        })
    }

    contextMenu.value.show = false
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

const goBackToSidebar = () => {
    showMobileSidebar.value = true
    showMobileChat.value = false
}

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

const getAvatarColor = () => 'bg-brand-blue/80'

const getInitials = (name) => {
    return name?.charAt(0).toUpperCase() || '?'
}

const groupMessagesByDate = (messages) => {
    const groups = {}
    messages.forEach(message => {
        const date = formatDate(message.created_at)
        if (!groups[date]) {
            groups[date] = []
        }
        groups[date].push(message)
    })
    return groups
}

// File upload functions
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
        const MAX_SIZE = 10 * 1024 * 1024; // 10 MB
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
            // Document
            previewUrls.value.push({ url: null, type: 'document', file, name: file.name })
        }
    }
}

const removeFile = (index) => {
    if (previewUrls.value[index]?.url) {
        URL.revokeObjectURL(previewUrls.value[index].url)
    }
    selectedFiles.value.splice(index, 1)
    previewUrls.value.splice(index, 1)
    // Rebuild uploadProgress for remaining files (optional)
    const newProgress = {}
    selectedFiles.value.forEach(file => {
        if (uploadProgress.value[file.name]) {
            newProgress[file.name] = uploadProgress.value[file.name]
        }
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
            // The broadcast will add the message to messagesList automatically
        } catch (error) {
            console.error('Upload failed', error)
        }
    }
    // Clear selected files
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

// Get the private file URL using message ID
const getFileUrl = (messageId) => {
    return `/chat/file/${messageId}`
}

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
    } catch (e) {
        console.error(e)
    }

    loadingAds.value = false
}

const sendAd = (ad) => {
    router.post('/chat/send', {
        conversation_id: props.conversation.id,
        type: 'ad',
        ad_id: ad.id
    }, {
        preserveScroll: true
    })

    showAdPicker.value = false
}
</script>

<template>
    <OlxLayout :hide-search-bar="true">
        <div class="h-[calc(100vh-73px)] bg-gray-100 sm:max-w-5xl  mx-auto  overflow-hidden">
            <div class="h-full max-w-[1600px] mx-auto">
                <!-- Desktop Layout -->
                <div class="hidden md:flex h-full bg-white shadow-xl">
                    <!-- Conversations Sidebar -->
                    <div class="w-96 border-r border-gray-200 flex flex-col bg-white">
                        <!-- Sidebar Header -->
                        <div class="p-5 border-b border-gray-200 bg-white">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-2xl font-bold text-gray-800">Chats</h2>
                                <div class="flex items-center gap-2">
                                    <!-- Delete button when in selection mode -->
                                    <button v-if="selectionMode" @click="deleteSelectedConversations"
                                        :disabled="selectedConversations.length === 0"
                                        class="p-2 hover:bg-gray-100 rounded-full transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        <Icon icon="lucide:trash-2" class="size-5 text-red-500" />
                                    </button>
                                    <!-- Cancel selection mode -->
                                    <button v-if="selectionMode" @click="cancelSelection"
                                        class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                                        <Icon icon="lucide:x" class="size-5 text-gray-600" />
                                    </button>
                                    <!-- Pen icon to toggle selection mode -->
                                    <button v-else @click="toggleSelectionMode"
                                        class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                                        <Icon icon="lucide:edit-2" class="size-5 text-gray-600" />
                                    </button>
                                </div>
                            </div>

                            <!-- Search Bar -->
                            <div class="relative">
                                <Icon icon="lucide:search"
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 size-4" />
                                <input v-model="searchQuery" type="text" placeholder="Search or start new chat"
                                    class="w-full pl-9 pr-4 py-2.5 bg-gray-100 border-0 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:bg-white transition-all" />
                            </div>
                        </div>

                        <!-- Conversations List -->
                        <div class="flex-1 overflow-y-auto">
                            <div v-if="!filteredConversations.length"
                                class="flex flex-col items-center justify-center h-full p-8">
                                <Icon icon="lucide:inbox" class="size-12 text-gray-300 mb-3" />
                                <p class="text-gray-500 text-sm">No conversations found</p>
                            </div>

                            <div v-for="conv in filteredConversations" :key="conv.id"
                                @click="selectionMode ? toggleSelectConversation(conv.id) : router.visit(`/chat/${conv.id}`)"
                                class="group flex items-start gap-3 px-5 py-3 hover:bg-gray-50 transition-all cursor-pointer border-b border-gray-100"
                                :class="[
                                    conv.id === conversation?.id && !selectionMode ? 'bg-gray-100' : '',
                                    selectionMode && selectedConversations.includes(conv.id) ? 'bg-brand-blue/5' : ''
                                ]">
                                <!-- Checkbox for selection mode -->
                                <div v-if="selectionMode" class="flex-shrink-0 pt-2">
                                    <input type="checkbox" :checked="selectedConversations.includes(conv.id)"
                                        @click.stop @change="toggleSelectConversation(conv.id)"
                                        class="w-4 h-4 rounded border-gray-300 text-brand-blue focus:ring-brand-blue" />
                                </div>

                                <!-- Avatar -->
                                <div class="relative flex-shrink-0">
                                    <div :class="[
                                        'size-12 rounded-full flex items-center justify-center text-white font-semibold text-lg shadow-sm',
                                        getAvatarColor()
                                    ]">
                                        {{ getInitials(conv.seller_id === page.props.auth.user.id ? conv.buyer.name :
                                            conv.seller.name) }}
                                    </div>
                                    <div
                                        class="absolute bottom-0 right-0 size-3 bg-green-500 rounded-full border-2 border-white">
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-semibold text-gray-900 truncate">
                                            {{ conv.seller_id === page.props.auth.user.id ? conv.buyer.name :
                                                conv.seller.name }}
                                        </h3>
                                        <span class="text-xs text-gray-400 whitespace-nowrap ml-2">
                                            {{ formatTime(getLastMessage(conv)?.created_at || conv.created_at) }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <p class="text-sm text-gray-500 truncate flex-1">
                                            <span v-if="getLastMessage(conv)?.sender_id === page.props.auth.user.id"
                                                class="text-gray-400">
                                                You:
                                            </span>
                                            {{ getLastMessage(conv)?.body || 'No messages yet' }}
                                        </p>
                                        <span v-if="getUnreadCount(conv) > 0"
                                            class="bg-brand-blue text-white text-xs font-medium px-2 py-0.5 rounded-full ml-2">
                                            {{ getUnreadCount(conv) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Area -->
                    <div class="flex-1 flex flex-col bg-gray-50">
                        <!-- Chat Header -->
                        <div v-if="conversation"
                            class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm">
                            <Link :href="route('user.profile', otherUser?.id)"
                                class="flex items-center cursor-pointer gap-3">
                                <div :class="[
                                    'size-12 rounded-full flex items-center justify-center text-white font-semibold text-lg shadow-sm',
                                    getAvatarColor()
                                ]">
                                    {{ otherUser?.name?.charAt(0).toUpperCase() || '?' }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 text-lg">{{ otherUser?.name }}</h3>
                                    <p class="text-xs text-gray-500 flex items-center gap-1">
                                        <span class="inline-block size-2 bg-green-500 rounded-full"></span>
                                        Online
                                    </p>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="bg-white border-b border-gray-200 px-6 py-5">
                            <h3 class="text-gray-500 text-center">Select a conversation to start messaging</h3>
                        </div>

                        <!-- Messages Area -->
                        <div v-if="conversation" class="flex-1 overflow-y-auto px-6 py-6" ref="messagesContainer">
                            <div v-for="(messages, date) in groupMessagesByDate(messagesList)" :key="date">
                                <div class="flex justify-center mb-4">
                                    <span class="bg-gray-200 text-gray-600 text-xs px-3 py-1 rounded-full">
                                        {{ date }}
                                    </span>
                                </div>

                                <div v-for="message in messages" :key="message.id"
                                    :class="['flex mb-4', message.sender_id === page.props.auth.user.id ? 'justify-end' : 'justify-start']">

                                    <div class="flex items-end gap-2 max-w-[70%]">
                                        <div v-if="message.sender_id !== page.props.auth.user.id"
                                            :class="['size-8 rounded-full flex-shrink-0', getAvatarColor()]">
                                        </div>

                                        <div @contextmenu.prevent="handleRightClick($event, message)"
                                            class="relative cursor-context-menu">
                                            <div :class="[
                                                'rounded-2xl px-4 py-2.5 break-words shadow-sm',
                                                message.type === 'text'
                                                    ? (message.sender_id === page.props.auth.user.id
                                                        ? 'bg-brand-teal text-white rounded-br-sm'
                                                        : 'bg-white text-gray-800 rounded-bl-sm border border-gray-200')
                                                    : '' // no background for file messages
                                            ]">
                                                <!-- File message -->
                                                <template v-if="message.type === 'file'">
                                                    <!-- Image -->
                                                    <img v-if="message.body.match(/\.(jpg|jpeg|png|gif|webp)$/i)"
                                                        :src="getFileUrl(message.id)"
                                                        class="max-w-[200px] rounded-lg cursor-pointer hover:opacity-90 transition shadow-sm border border-gray-200"
                                                        @click="openPreview({ url: getFileUrl(message.id), type: 'image', name: message.body.split('/').pop() })" />
                                                    <!-- Video -->
                                                    <video v-else-if="message.body.match(/\.(mp4|mov|avi|webm)$/i)"
                                                        controls
                                                        class="max-w-[250px] rounded-lg shadow-sm border border-gray-200">
                                                        <source :src="getFileUrl(message.id)" />
                                                    </video>
                                                    <!-- Document -->
                                                    <div v-else
                                                        class="flex items-center gap-2 p-2 bg-gray-100 rounded-lg">
                                                        <Icon icon="lucide:file-text" class="size-6 text-gray-600" />
                                                        <span class="text-sm truncate max-w-[150px]">
                                                            {{ message.body.split('/').pop() }}
                                                        </span>
                                                        <a :href="getFileUrl(message.id)" target="_blank"
                                                            class="text-brand-blue hover:underline text-xs ml-2">
                                                            Download
                                                        </a>
                                                    </div>
                                                </template>
                                                <!-- Text message -->
                                                <p v-else class="text-sm leading-relaxed">{{ message.body }}</p>
                                            </div>
                                            <div :class="[
                                                'text-xs text-gray-400 mt-1 flex items-center gap-1',
                                                message.sender_id === page.props.auth.user.id ? 'justify-end' : 'justify-start'
                                            ]">
                                                {{ formatTime(message.created_at) }}
                                                <Icon v-if="message.sender_id === page.props.auth.user.id"
                                                    :icon="message.is_read ? 'lucide:check-check' : 'lucide:check'"
                                                    :class="message.is_read ? 'text-brand-blue' : 'text-gray-400'"
                                                    class="size-3" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div ref="messagesEnd"></div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="flex-1 flex items-center justify-center">
                            <div class="text-center">
                                <div
                                    class="size-24 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <Icon icon="lucide:message-circle" class="size-12 text-gray-400" />
                                </div>
                                <p class="text-gray-500 font-medium">No conversation selected</p>
                                <p class="text-sm text-gray-400 mt-1">Choose a chat from the sidebar to start messaging
                                </p>
                            </div>
                        </div>

                        <!-- Input Area Desktop -->
                        <div v-if="conversation" class="bg-white border-t border-gray-200 p-4">
                            <!-- File Preview Area -->
                            <div v-if="previewUrls.length" class="mb-3 flex flex-wrap gap-2">
                                <div v-for="(item, idx) in previewUrls" :key="idx"
                                    class="relative group rounded-lg overflow-hidden border border-gray-200 bg-gray-50 shadow-sm">
                                    <div v-if="item.type === 'image'" class="w-20 h-20 bg-cover bg-center"
                                        :style="{ backgroundImage: `url(${item.url})` }">
                                    </div>
                                    <div v-else-if="item.type === 'video'"
                                        class="w-20 h-20 bg-gray-800 flex items-center justify-center">
                                        <Icon icon="lucide:video" class="size-8 text-white" />
                                    </div>
                                    <div v-else
                                        class="w-20 h-20 bg-gray-100 flex flex-col items-center justify-center p-2">
                                        <Icon icon="lucide:file-text" class="size-8 text-gray-500" />
                                        <span class="text-xs text-gray-600 truncate w-full text-center">
                                            {{ item.name.slice(0, 10) }}
                                        </span>
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

                            <!-- Quick Replies -->
                            <div class="flex gap-2 mb-3 overflow-x-auto pb-2">
                                <button v-for="(dummy, index) in dummyMessages.slice(0, 4)" :key="index"
                                    @click="sendDummyMessage(dummy.text)"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-all hover:scale-105 bg-brand-blue-light text-brand-blue">
                                    <Icon :icon="dummy.icon" class="size-3" />
                                    <span>{{ dummy.text }}</span>
                                </button>
                            </div>

                            <!-- Message Input with File Buttons -->
                            <div class="flex flex-col gap-2">
                                <div class="flex gap-2 items-center">

                                    <!-- Attachment Button -->
                                    <div class="relative">
                                        <button type="button" @click="showAttachMenu = !showAttachMenu"
                                            class="p-2 hover:bg-gray-100 rounded-full transition-colors" title="Attach">
                                            <Icon icon="lucide:paperclip" class="size-5 text-gray-500" />
                                        </button>

                                        <!-- Dropdown -->
                                        <div v-if="showAttachMenu"
                                            class="absolute bottom-12 left-0 bg-white shadow-lg border rounded-xl p-2 w-40 z-50">
                                            <button @click="triggerFileSelect('image'); showAttachMenu = false"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-gray-100 rounded-lg">
                                                <Icon icon="lucide:image" class="size-4 text-brand-blue" />
                                                Image
                                            </button>

                                            <button @click="triggerFileSelect('video'); showAttachMenu = false"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-gray-100 rounded-lg">
                                                <Icon icon="lucide:video" class="size-4 text-brand-blue" />
                                                Video
                                            </button>

                                            <button @click="triggerFileSelect('document'); showAttachMenu = false"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-gray-100 rounded-lg">
                                                <Icon icon="lucide:file-text" class="size-4 text-brand-blue" />
                                                Document
                                            </button>
                                            <button @click="openAdPicker(); showAttachMenu = false"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-gray-100 rounded-lg">
                                                <Icon icon="lucide:shopping-bag" class="size-4 text-brand-blue" />
                                                Product
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Emoji -->
                                    <button type="button" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                                        <Icon icon="lucide:smile" class="size-5 text-gray-500" />
                                    </button>

                                    <!-- Message Input -->
                                    <div class="flex-1 flex gap-2">
                                        <input v-model="newMessage" type="text" placeholder="Type a message..."
                                            class="flex-1 px-4 py-2.5 border-0 bg-gray-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:bg-white transition-all text-sm"
                                            @keyup.enter="sendMessage" />

                                        <button type="submit" @click="sendMessage"
                                            class="size-10 bg-brand-blue text-white rounded-full hover:bg-brand-blue-dark transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center shadow-sm"
                                            :disabled="!newMessage.trim()">
                                            <Icon icon="lucide:send" class="size-4" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Send Files Button -->
                                <button v-if="selectedFiles.length" @click="sendFiles" :disabled="uploading"
                                    class="bg-brand-teal hover:bg-brand-teal-dark text-white rounded-xl py-2 text-sm font-medium transition disabled:opacity-50">
                                    <span v-if="uploading">Uploading...</span>
                                    <span v-else>Send {{ selectedFiles.length }} file(s)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Layout -->
                <div class="md:hidden h-full">
                    <!-- Mobile Conversations List -->
                    <div v-if="showMobileSidebar || (!showMobileChat && !conversation)"
                        class="h-full bg-white flex flex-col">
                        <div class="p-4 border-b border-gray-200 bg-white">
                            <div class="flex items-center justify-between mb-3">
                                <h2 class="text-2xl font-bold text-gray-800">Chats</h2>
                                <div class="flex items-center gap-2">
                                    <!-- Delete button when in selection mode -->
                                    <button v-if="selectionMode" @click="deleteSelectedConversations"
                                        :disabled="selectedConversations.length === 0"
                                        class="p-2 hover:bg-gray-100 rounded-full transition-colors disabled:opacity-50">
                                        <Icon icon="lucide:trash-2" class="size-5 text-red-500" />
                                    </button>
                                    <!-- Cancel selection mode -->
                                    <button v-if="selectionMode" @click="cancelSelection"
                                        class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                                        <Icon icon="lucide:x" class="size-5 text-gray-600" />
                                    </button>
                                    <!-- Pen icon to toggle selection mode -->
                                    <button v-else @click="toggleSelectionMode"
                                        class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                                        <Icon icon="lucide:edit-2" class="size-5 text-gray-600" />
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <Icon icon="lucide:search"
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 size-4" />
                                <input v-model="searchQuery" type="text" placeholder="Search"
                                    class="w-full pl-9 pr-4 py-2.5 bg-gray-100 border-0 rounded-xl text-sm focus:outline-none" />
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto">
                            <div v-if="!filteredConversations.length"
                                class="flex flex-col items-center justify-center h-full p-8">
                                <Icon icon="lucide:inbox" class="size-12 text-gray-300 mb-3" />
                                <p class="text-gray-500 text-sm">No conversations</p>
                            </div>

                            <button v-for="conv in filteredConversations" :key="conv.id"
                                @click="selectionMode ? toggleSelectConversation(conv.id) : router.visit(`/chat/${conv.id}`)"
                                class="w-full flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-all border-b border-gray-100 text-left"
                                :class="[
                                    selectionMode && selectedConversations.includes(conv.id) ? 'bg-brand-blue/5' : ''
                                ]">
                                <!-- Checkbox for selection mode -->
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
                                            conv.seller.name) }}
                                    </div>
                                    <div
                                        class="absolute bottom-0 right-0 size-3 bg-green-500 rounded-full border-2 border-white">
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-semibold text-gray-900 truncate">
                                            {{ conv.seller_id === page.props.auth.user.id ? conv.buyer.name :
                                                conv.seller.name }}
                                        </h3>
                                        <span class="text-xs text-gray-400 whitespace-nowrap ml-2">
                                            {{ formatTime(conv.last_message_at || conv.created_at) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ conv.last_message?.body || 'No messages yet' }}
                                    </p>
                                </div>
                                <span v-if="getUnreadCount(conv) > 0"
                                    class="bg-brand-blue text-white text-xs font-medium px-2 py-0.5 rounded-full self-center">
                                    {{ getUnreadCount(conv) }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Chat View -->
                    <div v-else-if="showMobileChat && conversation" class="h-full bg-gray-50 flex flex-col">
                        <!-- Mobile Chat Header -->
                        <div class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
                            <button @click="goBackToSidebar" class="p-1 -ml-1">
                                <Icon icon="lucide:arrow-left" class="size-6 text-gray-600" />
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
                                    <h3 class="font-semibold text-gray-900">{{ otherUser?.name }}</h3>
                                    <p class="text-xs text-green-500">Online</p>
                                </div>
                            </Link>
                        </div>

                        <!-- Mobile Messages -->
                        <div class="flex-1 overflow-y-auto p-4 space-y-4">
                            <div v-for="message in messagesList" :key="message.id"
                                :class="['flex', message.sender_id === page.props.auth.user.id ? 'justify-end' : 'justify-start']">
                                <div class="max-w-[85%]">
                                    <div :class="[
                                        'rounded-2xl px-4 py-2.5 break-words shadow-sm',
                                        message.type === 'text'
                                            ? (message.sender_id === page.props.auth.user.id
                                                ? 'bg-brand-teal text-white rounded-br-sm'
                                                : 'bg-white text-gray-800 rounded-bl-sm border border-gray-200')
                                            : '' // no background for file messages
                                    ]">
                                        <!-- File message -->
                                        <template v-if="message.type === 'file'">
                                            <img v-if="message.body.match(/\.(jpg|jpeg|png|gif|webp)$/i)"
                                                :src="getFileUrl(message.id)"
                                                class="max-w-[200px] rounded-lg cursor-pointer shadow-sm border border-gray-200"
                                                @click="openPreview({ url: getFileUrl(message.id), type: 'image', name: message.body.split('/').pop() })" />
                                            <video v-else-if="message.body.match(/\.(mp4|mov|avi|webm)$/i)" controls
                                                class="max-w-[250px] rounded-lg shadow-sm border border-gray-200">
                                                <source :src="getFileUrl(message.id)" />
                                            </video>
                                            <div v-else class="flex items-center gap-2">
                                                <Icon icon="lucide:file-text" class="size-5" />
                                                <span class="text-sm truncate">{{ message.body.split('/').pop()
                                                }}</span>
                                                <a :href="getFileUrl(message.id)" target="_blank"
                                                    class="text-brand-blue underline text-xs">Download</a>
                                            </div>
                                        </template>
                                        <p v-else>{{ message.body }}</p>
                                    </div>
                                    <div :class="[
                                        'text-xs text-gray-400 mt-1 flex items-center gap-1',
                                        message.sender_id === page.props.auth.user.id ? 'justify-end' : 'justify-start'
                                    ]">
                                        {{ formatTime(message.created_at) }}
                                    </div>
                                </div>
                            </div>
                            <div ref="messagesEnd"></div>
                        </div>

                        <!-- Mobile Input -->
                        <div class="bg-white border-t border-gray-200 p-3">

                            <!-- Quick Replies (mobile) -->
                            <div class="flex gap-2 mb-3 overflow-x-auto pb-2">
                                <button v-for="(dummy, index) in dummyMessages.slice(0, 4)" :key="index"
                                    @click="sendDummyMessage(dummy.text)"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-all hover:scale-105 bg-brand-blue-light text-brand-blue">
                                    <Icon :icon="dummy.icon" class="size-3" />
                                    <span>{{ dummy.text }}</span>
                                </button>
                            </div>

                            <!-- File Preview (mobile) -->
                            <div v-if="previewUrls.length" class="mb-2 flex flex-wrap gap-2">
                                <div v-for="(item, idx) in previewUrls" :key="idx"
                                    class="relative w-16 h-16 rounded-lg overflow-hidden border shadow-sm">
                                    <img v-if="item.type === 'image'" :src="item.url"
                                        class="w-full h-full object-cover" />
                                    <div v-else-if="item.type === 'video'"
                                        class="w-full h-full bg-gray-800 flex items-center justify-center">
                                        <Icon icon="lucide:video" class="size-6 text-white" />
                                    </div>
                                    <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center">
                                        <Icon icon="lucide:file-text" class="size-6 text-gray-500" />
                                    </div>
                                    <button @click="removeFile(idx)"
                                        class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-0.5">
                                        <Icon icon="lucide:x" class="size-3" />
                                    </button>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <!-- Attachment Button -->
                                <div class="relative">
                                    <button type="button" @click="showAttachMenu = !showAttachMenu"
                                        class="p-2 hover:bg-gray-100 rounded-full transition-colors" title="Attach">
                                        <Icon icon="lucide:paperclip" class="size-5 text-gray-500" />
                                    </button>

                                    <!-- Dropdown -->
                                    <div v-if="showAttachMenu"
                                        class="absolute bottom-12 left-0 bg-white shadow-lg border rounded-xl p-2 w-40 z-50">
                                        <button @click="triggerFileSelect('image'); showAttachMenu = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-gray-100 rounded-lg">
                                            <Icon icon="lucide:image" class="size-4 text-brand-blue" />
                                            Image
                                        </button>

                                        <button @click="triggerFileSelect('video'); showAttachMenu = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-gray-100 rounded-lg">
                                            <Icon icon="lucide:video" class="size-4 text-brand-blue" />
                                            Video
                                        </button>

                                        <button @click="triggerFileSelect('document'); showAttachMenu = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-gray-100 rounded-lg">
                                            <Icon icon="lucide:file-text" class="size-4 text-brand-blue" />
                                            Document
                                        </button>
                                        <button @click="openAdPicker(); showAttachMenu = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-gray-100 rounded-lg">
                                            <Icon icon="lucide:shopping-bag" class="size-4 text-brand-blue" />
                                            Product
                                        </button>
                                    </div>
                                </div>
                                <input v-model="newMessage" type="text" placeholder="Type a message..."
                                    class="flex-1 px-4 py-2.5 bg-gray-100 rounded-2xl focus:outline-none text-sm" />
                                <button type="submit" @click="sendMessage"
                                    class="size-11 bg-brand-blue text-white rounded-full hover:bg-brand-blue-dark disabled:opacity-50 flex items-center justify-center"
                                    :disabled="!newMessage.trim()">
                                    <Icon icon="lucide:send" class="size-4" />
                                </button>
                            </div>
                            <button v-if="selectedFiles.length" @click="sendFiles" :disabled="uploading"
                                class="mt-2 w-full bg-brand-teal hover:bg-brand-teal-dark text-white rounded-xl py-2 text-sm font-medium">
                                Send {{ selectedFiles.length }} file(s)
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Empty State -->
                    <div v-else class="h-full bg-white flex flex-col items-center justify-center p-6">
                        <div class="size-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <Icon icon="lucide:message-circle" class="size-12 text-gray-400" />
                        </div>
                        <p class="text-lg font-medium text-gray-700 mb-2">No conversation selected</p>
                        <p class="text-sm text-gray-500 mb-6 text-center">Choose a conversation to start messaging</p>
                        <button @click="showMobileSidebar = true"
                            class="px-6 py-3 bg-brand-blue text-white rounded-xl hover:bg-brand-blue-dark font-medium shadow-sm">
                            View Conversations
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Context Menu -->
        <div v-if="contextMenu.show" class="fixed z-50 bg-white rounded-lg shadow-xl border py-1 min-w-[180px]"
            :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }">
            <button @click="deleteMessage"
                class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 flex items-center gap-2 text-red-600">
                <Icon icon="lucide:trash-2" class="size-4" />
                Delete Message
            </button>
        </div>

        <!-- Alert Dialog -->
        <div v-if="isOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <Icon v-if="options.icon" :icon="options.icon" class="size-6 text-red-500" />
                    <h3 class="text-lg font-semibold">{{ options.title }}</h3>
                </div>
                <p class="text-gray-600 mb-6">{{ options.description }}</p>
                <div class="flex gap-3 justify-end">
                    <button @click="onCancel"
                        class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                        {{ options.cancelText }}
                    </button>
                    <button @click="onConfirm"
                        class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors">
                        {{ options.confirmText }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
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
                <div v-else class="bg-white p-6 rounded-lg">
                    <p class="text-lg">Document: {{ previewItem?.name }}</p>
                    <a :href="previewItem.url" target="_blank" class="text-brand-blue underline">Open</a>
                </div>
            </div>
        </div>
    </OlxLayout>
    <AdPickerModal v-model="showAdPicker" @select="sendAd" :conversation-id="conversation.id" />
</template>

<style scoped>
.focus\:ring-brand-blue\/20:focus {
    --tw-ring-color: rgba(59, 130, 246, 0.2);
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

/* Message animations */
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

/* Quick replies hover effect */
.whitespace-nowrap:hover {
    transform: scale(1.05);
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .overflow-x-auto::-webkit-scrollbar {
        display: none;
    }
}

.cursor-context-menu {
    cursor: context-menu;
}
</style>