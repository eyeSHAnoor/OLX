<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
    conversations: Array
})

const conversationsList = ref(props.conversations || [])

// Listen for new messages across all conversations
onMounted(() => {
    conversationsList.value.forEach(conv => {
        window.Echo.private(`conversation.${conv.id}`)
            .listen('.message.sent', (e) => {
                // Update the conversation in the list with latest message
                const index = conversationsList.value.findIndex(c => c.id === conv.id)
                if (index !== -1) {
                    conversationsList.value[index].last_message = e.message
                    conversationsList.value[index].last_message_at = e.message.created_at

                    // Move to top
                    const updated = conversationsList.value.splice(index, 1)[0]
                    conversationsList.value.unshift(updated)
                }
            })
    })
})

onUnmounted(() => {
    conversationsList.value.forEach(conv => {
        window.Echo.leave(`conversation.${conv.id}`)
    })
})

const formatTime = (date) => {
    const messageDate = new Date(date)
    const today = new Date()

    if (messageDate.toDateString() === today.toDateString()) {
        return messageDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }
    return messageDate.toLocaleDateString()
}
</script>

<template>
    <OlxLayout>
        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <h1 class="text-2xl font-bold mb-6">My Chats</h1>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- Empty State -->
                <div v-if="!conversationsList.length" class="text-center py-16">
                    <Icon icon="lucide:message-circle" class="size-16 text-gray-300 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-gray-700 mb-2">No conversations yet</h3>
                    <p class="text-muted-foreground mb-4">Start chatting with sellers when you're interested in their
                        products</p>
                    <Link href="/"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90">
                        <Icon icon="lucide:shopping-bag" class="size-5" />
                        Browse Ads
                    </Link>
                </div>

                <!-- Conversations List -->
                <div v-else class="divide-y">
                    <Link v-for="conv in conversationsList" :key="conv.id" :href="`/chat/${conv.id}`"
                        class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">

                        <!-- User Avatar -->
                        <div class="relative flex-shrink-0">
                            <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <Icon icon="lucide:user" class="size-6 text-primary" />
                            </div>
                            <!-- Online Status (optional) -->
                            <div
                                class="absolute bottom-0 right-0 size-3 bg-green-500 rounded-full border-2 border-white">
                            </div>
                        </div>

                        <!-- Conversation Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="font-semibold truncate">
                                    {{ conv.seller_id === $page.props.auth.user.id ? conv.buyer.name : conv.seller.name
                                    }}
                                </h3>
                                <span class="text-xs text-muted-foreground">
                                    {{ formatTime(conv.last_message_at || conv.created_at) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <p class="text-sm text-muted-foreground truncate max-w-[200px]">
                                    <span v-if="conv.last_message" class="flex items-center gap-1">
                                        <Icon v-if="conv.last_message.sender_id === $page.props.auth.user.id"
                                            icon="lucide:check-check"
                                            :class="conv.last_message.is_read ? 'text-green-500' : 'text-gray-400'"
                                            class="size-4" />
                                        {{ conv.last_message.body }}
                                    </span>
                                    <span v-else class="text-gray-400">No messages yet</span>
                                </p>

                                <!-- Product Info -->
                                <span class="text-xs bg-gray-100 px-2 py-1 rounded-full truncate max-w-[150px]">
                                    {{ conv.product?.ad_title || 'Product' }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </OlxLayout>
</template>