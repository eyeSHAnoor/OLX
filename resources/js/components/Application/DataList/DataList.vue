<script setup lang="ts">
defineProps<{
  borderClass?: string
  divideClass?: string
}>()
</script>

<template>
  <div :class="borderClass ?? 'border-gray-100'">
    <dl :class="['grid gap-y-3', divideClass ?? 'divide-y divide-gray-100']">
      <slot />
    </dl>
  </div>
</template>
