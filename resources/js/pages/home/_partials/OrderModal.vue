<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const model = defineModel<boolean>({ default: false });

const props = defineProps<{
    ad?: any;
}>();

const emit = defineEmits(['order-placed', 'success']);

// Form handling
const form = useForm({
    ad_id: props.ad?.id,
    qty: 1,
    delivery_option: 'pickup',
    delivery_address: '',
    contact_number: '',
    notes: '',
    agree_terms: false
});

// Total price computation
const totalPrice = computed(() => {
    return (Number(props.ad?.price) || 0) * form.qty
});

// Quantity handlers
const incrementQuantity = () => {
    form.qty++
};

const decrementQuantity = () => {
    if (form.qty > 1) {
        form.qty--
    }
};

const validateQuantity = () => {
    if (form.qty < 1 || isNaN(form.qty)) {
        form.qty = 1
    }
};

// Submit order
const submitOrder = () => {
    form.post(route('orders.store'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('order-placed');
            emit('success');
            setTimeout(() => {
                closeModal();
            }, 1500);
        },
        onError: (errors) => {
            console.error('Order submission failed:', errors);
        }
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset('qty', 'delivery_address', 'contact_number', 'notes', 'agree_terms');
    form.delivery_option = 'pickup';
    model.value = false;
};

// Reset form when modal opens/closes
watch(model, (isOpen) => {
    if (!isOpen) {
        form.clearErrors();
        form.reset('qty', 'delivery_address', 'contact_number', 'notes', 'agree_terms');
        form.delivery_option = 'pickup';
    } else if (props.ad) {
        // Pre-fill contact number if available from user profile
        form.contact_number = props.ad?.buyer_phone || '';
        form.ad_id = props.ad.id;
    }
});

// Reset form when ad changes
watch(() => props.ad, (newAd) => {
    if (newAd) {
        form.ad_id = newAd.id;
        form.contact_number = newAd?.buyer_phone || '';
    }
}, { immediate: true });
</script>

<template>
    <Dialog v-model:open="model">
        <DialogContent class="flex max-h-[90vh] w-full max-w-[90vw] flex-col sm:max-w-[700px]">
            <DialogHeader class="">
                <DialogTitle class="flex items-center gap-2 text-lg font-semibold sm:text-xl">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                        <Icon icon="lucide:shopping-cart" class="h-4 w-4 text-primary" />
                    </div>
                    <span>Place Your Order</span>
                </DialogTitle>
                <p class="text-sm text-muted-foreground mt-1 hidden sm:visible">Complete the details below to order this
                    item</p>
            </DialogHeader>

            <!-- Product Summary -->
            <div v-if="ad" class=" rounded-lg bg-muted/30 pt-4">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-16 w-16 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gray-200">
                        <img v-if="ad.images?.length" :src="`/storage/${ad.images[0].path}`"
                            class="h-full w-full object-cover" />
                        <Icon v-else icon="lucide:image" class="h-6 w-6 text-gray-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="truncate text-sm font-medium text-gray-900">
                            {{ ad.ad_title }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Seller: {{ ad.seller_name }}
                        </p>
                        <p class="text-base font-bold text-primary mt-1">
                            Rs. {{ Number(ad.price).toLocaleString() }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Content with Scroll -->
            <div class="flex-1 overflow-y-auto py-2 px-2 pr-3">
                <form @submit.prevent="submitOrder" class="space-y-4">
                    <!-- Quantity -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center">
                            <button type="button" @click="decrementQuantity"
                                class="p-3 border border-r-0 border-gray-300 rounded-l-lg hover:bg-gray-50 transition-colors"
                                :disabled="form.qty <= 1">
                                <Icon icon="lucide:minus" class="size-4" />
                            </button>
                            <input type="number" v-model="form.qty" min="1"
                                class="w-20 text-center border-y border-gray-300 py-2 focus:outline-none focus:ring-1 focus:ring-primary"
                                :class="{ 'border-red-500': form.errors.qty }" @input="validateQuantity" />
                            <button type="button" @click="incrementQuantity"
                                class="p-3 border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-50 transition-colors">
                                <Icon icon="lucide:plus" class="size-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.qty" class="text-xs text-red-600">
                            {{ form.errors.qty }}
                        </p>
                    </div>

                    <!-- Price Summary -->
                    <div class="bg-blue-50 rounded-lg p-3 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Price per item:</span>
                            <span class="font-medium">Rs. {{ Number(ad.price).toLocaleString() }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Quantity:</span>
                            <span class="font-medium">{{ form.qty }}</span>
                        </div>
                        <div class="border-t border-blue-200 pt-2 mt-1">
                            <div class="flex justify-between font-semibold">
                                <span>Total Amount:</span>
                                <span class="text-primary text-lg">Rs. {{ totalPrice.toLocaleString() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery/Pickup Option -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">
                            Delivery/Pickup Option <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors"
                                :class="form.delivery_option === 'pickup' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" v-model="form.delivery_option" value="pickup" class="sr-only">
                                <Icon icon="lucide:map-pin" class="size-5 mr-3"
                                    :class="form.delivery_option === 'pickup' ? 'text-primary' : 'text-gray-400'" />
                                <div class="flex-1">
                                    <p class="font-medium text-sm">Self Pickup</p>
                                    <p class="text-xs text-gray-500">Arrange pickup with seller</p>
                                </div>
                                <Icon v-if="form.delivery_option === 'pickup'" icon="lucide:check-circle"
                                    class="size-5 text-primary" />
                            </label>
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors"
                                :class="form.delivery_option === 'delivery' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" v-model="form.delivery_option" value="delivery" class="sr-only">
                                <Icon icon="lucide:truck" class="size-5 mr-3"
                                    :class="form.delivery_option === 'delivery' ? 'text-primary' : 'text-gray-400'" />
                                <div class="flex-1">
                                    <p class="font-medium text-sm">Home Delivery</p>
                                    <p class="text-xs text-gray-500">Arrange delivery with seller</p>
                                </div>
                                <Icon v-if="form.delivery_option === 'delivery'" icon="lucide:check-circle"
                                    class="size-5 text-primary" />
                            </label>
                        </div>
                    </div>

                    <!-- Delivery Address (if delivery selected) -->
                    <div v-if="form.delivery_option === 'delivery'" class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">
                            Delivery Address <span class="text-red-500">*</span>
                        </label>
                        <textarea v-model="form.delivery_address" rows="2"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            :class="{ 'border-red-500': form.errors.delivery_address }"
                            placeholder="Enter your complete delivery address"></textarea>
                        <p v-if="form.errors.delivery_address" class="text-xs text-red-600">
                            {{ form.errors.delivery_address }}
                        </p>
                    </div>

                    <!-- Contact Information -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">
                            Contact Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" v-model="form.contact_number"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            :class="{ 'border-red-500': form.errors.contact_number }" placeholder="Your phone number" />
                        <p v-if="form.errors.contact_number" class="text-xs text-red-600">
                            {{ form.errors.contact_number }}
                        </p>
                    </div>

                    <!-- Additional Notes -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">
                            Additional Notes <span class="text-gray-400 text-xs">(Optional)</span>
                        </label>
                        <textarea v-model="form.notes" rows="2"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            placeholder="Any special instructions or questions for the seller"></textarea>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="flex items-start gap-2">
                        <input type="checkbox" id="terms" v-model="form.agree_terms"
                            class="mt-1 rounded border-gray-300 text-primary focus:ring-primary"
                            :class="{ 'border-red-500': form.errors.agree_terms }" />
                        <label for="terms" class="text-xs text-gray-600">
                            I agree to the <a href="#" class="text-primary hover:underline">Terms and Conditions</a>
                            and confirm that this order is genuine
                        </label>
                    </div>
                    <p v-if="form.errors.agree_terms" class="text-xs text-red-600">
                        {{ form.errors.agree_terms }}
                    </p>

                    <!-- Error Message -->
                    <div v-if="form.errors.ad_id" class="rounded-md bg-red-50 p-3 text-sm text-red-600">
                        {{ form.errors.ad_id }}
                    </div>

                    <!-- Success Message -->
                    <div v-if="form.recentlySuccessful" class="rounded-md bg-green-50 p-3 text-sm text-green-600">
                        Order placed successfully! The seller has been notified.
                    </div>
                </form>
                <!-- Safety Notice -->
                <div class=" pb-2">
                    <div class="bg-yellow-50 rounded-lg p-3">
                        <div class="flex gap-2">
                            <Icon icon="lucide:shield-alert" class="size-5 text-yellow-600 flex-shrink-0" />
                            <div class="text-xs text-yellow-800">
                                <p class="font-medium mb-1">Important Safety Tips:</p>
                                <ul class="list-disc list-inside space-y-0.5">
                                    <li>Never send money in advance</li>
                                    <li>Meet in a safe public place for pickup</li>
                                    <li>Inspect the item before completing payment</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <DialogFooter class="flex flex-col-reverse gap-2 border-t px-6 sm:flex-row sm:justify-end">
                <Button @click="submitOrder" :disabled="form.processing"
                    class="w-full bg-primary text-white hover:bg-primary/90 sm:w-auto">
                    <Icon v-if="form.processing" icon="lucide:loader-2" class="mr-2 h-4 w-4 animate-spin" />
                    {{ form.processing ? 'Placing Order...' : 'Place Order' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Custom scrollbar for the content area */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Remove spinner from number input */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}
</style>