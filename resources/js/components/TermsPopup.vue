<template>
    <div v-if="!accepted" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] flex flex-col shadow-xl">
            <!-- Header -->
            <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-200">
                <div class="w-10 h-10 bg-brand-teal/10 rounded-full flex items-center justify-center">
                    <Icon icon="mdi:file-document" class="text-brand-teal text-xl" />
                </div>
                <h2 class="text-xl font-bold text-gray-900">Terms & Privacy Policy</h2>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="space-y-8">
                    <!-- Terms & Conditions Section -->
                    <section>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <span class="w-1.5 h-5 bg-brand-teal rounded-full mr-3"></span>
                            Terms & Conditions
                        </h3>
                        <div class="space-y-6 text-sm text-gray-600 leading-relaxed">
                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">1. Platform Role</h4>
                                <p>AMO Mercatus is an online marketplace where sellers can list products and connect
                                    with buyers. We provide the platform and infrastructure to facilitate these
                                    connections but are not directly involved in transactions between users.</p>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">2. Seller Responsibilities</h4>
                                <p>Sellers are responsible for providing accurate product information and ensuring their
                                    listings comply with applicable laws. This includes maintaining truthful
                                    descriptions, fair pricing, and adherence to all relevant regulations.</p>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">3. Prohibited Activities</h4>
                                <p>Users must not post illegal products, misleading listings, or engage in fraudulent
                                    activities. The following are strictly prohibited:</p>
                                <ul class="list-disc pl-5 mt-2 space-y-1">
                                    <li>Listing counterfeit or unauthorized products</li>
                                    <li>Misrepresenting product specifications or condition</li>
                                    <li>Engaging in price manipulation or fraudulent transactions</li>
                                    <li>Violating intellectual property rights</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">4. Account Suspension</h4>
                                <p>AMO Mercatus reserves the right to remove listings or suspend accounts that violate
                                    platform rules. We maintain a fair and transparent process for reviewing potential
                                    violations and enforcing appropriate actions.</p>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">5. Dispute Resolution</h4>
                                <p>AMO Mercatus acts as a marketplace platform and is not responsible for disputes
                                    between buyers and sellers. We encourage users to communicate directly and resolve
                                    issues amicably. In cases where assistance is needed, we provide mediation support
                                    to help facilitate resolution.</p>
                            </div>
                        </div>
                    </section>

                    <!-- Privacy Policy Section -->
                    <section class="pt-4 border-t border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <span class="w-1.5 h-5 bg-brand-teal rounded-full mr-3"></span>
                            Privacy Policy
                        </h3>
                        <div class="space-y-6 text-sm text-gray-600 leading-relaxed">
                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">1. Information We Collect</h4>
                                <p>AMO Mercatus respects your privacy. We collect only the information necessary to
                                    provide our marketplace services:</p>
                                <ul class="list-disc pl-5 mt-2 space-y-1">
                                    <li><span class="font-medium text-gray-800">Name:</span> For account identification
                                        and personalization</li>
                                    <li><span class="font-medium text-gray-800">Email Address:</span> For account
                                        verification and communications</li>
                                    <li><span class="font-medium text-gray-800">Phone Number:</span> For account
                                        security and support</li>
                                    <li><span class="font-medium text-gray-800">Payment Information:</span> Processed
                                        securely through our payment partners</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">2. How We Use Your Information</h4>
                                <p>Your information is used to provide marketplace services, process membership
                                    payments, improve user experience, and communicate updates or support. We use your
                                    data only for purposes that enhance your experience.</p>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">3. Data Security</h4>
                                <p>We take appropriate security measures to protect user data and prevent unauthorized
                                    access. Our security protocols are regularly reviewed and updated.</p>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">4. Third-Party Services</h4>
                                <p>We may use trusted third‑party payment gateways (e.g., JazzCash) to securely process
                                    payments. These partners adhere to strict security standards.</p>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">5. User Consent</h4>
                                <p>By using the AMO Mercatus platform, you agree to the collection and use of
                                    information as described. Please review this policy periodically for updates.</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <!-- Footer with Checkbox and Actions -->
            <div class="border-t border-gray-200 px-6 py-5">
                <div class="flex items-start mb-4">
                    <input type="checkbox" id="accept-terms-checkbox" v-model="termsAccepted"
                        class="mt-0.5 w-4 h-4 text-brand-teal rounded border-gray-300 focus:ring-brand-teal/20" />
                    <label for="accept-terms-checkbox" class="ml-3 text-sm text-gray-600 leading-relaxed">
                        I have read and agree to the <span class="font-medium text-gray-800">Terms & Conditions</span>
                        and
                        <span class="font-medium text-gray-800">Privacy Policy</span> above.
                    </label>
                </div>

                <div class="flex justify-end gap-3">
                    <!-- <button @click="declineTerms"
                        class="px-4 py-2 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                        Decline
                    </button> -->
                    <button @click="acceptTerms" :disabled="!termsAccepted || loading"
                        class="px-6 py-2 bg-brand-teal text-white rounded-lg hover:bg-brand-teal/90 transition-all text-sm font-medium shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="loading" class="flex items-center gap-2">
                            <Icon icon="lucide:loader-circle" class="animate-spin size-4" />
                            Accepting...
                        </span>
                        <span v-else>Accept & Continue</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Icon } from '@iconify/vue'
import axios from 'axios'

const props = defineProps({
    accepted: Boolean
})

const emit = defineEmits(['update:accepted'])

const accepted = ref(props.accepted)
const termsAccepted = ref(false)
const loading = ref(false)

const acceptTerms = async () => {
    if (!termsAccepted.value) return
    loading.value = true
    try {
        await axios.post('/accept-terms')
        accepted.value = true
        emit('update:accepted', true)
    } catch (error) {
        console.error('Failed to accept terms', error)
        // Optional: show error toast
    } finally {
        loading.value = false
    }
}

const declineTerms = () => {
    // Redirect to logout or home as appropriate
    window.location.href = '/logout'
}
</script>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 5px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Smooth scroll offset for section anchors if used */
section {
    scroll-margin-top: 1rem;
}
</style>