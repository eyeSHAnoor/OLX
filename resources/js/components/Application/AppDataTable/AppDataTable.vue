<script setup lang="ts" generic="TData, TValue">
import { computed, ref } from 'vue';
import type { ColumnDef, SortingState, ColumnFiltersState, VisibilityState, ExpandedState } from '@tanstack/vue-table';
import type { PaginationData } from '@/types';

import {
	FlexRender,
	getCoreRowModel,
	useVueTable,
	getPaginationRowModel,
	getSortedRowModel,
	getFilteredRowModel,
	getExpandedRowModel,
} from '@tanstack/vue-table';
import { valueUpdater } from '@/lib/utils';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
// import AppDataTablePagination from './AppDataTablePagination.vue';
import AppDataTableViewOptions from './AppDataTableViewOptions.vue';
import SearchInput from '../SearchInput.vue';

const props = defineProps<{
	columns: ColumnDef<TData, TValue>[];
	data: TData[];
	searchPlaceholder?: string;
	paginationData?: PaginationData | null;
	isFiltered?: boolean;
	hasMobileView?: boolean;
}>();

const emit = defineEmits(['onChangeRecordsPerPage', 'resetFilter']);

const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<VisibilityState>({});
const rowSelection = ref({});
const expanded = ref<ExpandedState>({});

const table = useVueTable({
	get data() {
		return props.data;
	},
	get columns() {
		return props.columns;
	},
	getCoreRowModel: getCoreRowModel(),
	getPaginationRowModel: getPaginationRowModel(),
	getSortedRowModel: getSortedRowModel(),
	getExpandedRowModel: getExpandedRowModel(),

	onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
	onColumnFiltersChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnFilters),
	getFilteredRowModel: getFilteredRowModel(),
	onColumnVisibilityChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnVisibility),
	onRowSelectionChange: (updaterOrValue) => valueUpdater(updaterOrValue, rowSelection),
	onExpandedChange: (updaterOrValue) => valueUpdater(updaterOrValue, expanded),

	state: {
		get sorting() {
			return sorting.value;
		},
		get columnFilters() {
			return columnFilters.value;
		},
		get columnVisibility() {
			return columnVisibility.value;
		},
		get rowSelection() {
			return rowSelection.value;
		},
		get expanded() {
			return expanded.value;
		},
	},
});

const search = defineModel<string | number>('search');
const perPage = defineModel<string | number>('perPage');
</script>

<template>
	<section>
		<div class="flex flex-col md:flex-row md:items-center md:justify-between pt-1">
			<div class="flex items-center flex-wrap gap-3">
				<SearchInput class="max-w-sm" :placeholder="searchPlaceholder" v-model="search" />

				<div v-if="isFiltered">
					<Button variant="ghost" class="h-8 px-2 lg:px-3" @click="emit('resetFilter')">
						Reset <Icon icon="radix-icons:cross-2" class="size-4 ml-2" />
					</Button>
				</div>
			</div>

			<div class="md:ml-auto mt-3 md:mt-0">
				<AppDataTableViewOptions :table="table" />
			</div>
		</div>

		<div class="mt-4 mb-4 flex items-center gap-3 flex-wrap">
			<slot name="filters" />
		</div>

		<!-- Desktop View -->
		<div class="border rounded-md hidden lg:block">
			<Table>
				<TableHeader>
					<TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
						<TableHead v-for="header in headerGroup.headers" :key="header.id">
							<FlexRender
								v-if="!header.isPlaceholder"
								:render="header.column.columnDef.header"
								:props="header.getContext()" />
						</TableHead>
					</TableRow>
				</TableHeader>
				<TableBody>
					<template v-if="table.getRowModel().rows?.length">
						<template v-for="row in table.getRowModel().rows" :key="row.id">
							<TableRow :data-state="row.getIsSelected() ? 'selected' : undefined">
								<TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
									<FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
								</TableCell>
							</TableRow>
							<TableRow v-if="row.getIsExpanded()">
								<TableCell :colspan="row.getAllCells().length">
									{{ JSON.stringify(row.original) }}
								</TableCell>
							</TableRow>
						</template>
					</template>
					<template v-else>
						<TableRow>
							<TableCell :colspan="columns.length" class="h-24 text-center"> No results. </TableCell>
						</TableRow>
					</template>
				</TableBody>
			</Table>
		</div>

		<!-- Mobile View (Visible on smaller screens only) -->
		<div class="lg:hidden grid grid-cols-1 gap-3 divide-y divide-gray-200 mt-4">
			<div v-for="row in table.getRowModel().rows" :key="row.id" class="py-2">
				<div v-for="(cell, index) in row.getVisibleCells()" :key="cell.id"
				class="grid grid-cols-[30%_auto] gap-3 p-1 rounded-md " :class="[index === 0 && 'bg-muted-foreground/20']" >
					<!-- ✅ Header Label -->
					<div class="text-sm font-semibold">
						<p >{{ cell.column.columnDef.meta?.mobileTitle || cell.column.id }}:</p>
						<!-- <FlexRender :render="cell.column.columnDef.header" :props="cell.getContext()" /> -->
					</div>

					<!-- ✅ Cell Content -->
					<div class="text-sm text-gray-800">
						<FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
					</div>
				</div>

				<!-- Optional: expanded content -->
				<div v-if="row.getIsExpanded()" class="mt-2 text-xs text-gray-500">
					<pre>{{ JSON.stringify(row.original, null, 2) }}</pre>
				</div>
			</div>
		</div>

		<!-- <AppDataTablePagination :table="table" /> -->
		<AppDataTablePaginationServer :paginationData="paginationData" v-model="perPage" />
	</section>
</template>
