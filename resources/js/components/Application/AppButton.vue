<script setup>
import { computed } from 'vue';
import { wTrans } from 'laravel-vue-i18n';
// import { useI18n } from 'vue-i18n';
// const { t } = useI18n();

const props = defineProps({
    processing: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    href: {
        type: String,
    },
    label: {
        type: String,
    },
    size: {
        type: String,
        default: 'sm',
    },
    icon: {
        type: String,
        default: 'lucide:save',
    },
    iconSize: {
        type: String,
        default: 'size-4',
    },
    additionalClasses: {
        type: String,
        default: '',
    },

    noIcon: {
        type: Boolean,
        default: false,
    },

    noLabel: {
        type: Boolean,
        default: false,
    },

});
const labelText = computed(() => props.label ?? wTrans("index.save_btn"))
const iconClasses = computed(() => props.iconSize);

</script>

<template>
    <Button v-if="href" :class="[additionalClasses]" as="button" :size="size" :disabled="processing || disabled"
        as-child>
        <Link class="flex items-center justify-center gap-3" :href>
        <Icon v-if="!noIcon" :icon="processing ? 'codex:loader' : icon" :class="['size-2', iconClasses]" />
        <span v-if="!noLabel">{{ labelText }}</span>
        </Link>
    </Button>

    <Button v-else :class="[additionalClasses]" as="button" :size="size" :disabled="processing || disabled"
        class="flex items-center justify-center bg-brand-blue hover:bg-brand-blue/80 text-white">
        <Icon v-if="!noIcon" :icon="processing ? 'codex:loader' : icon" :class="['size-2', iconClasses]" />
        <span v-if="!noLabel">{{ label }}</span>
    </Button>
</template>
