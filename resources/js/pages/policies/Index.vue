<script setup>
import { ref } from 'vue'
import { Icon } from '@iconify/vue'
import { router } from '@inertiajs/vue3'

const activeTab = ref('privacy') // privacy, terms, refund
const lastUpdated = 'March 11, 2026'

const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId)
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
}

const goBack = () => {
    router.visit('/')
}

const sections = {
    privacy: [
        { id: 'information-collection', title: 'Information We Collect' },
        { id: 'information-usage', title: 'How We Use Your Information' },
        { id: 'data-security', title: 'Data Security' },
        { id: 'third-party', title: 'Third-Party Services' },
        { id: 'user-consent', title: 'User Consent' }
    ],
    terms: [
        { id: 'platform-role', title: 'Platform Role' },
        { id: 'seller-responsibilities', title: 'Seller Responsibilities' },
        { id: 'prohibited-activities', title: 'Prohibited Activities' },
        { id: 'account-suspension', title: 'Account Suspension' },
        { id: 'dispute-resolution', title: 'Dispute Resolution' }
    ],
    refund: [
        { id: 'membership-fees', title: 'Membership Fees' },
        { id: 'refund-policy', title: 'Refund Policy' },
        { id: 'cancellation', title: 'Cancellation Terms' },
        { id: 'exceptional-cases', title: 'Exceptional Cases' },
        { id: 'policy-updates', title: 'Policy Updates' }
    ]
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header with navigation -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <button @click="goBack"
                            class="flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                            <Icon icon="mdi:arrow-left" class="text-xl mr-2" />
                            <span class="text-sm font-medium">Back to Home</span>
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Last Updated:</span>
                        <span class="text-sm font-medium text-teal-600">{{ lastUpdated }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                <!-- Sidebar Navigation -->
                <div class="hidden lg:block lg:col-span-3">
                    <div class="sticky top-24 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <!-- Tab Navigation -->
                        <div class="space-y-1 mb-6">
                            <button @click="activeTab = 'privacy'"
                                class="w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-colors"
                                :class="activeTab === 'privacy' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'">
                                <Icon icon="mdi:shield-lock" class="inline mr-2 text-lg" />
                                Privacy Policy
                            </button>
                            <button @click="activeTab = 'terms'"
                                class="w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-colors"
                                :class="activeTab === 'terms' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'">
                                <Icon icon="mdi:file-document" class="inline mr-2 text-lg" />
                                Terms & Conditions
                            </button>
                            <button @click="activeTab = 'refund'"
                                class="w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-colors"
                                :class="activeTab === 'refund' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'">
                                <Icon icon="mdi:cash-refund" class="inline mr-2 text-lg" />
                                Refund & Cancellation
                            </button>
                        </div>

                        <!-- Section Links -->
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3 px-4">
                                On this page
                            </p>
                            <div class="space-y-1">
                                <button v-for="section in sections[activeTab]" :key="section.id"
                                    @click="scrollToSection(section.id)"
                                    class="w-full text-left px-4 py-2 text-xs text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                    {{ section.title }}
                                </button>
                            </div>
                        </div>

                        <!-- Contact Support -->
                        <div class="border-t border-gray-100 mt-6 pt-6 px-4">
                            <div class="bg-teal-50 rounded-lg p-4">
                                <div class="flex items-center mb-3">
                                    <div class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center">
                                        <Icon icon="mdi:help-circle" class="text-white text-lg" />
                                    </div>
                                    <h4 class="ml-3 text-sm font-semibold text-teal-700">Need Help?</h4>
                                </div>
                                <p class="text-xs text-gray-600 mb-3">
                                    Have questions about our policies? Our support team is here to help.
                                </p>
                                <a href="mailto:support@amomercatus.com"
                                    class="block w-full text-center bg-white text-teal-600 text-xs font-medium py-2 px-3 rounded-lg border border-teal-200 hover:bg-teal-50 transition-colors">
                                    Contact Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="lg:col-span-9">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Mobile Tab Navigation -->
                        <div class="lg:hidden border-b border-gray-200">
                            <div class="flex p-1 bg-gray-50">
                                <button @click="activeTab = 'privacy'"
                                    class="flex-1 px-3 py-2 text-xs font-medium rounded-lg transition-colors"
                                    :class="activeTab === 'privacy' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600'">
                                    Privacy
                                </button>
                                <button @click="activeTab = 'terms'"
                                    class="flex-1 px-3 py-2 text-xs font-medium rounded-lg transition-colors"
                                    :class="activeTab === 'terms' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600'">
                                    Terms
                                </button>
                                <button @click="activeTab = 'refund'"
                                    class="flex-1 px-3 py-2 text-xs font-medium rounded-lg transition-colors"
                                    :class="activeTab === 'refund' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600'">
                                    Refund
                                </button>
                            </div>
                        </div>

                        <!-- Privacy Policy Content -->
                        <div v-if="activeTab === 'privacy'" class="p-6 sm:p-8">
                            <!-- Header -->
                            <div class="mb-8">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                        <Icon icon="mdi:shield-lock" class="text-blue-600 text-2xl" />
                                    </div>
                                    <h1 class="ml-4 text-2xl sm:text-3xl font-bold text-gray-900">Privacy Policy</h1>
                                </div>
                                <p class="text-gray-600 text-sm leading-relaxed">
                                    At AMO Mercatus, we take your privacy seriously. This policy describes how we
                                    collect,
                                    use, and protect your personal information when you use our marketplace platform.
                                </p>
                            </div>

                            <!-- Content Sections -->
                            <div class="space-y-8">
                                <section id="information-collection" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Information We Collect
                                    </h2>
                                    <div class="pl-4 space-y-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            AMO Mercatus respects the privacy of its users and is committed to
                                            protecting
                                            personal information. We may collect basic information necessary to provide
                                            you
                                            with our marketplace services.
                                        </p>
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h3 class="text-sm font-medium text-gray-900 mb-3">Types of information we
                                                collect:</h3>
                                            <ul class="space-y-2">
                                                <li class="flex items-start text-sm text-gray-600">
                                                    <Icon icon="mdi:check-circle"
                                                        class="text-teal-500 mr-2 mt-0.5 flex-shrink-0" />
                                                    <span><span class="font-medium text-gray-900">Name:</span> For
                                                        account identification and personalization</span>
                                                </li>
                                                <li class="flex items-start text-sm text-gray-600">
                                                    <Icon icon="mdi:check-circle"
                                                        class="text-teal-500 mr-2 mt-0.5 flex-shrink-0" />
                                                    <span><span class="font-medium text-gray-900">Email Address:</span>
                                                        For account verification and communications</span>
                                                </li>
                                                <li class="flex items-start text-sm text-gray-600">
                                                    <Icon icon="mdi:check-circle"
                                                        class="text-teal-500 mr-2 mt-0.5 flex-shrink-0" />
                                                    <span><span class="font-medium text-gray-900">Phone Number:</span>
                                                        For account security and support</span>
                                                </li>
                                                <li class="flex items-start text-sm text-gray-600">
                                                    <Icon icon="mdi:check-circle"
                                                        class="text-teal-500 mr-2 mt-0.5 flex-shrink-0" />
                                                    <span><span class="font-medium text-gray-900">Payment
                                                            Information:</span> Processed securely through our payment
                                                        partners</span>
                                                </li>
                                                <li class="flex items-start text-sm text-gray-600">
                                                    <Icon icon="mdi:check-circle"
                                                        class="text-teal-500 mr-2 mt-0.5 flex-shrink-0" />
                                                    <span><span class="font-medium text-gray-900">Account
                                                            Details:</span> Transaction history and marketplace
                                                        activity</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </section>

                                <section id="information-usage" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        How We Use Your Information
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            This information is used to provide marketplace services, process membership
                                            payments,
                                            improve user experience, and communicate updates or support. We are
                                            committed to using
                                            your data only for purposes that enhance your experience on our platform.
                                        </p>
                                    </div>
                                </section>

                                <section id="data-security" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Data Security
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            We take appropriate security measures to protect user data and prevent
                                            unauthorized
                                            access. Our security protocols are regularly reviewed and updated to
                                            maintain the
                                            highest standards of data protection.
                                        </p>
                                    </div>
                                </section>

                                <section id="third-party" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Third-Party Services
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            We may use trusted third party payment gateways such as JazzCash to securely
                                            process
                                            payments. These partners are carefully vetted and adhere to strict security
                                            standards
                                            to protect your financial information.
                                        </p>
                                    </div>
                                </section>

                                <section id="user-consent" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        User Consent
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            By using the AMO Mercatus platform, users agree to the collection and use of
                                            information as described in this policy. We recommend reviewing this policy
                                            periodically
                                            for any updates.
                                        </p>
                                    </div>
                                </section>
                            </div>
                        </div>

                        <!-- Terms & Conditions Content -->
                        <div v-else-if="activeTab === 'terms'" class="p-6 sm:p-8">
                            <!-- Header -->
                            <div class="mb-8">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                        <Icon icon="mdi:file-document" class="text-blue-600 text-2xl" />
                                    </div>
                                    <h1 class="ml-4 text-2xl sm:text-3xl font-bold text-gray-900">Terms & Conditions
                                    </h1>
                                </div>
                                <p class="text-gray-600 text-sm leading-relaxed">
                                    Welcome to AMO Mercatus. By accessing or using our platform, you agree to comply
                                    with
                                    and be bound by the following terms and conditions.
                                </p>
                            </div>

                            <!-- Content Sections -->
                            <div class="space-y-8">
                                <section id="platform-role" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Platform Role
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            AMO Mercatus is an online marketplace where sellers can list products and
                                            connect with
                                            potential buyers. We provide the platform and infrastructure to facilitate
                                            these
                                            connections but are not directly involved in transactions between users.
                                        </p>
                                    </div>
                                </section>

                                <section id="seller-responsibilities" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Seller Responsibilities
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            Sellers are responsible for providing accurate product information and
                                            ensuring their
                                            listings comply with applicable laws. This includes maintaining truthful
                                            descriptions,
                                            fair pricing, and adherence to all relevant regulations.
                                        </p>
                                    </div>
                                </section>

                                <section id="prohibited-activities" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Prohibited Activities
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed mb-3">
                                            Users must not post illegal products, misleading listings, or engage in
                                            fraudulent
                                            activities. The following activities are strictly prohibited:
                                        </p>
                                        <ul class="space-y-2 text-sm text-gray-600">
                                            <li class="flex items-start">
                                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full mt-2 mr-2"></span>
                                                Listing counterfeit or unauthorized products
                                            </li>
                                            <li class="flex items-start">
                                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full mt-2 mr-2"></span>
                                                Misrepresenting product specifications or condition
                                            </li>
                                            <li class="flex items-start">
                                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full mt-2 mr-2"></span>
                                                Engaging in price manipulation or fraudulent transactions
                                            </li>
                                            <li class="flex items-start">
                                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full mt-2 mr-2"></span>
                                                Violating intellectual property rights
                                            </li>
                                        </ul>
                                    </div>
                                </section>

                                <section id="account-suspension" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Account Suspension
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            AMO Mercatus reserves the right to remove listings or suspend accounts that
                                            violate
                                            platform rules. We maintain a fair and transparent process for reviewing
                                            potential
                                            violations and enforcing appropriate actions.
                                        </p>
                                    </div>
                                </section>

                                <section id="dispute-resolution" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Dispute Resolution
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            AMO Mercatus acts as a marketplace platform and is not responsible for
                                            disputes between
                                            buyers and sellers. We encourage users to communicate directly and resolve
                                            issues
                                            amicably. In cases where assistance is needed, we provide mediation support
                                            to help
                                            facilitate resolution.
                                        </p>
                                    </div>
                                </section>
                            </div>
                        </div>

                        <!-- Refund & Cancellation Content -->
                        <div v-else-if="activeTab === 'refund'" class="p-6 sm:p-8">
                            <!-- Header -->
                            <div class="mb-8">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                        <Icon icon="mdi:cash-refund" class="text-blue-600 text-2xl" />
                                    </div>
                                    <h1 class="ml-4 text-2xl sm:text-3xl font-bold text-gray-900">Refund & Cancellation
                                        Policy</h1>
                                </div>
                                <p class="text-gray-600 text-sm leading-relaxed">
                                    Understanding our refund and cancellation policies is important. Please review the
                                    following
                                    information carefully.
                                </p>
                            </div>

                            <!-- Content Sections -->
                            <div class="space-y-8">
                                <section id="membership-fees" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Membership Fees
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            Sellers may be required to pay a membership fee to access platform features
                                            and list
                                            products. These fees provide access to our marketplace tools, analytics, and
                                            support
                                            services that help sellers succeed on our platform.
                                        </p>
                                    </div>
                                </section>

                                <section id="refund-policy" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Refund Policy
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            Membership fees are generally non-refundable once the service has been
                                            activated. This
                                            policy ensures we can maintain and improve our platform services for all
                                            users. The
                                            activation of your membership provides immediate access to all included
                                            features and
                                            benefits.
                                        </p>
                                    </div>
                                </section>

                                <section id="exceptional-cases" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Exceptional Cases
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            In exceptional cases, refund requests may be reviewed and approved at the
                                            discretion of
                                            AMO Mercatus management. Such cases may include technical issues preventing
                                            platform
                                            access, duplicate charges, or other extenuating circumstances. Each request
                                            is evaluated
                                            on its own merits.
                                        </p>
                                    </div>
                                </section>

                                <section id="cancellation" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Cancellation Terms
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            Users may cancel their membership at any time, but no refund will be issued
                                            for the
                                            remaining membership period. Cancellation takes effect at the end of your
                                            current billing
                                            cycle, and you will retain access to your membership benefits until that
                                            time.
                                        </p>
                                    </div>
                                </section>

                                <section id="policy-updates" class="scroll-mt-24">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <span class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></span>
                                        Policy Updates
                                    </h2>
                                    <div class="pl-4">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            AMO Mercatus reserves the right to update these policies at any time to
                                            improve service
                                            quality and ensure compliance with applicable regulations. Users will be
                                            notified of
                                            significant changes, and continued use of the platform constitutes
                                            acceptance of updated
                                            policies.
                                        </p>
                                    </div>
                                </section>
                            </div>
                        </div>

                        <!-- Footer Note -->
                        <div class="border-t border-gray-200 bg-gray-50 p-6 sm:p-8">
                            <div class="flex items-start">
                                <Icon icon="mdi:information" class="text-blue-500 text-xl mr-3 flex-shrink-0" />
                                <p class="text-xs text-gray-500 leading-relaxed">
                                    These policies were last updated on {{ lastUpdated }}. By continuing to use AMO
                                    Mercatus,
                                    you acknowledge that you have read and understood these terms. If you have any
                                    questions
                                    or concerns, please contact our support team at
                                    <a href="mailto:support@amomercatus.com"
                                        class="text-blue-600 hover:underline">support@amomercatus.com</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Section highlighting on scroll */
section {
    scroll-margin-top: 6rem;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
    section {
        scroll-margin-top: 5rem;
    }
}
</style>