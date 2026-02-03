<script setup lang="ts" generic="TData, TValue">
import type { Column } from '@tanstack/vue-table';

import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';

interface DataTableColumnHeaderProps {
	column: Column<TData, any>;
	title?: string;
}

defineProps<DataTableColumnHeaderProps>();
</script>

<script lang="ts">
export default {
	inheritAttrs: false,
};
</script>

<template>
	<div v-if="column.getCanSort()" :class="cn('flex items-center space-x-2', $attrs.class ?? '')">
		<Button
			variant="ghost"
			class="-ml-3 h-8 data-[state=open]:bg-accent"
			@click="column.toggleSorting(column.getIsSorted() === 'asc')">
			<span>{{ title }}</span>
			<Icon v-if="column.getIsSorted() === 'desc'" icon="radix-icons:arrow-down" class="size-4 ml-2" />
			<Icon v-else-if="column.getIsSorted() === 'asc'" icon="radix-icons:arrow-up" class="size-4 ml-2" />
			<Icon v-else icon="radix-icons:caret-sort" class="size-4 ml-2" />
		</Button>

	</div>

	<div v-else :class="$attrs.class">
		{{ title }}
	</div>
</template>
