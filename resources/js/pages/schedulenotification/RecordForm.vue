<!-- resources/js/Pages/Admin/Notifications/Create.vue -->
<script setup lang="ts">
import { usePage, router, useForm, Head } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useBreadcrumb } from "@/composables/useBreadcrumb";
import { useAlertDialog } from "@/composables/useAlertDialog";

defineOptions({ layout: Layout });

interface PageProps {
    notification?: App.Data.ScheduledNotificationData;
}

const page = usePage<PageProps>();
const notification = computed(() => page.props.notification);
const isEditing = computed(() => !!notification.value);

// Form setup
const form = useForm({
    title: notification.value?.title || "",
    message: notification.value?.message || "",
    url: notification.value?.url || "",
    scheduled_at: notification.value?.scheduled_at || "",
});

// Breadcrumbs
const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: "Home", href: "/dashboard" },
        { label: "Notifications", href: route("scheduled-notifications.index") },
        {
            label: isEditing.value ? "Edit Notification" : "Create Notification",
            href: route("scheduled-notifications.create"),
        },
    ]);
});

// Submit form
const submit = () => {
    if (isEditing.value) {
        form.put(route("scheduled-notifications.update", notification.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route("scheduled-notifications.index"));
            },
        });
    } else {
        form.post(route("scheduled-notifications.store"), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route("scheduled-notifications.index"));
            },
        });
    }
};

// Delete notification
const alert = useAlertDialog();
const destroy = async () => {
    if (!notification.value?.id) return;

    const confirmed = await alert.show({
        title: "Delete Notification",
        description: `Are you sure you want to delete "${notification.value.title}"?`,
        confirmText: "Yes, Delete",
        cancelText: "Cancel",
    });

    if (confirmed) {
        form.delete(route("scheduled-notifications.destroy", notification.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route("scheduled-notifications.index"));
            },
        });
    }
};

// Get minimum datetime (now)
const getMinDateTime = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");
    const hours = String(now.getHours()).padStart(2, "0");
    const minutes = String(now.getMinutes()).padStart(2, "0");
    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

// Format date for display
const formatDate = (date: string) => {
    if (!date) return "";
    return new Date(date).toLocaleString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <AppContainer>

        <Head :title="isEditing ? `Edit: ${notification?.title}` : 'Create Scheduled Notification'
            " />

        <div class="my-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{
                            isEditing
                                ? `Edit: ${notification?.title}`
                                : "Create New Scheduled Notification"
                        }}
                    </h1>
                    <p class="text-muted-foreground mt-2">
                        {{
                            isEditing
                                ? "Update your scheduled notification details"
                                : "Schedule a notification to be sent to all users"
                        }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <AppButton label="Cancel" variant="outline"
                        @click="router.visit(route('scheduled-notifications.index'))" :disabled="form.processing" />
                    <AppButton :label="isEditing ? 'Update Notification' : 'Schedule Notification'" icon="lucide:check"
                        :processing="form.processing" @click="submit" class="bg-blue-500 hover:bg-blue-600" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Notification Details</CardTitle>
                        <CardDescription>
                            Enter the notification content and schedule
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <ValidationErrors />

                        <div class="space-y-4">
                            <div>
                                <TextInput label="Title *" v-model="form.title" :error="form.errors.title"
                                    placeholder="e.g., New Feature Update" required />
                            </div>

                            <div>
                                <label class="text-sm font-medium block mb-2">Message *</label>
                                <textarea v-model="form.message" rows="6"
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                    :class="{ 'border-destructive': form.errors.message }"
                                    placeholder="Enter your notification message..." required></textarea>
                                <p v-if="form.errors.message" class="text-sm text-destructive mt-1">
                                    {{ form.errors.message }}
                                </p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    This message will be sent to all users
                                </p>
                            </div>

                            <div>
                                <TextInput label="Link URL (Optional)" v-model="form.url" :error="form.errors.url"
                                    placeholder="https://example.com/page" />
                                <p class="text-xs text-muted-foreground mt-1">
                                    Users will be redirected to this URL when they click the notification
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium block mb-2">Schedule Date & Time *</label>
                                <input type="datetime-local" v-model="form.scheduled_at" :min="getMinDateTime()"
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                    :class="{ 'border-destructive': form.errors.scheduled_at }" required />
                                <p v-if="form.errors.scheduled_at" class="text-sm text-destructive mt-1">
                                    {{ form.errors.scheduled_at }}
                                </p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    The notification will be sent at this date and time
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Notification Preview</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="p-4 border rounded-lg bg-muted/50">
                            <div class="space-y-2">
                                <div class="flex items-start gap-2">
                                    <Icon icon="lucide:bell" class="size-4 mt-0.5 text-primary" />
                                    <div class="flex-1">
                                        <p class="font-semibold text-sm">
                                            {{ form.title || "Notification Title" }}
                                        </p>
                                        <p class="text-sm text-muted-foreground mt-1">
                                            {{ form.message || "Notification message preview..." }}
                                        </p>
                                        <div v-if="form.url" class="mt-2">
                                            <Badge variant="outline" class="text-xs">
                                                🔗 Click to open link
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Status</span>
                                <Badge variant="secondary">Pending</Badge>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Scheduled</span>
                                <span class="font-medium">
                                    {{ form.scheduled_at ? formatDate(form.scheduled_at) : "Not set" }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Recipients</span>
                                <span class="font-medium">All Users</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Actions</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex flex-col gap-2">
                            <AppButton :label="isEditing ? 'Update Notification' : 'Schedule Notification'"
                                icon="lucide:check" :processing="form.processing" @click="submit"
                                class="bg-blue-500 hover:bg-blue-600 w-full justify-center" />

                            <AppButton label="Cancel" variant="outline" class="w-full justify-center"
                                @click="router.visit(route('scheduled-notifications.index'))"
                                :disabled="form.processing" />

                            <AppButton v-if="isEditing" label="Delete Notification" variant="danger"
                                icon="lucide:trash-2" class="w-full justify-center" @click="destroy"
                                :disabled="form.processing" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppContainer>
</template>
