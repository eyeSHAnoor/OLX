
<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    value: string // Example: '15-Jul-25 | 15:21'
}>()
const { formatDate } = useHelpers();

const [date, time] = computed(() => {
    const datetime = formatDate(props.value, true, 'dd-MM-yyyy | h:mm aaa')
    const parts = datetime?.split('|').map(s => s.trim())
    return [parts ? parts[0] : '', parts ? parts[1] : '']
}).value
</script>

<template>
    <div class="">
        <p>{{ date }}</p>
        <p class="text-muted-foreground">{{ time }}</p>
    </div>
</template>
