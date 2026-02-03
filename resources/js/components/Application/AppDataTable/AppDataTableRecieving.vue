<script setup lang="ts" generic="TData, TValue">
import { computed, ref, watch, h } from 'vue'
import type {
    ColumnDef,
    SortingState,
    ColumnFiltersState,
    VisibilityState,
    ExpandedState,
    PaginationState,
} from '@tanstack/vue-table'
import type { PaginationData } from '@/types'

import {
    FlexRender,
    getCoreRowModel,
    useVueTable,
    getPaginationRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    getExpandedRowModel,
} from '@tanstack/vue-table'
import { valueUpdater } from '@/lib/utils'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import AppDataTableViewOptions from './AppDataTableViewOptions.vue'
import SearchInput from '../SearchInput.vue'
import AppDataTableColumnHeader from './AppDataTableColumnHeader.vue'
// import { useI18n } from 'vue-i18n';
// const { t } = useI18n();
interface SimpleColumn {
    accessorKey: string
    header: string
    sortable?: boolean
    enableHiding?: boolean
    mobileTitle?: string
}

const props = defineProps<{
    columns: SimpleColumn[]
    data: TData[]
    searchPlaceholder?: string
    paginationData?: PaginationData | null
    isFiltered?: boolean
    hasMobileView?: boolean
    selectable?: boolean
}>()

const emit = defineEmits(['onChangeRecordsPerPage', 'resetFilter'])
const slots = defineSlots()

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const rowSelection = ref({})
const expanded = ref<ExpandedState>({})

// ✅ Expose selected rows via v-model
const selected = defineModel<TData[]>('selected', { default: [] })

// Convert simple columns to TanStack table column definitions
const tableColumns = computed<ColumnDef<TData, TValue>[]>(() => {
    const cols: ColumnDef<TData, TValue>[] = props.columns.map((col) => {
        const columnDef: ColumnDef<TData, TValue> = {
            accessorKey: col.accessorKey,
            enableHiding: col.enableHiding !== false,
            meta: {
                mobileTitle: col.mobileTitle || col.header,
                header: col.header,
            },
        }

        // Header renderer
        if (col.sortable) {
            columnDef.header = ({ column }) =>
                h(AppDataTableColumnHeader, {
                    column,
                    // title: col.header,
                    title: unref(col.header),
                })
        } else {
            // columnDef.header = col.header
            columnDef.header = () => unref(col.header)
        }

        // Cell renderer
        const slotName = `${col.accessorKey}-cell`
        if (slots[slotName]) {
            columnDef.cell = ({ row, getValue }) => {
                return h('div', {}, slots[slotName]?.({ row, getValue, value: getValue() }))
            }
        } else {
            columnDef.cell = ({ getValue }) => {
                const value = getValue()
                return h('div', {}, value?.toString() || '')
            }
        }

        return columnDef
    })

    // If selectable, prepend a selection column
    if (props.selectable) {
        const selectionColumn: ColumnDef<TData, TValue> = {
            id: 'select',
            header: ({ table }) =>
                h('input', {
                    type: 'checkbox',
                    checked: table.getIsAllPageRowsSelected(),
                    indeterminate: table.getIsSomePageRowsSelected(),
                    onChange: (e: Event) =>
                        table.toggleAllPageRowsSelected((e.target as HTMLInputElement).checked),
                }),
            cell: ({ row }) =>
                h('input', {
                    type: 'checkbox',
                    checked: row.getIsSelected(),
                    indeterminate: row.getIsSomeSelected(),
                    onChange: (e: Event) =>
                        row.toggleSelected((e.target as HTMLInputElement).checked),
                }),
            enableSorting: false,
            enableHiding: false,
        }
        return [selectionColumn, ...cols]
    }

    return cols
})

const search = defineModel<string | number>('search')
const perPage = defineModel<string | number>('perPage')

const pagination = ref<PaginationState>({
    pageIndex: 0,
    pageSize: Number(perPage.value) || 10,
})

// keep pagination in sync if perPage changes
watch(perPage, (newVal) => {
    pagination.value.pageSize = Number(newVal) || 10
})

const table = useVueTable({
    get data() {
        return props.data
    },
    get columns() {
        return tableColumns.value
    },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getExpandedRowModel: getExpandedRowModel(),

    onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnFilters),
    getFilteredRowModel: getFilteredRowModel(),
    onColumnVisibilityChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, rowSelection),
    onExpandedChange: (updaterOrValue) => valueUpdater(updaterOrValue, expanded),
    onPaginationChange: (updaterOrValue) => valueUpdater(updaterOrValue, pagination),

    state: {
        get pagination() {
            return pagination.value
        },
        get sorting() {
            return sorting.value
        },
        get columnFilters() {
            return columnFilters.value
        },
        get columnVisibility() {
            return columnVisibility.value
        },
        get rowSelection() {
            return rowSelection.value
        },
        get expanded() {
            return expanded.value
        },
    },
})

// Watch for changes in rowSelection and update v-model:selected
watch(rowSelection, () => {
    selected.value = table.getSelectedRowModel().rows.map((r) => r.original as TData)
})
</script>

<template>
    <section>
        <!-- Desktop View -->
        <div class="border rounded-md hidden lg:block mt-5">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
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
                            <TableCell :colspan="columns.length" class="h-24 text-center">
                                {{ $t("index.no_results") }}
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Mobile View -->
        <div class="lg:hidden grid grid-cols-1 gap-3 divide-y divide-gray-200 mt-4">
            <div v-for="row in table.getRowModel().rows" :key="row.id" class="py-2">
                <div v-for="(cell, index) in row.getVisibleCells()" :key="cell.id"
                    class="grid grid-cols-[30%_auto] gap-3 p-1 rounded-md"
                    :class="[index === 0 && 'bg-muted-foreground/20']">
                    <div class="text-sm font-semibold">
                        <p>{{ cell.column.columnDef.meta?.header || cell.column.id }}:</p>
                    </div>
                    <div class="text-sm text-gray-800">
                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                    </div>
                </div>

                <div v-if="row.getIsExpanded()" class="mt-2 text-xs text-gray-500">
                    <pre>{{ JSON.stringify(row.original, null, 2) }}</pre>
                </div>
            </div>
        </div>

        <AppDataTablePaginationServer :paginationData="paginationData" v-model="perPage" />
    </section>
</template>
