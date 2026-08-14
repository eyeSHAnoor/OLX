<template>
    <div class="mt-8 rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-5 lg:p-6" :class="theme.card">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg sm:text-xl font-semibold" :class="theme.text">
                Comments ({{ comments.length }})
            </h2>
        </div>

        <!-- New comment form -->
        <div v-if="$page.props.auth?.user" class="mb-6">
            <textarea v-model="newCommentText" placeholder="Write a comment..." rows="3"
                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition resize-none"
                :class="theme.input"></textarea>
            <div class="flex justify-end mt-2">
                <button @click="submitComment" :disabled="!newCommentText.trim() || submitting"
                    class="px-4 py-2 text-white rounded-lg hover:opacity-90 disabled:opacity-50 transition text-sm"
                    :class="theme.button">
                    <Icon v-if="submitting" icon="lucide:loader-2" class="size-4 animate-spin inline mr-1" />
                    Post Comment
                </button>
            </div>
        </div>
        <div v-else class="mb-6 p-4 rounded-lg text-center text-sm" :class="[theme.bgLight, theme.textMuted]">
            <Link href="/login" class="text-primary font-medium hover:underline">Sign in</Link>
            to leave a comment.
        </div>

        <!-- Comments list -->
        <div v-if="comments.length" class="space-y-4">
            <div v-for="comment in comments" :key="comment.id" class="border-b last:border-0 pb-4"
                :class="theme.border">
                <!-- Parent comment -->
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <Icon icon="lucide:user" class="size-4 text-primary" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <Link :href="route('user.profile', comment.user.id)"
                                class="font-medium text-sm hover:text-primary" :class="theme.text">
                                {{ comment.user.name }}
                            </Link>
                            <span class="text-xs" :class="theme.textMuted">{{
                                formatRelativeTime(comment.created_at)
                                }}</span>
                        </div>
                        <p class="text-sm leading-relaxed mb-2" :class="theme.text">
                            {{ comment.message }}
                        </p>

                        <!-- Actions: Like & Reply -->
                        <div class="flex items-center gap-3 text-xs">
                            <button @click="toggleLike(comment)"
                                class="flex items-center gap-1 transition hover:text-red-500" :class="theme.textMuted">
                                <Icon :icon="comment.is_liked_by_user ? 'mdi:heart' : 'lucide:heart'" class="size-3.5"
                                    :class="comment.is_liked_by_user ? 'text-red-500' : ''" />
                                <span>{{ comment.likes?.length || 0 }}</span>
                            </button>
                            <button v-if="$page.props.auth?.user" @click="toggleReplyForm(comment.id)"
                                class="transition hover:text-primary" :class="theme.textMuted">
                                Reply
                            </button>
                        </div>

                        <!-- Reply form -->
                        <div v-if="replyFormId === comment.id" class="mt-3">
                            <textarea v-model="replyText" placeholder="Write a reply..." rows="2"
                                class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition resize-none"
                                :class="theme.input"></textarea>
                            <div class="flex justify-end gap-2 mt-2">
                                <button @click="cancelReply" class="px-3 py-1 text-sm rounded transition"
                                    :class="[theme.textMuted, theme.hover]">
                                    Cancel
                                </button>
                                <button @click="submitReply(comment.id)"
                                    :disabled="!replyText.trim() || submittingReply"
                                    class="px-3 py-1 text-white rounded-lg hover:opacity-90 disabled:opacity-50 text-sm"
                                    :class="theme.button">
                                    <Icon v-if="submittingReply" icon="lucide:loader-2"
                                        class="size-3 animate-spin inline mr-1" />
                                    Reply
                                </button>
                            </div>
                        </div>

                        <!-- Nested replies -->
                        <div v-if="comment.replies?.length" class="ml-6 mt-3 space-y-3">
                            <div v-for="reply in comment.replies" :key="reply.id" class="flex gap-3">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0"
                                    :class="theme.badge">
                                    <Icon icon="lucide:user" class="size-3.5" :class="theme.textMuted" />
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <Link :href="route('user.profile', reply.user.id)"
                                            class="text-sm font-medium hover:text-primary" :class="theme.text">
                                            {{ reply.user.name }}
                                        </Link>
                                        <span class="text-xs" :class="theme.textMuted">{{
                                            formatRelativeTime(reply.created_at)
                                            }}</span>
                                    </div>
                                    <p class="text-sm leading-relaxed mb-1" :class="theme.text">
                                        {{ reply.message }}
                                    </p>
                                    <button @click="toggleLike(reply)"
                                        class="flex items-center gap-1 text-xs transition mt-1 hover:text-red-500"
                                        :class="theme.textMuted">
                                        <Icon :icon="reply.is_liked_by_user ? 'mdi:heart' : 'lucide:heart'"
                                            class="size-3" :class="reply.is_liked_by_user ? 'text-red-500' : ''" />
                                        <span>{{ reply.likes?.length || 0 }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-6 text-sm" :class="theme.textMuted">
            No comments yet. Start the conversation!
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";
import { Link } from "@inertiajs/vue3";
import { useTheme } from "@/Composables/useTheme";

// Theme
const { theme } = useTheme();

const props = defineProps({
    comments: { type: Array, default: () => [] },
    adId: { type: Number, required: true },
});

const newCommentText = ref("");
const replyText = ref("");
const replyFormId = ref(null);
const submitting = ref(false);
const submittingReply = ref(false);

// Helper: format relative time (simplified)
const formatRelativeTime = (date) => {
    const now = new Date();
    const d = new Date(date);
    const diff = Math.floor((now - d) / 1000);
    if (diff < 60) return "just now";
    if (diff < 3600) return Math.floor(diff / 60) + "m ago";
    if (diff < 86400) return Math.floor(diff / 3600) + "h ago";
    return Math.floor(diff / 86400) + "d ago";
};

// Submit a top‑level comment
const submitComment = () => {
    if (!newCommentText.value.trim() || submitting.value) return;
    submitting.value = true;

    router.post(
        route("comments.store"),
        {
            ad_id: props.adId,
            message: newCommentText.value,
            parent_id: null,
            type: "text",
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                newCommentText.value = "";
                submitting.value = false;
            },
            onError: (err) => {
                console.error(err);
                submitting.value = false;
            },
        }
    );
};

// Toggle reply form visibility
const toggleReplyForm = (commentId) => {
    replyFormId.value = replyFormId.value === commentId ? null : commentId;
    replyText.value = "";
};

const cancelReply = () => {
    replyFormId.value = null;
    replyText.value = "";
};

// Submit a reply
const submitReply = (parentId) => {
    if (!replyText.value.trim() || submittingReply.value) return;
    submittingReply.value = true;

    router.post(
        route("comments.store"),
        {
            ad_id: props.adId,
            message: replyText.value,
            parent_id: parentId,
            type: "text",
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                replyText.value = "";
                replyFormId.value = null;
                submittingReply.value = false;
            },
            onError: (err) => {
                console.error(err);
                submittingReply.value = false;
            },
        }
    );
};

// Toggle like on a comment
const toggleLike = (comment) => {
    if (!usePage().props.auth?.user) {
        router.visit("/login");
        return;
    }

    router.post(
        route("comments.like", comment.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // Optimistic update can be done here if desired
            },
        }
    );
};
</script>