<script setup lang="ts" generic="TData, TValue">
import type { Table } from '@tanstack/vue-table';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import {
	DropdownMenu,
	DropdownMenuCheckboxItem,
	DropdownMenuContent,
	DropdownMenuLabel,
	DropdownMenuSeparator,
	DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface DataTableViewOptionsProps {
	table: Table<TData>;
}

const props = defineProps<DataTableViewOptionsProps>();

const columns = computed(() =>
	props.table.getAllColumns().filter((column) => typeof column.accessorFn !== 'undefined' && column.getCanHide())
);
</script>

<template>
	<DropdownMenu>
		<DropdownMenuTrigger as-child>
            <Button variant="secondary" size="sm" class="h-7 flex">
				<Icon icon="radix-icons:mixer-horizontal" class="size-4 mr-2" />
				View
			</Button>
		</DropdownMenuTrigger>
		<DropdownMenuContent align="end" class="min-w-[150px]">
			<DropdownMenuLabel>Toggle columns</DropdownMenuLabel>
			<DropdownMenuSeparator />

			<DropdownMenuCheckboxItem
				v-for="column in columns"
				:key="column.id"
				class="capitalize"
				:modelValue="column.getIsVisible()"
				@update:modelValue="(value: boolean) => column.toggleVisibility(!!value)">
				{{ column.id }}
			</DropdownMenuCheckboxItem>
		</DropdownMenuContent>
	</DropdownMenu>
</template>
