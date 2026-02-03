import { ref } from 'vue'

export interface BreadcrumbItem {
    label: string
    href?: string
}

const breadcrumbs = ref<BreadcrumbItem[]>([])

export function useBreadcrumb() {
    function set(items: BreadcrumbItem[]) {
        breadcrumbs.value = items
    }

    function resetList() {
        breadcrumbs.value = []
    }

    return {
        breadcrumbs,
        set,
        resetList,
    }
}
