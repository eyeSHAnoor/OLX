<script setup lang="ts">
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Icon } from '@iconify/vue';
import type { PaginationData } from '@/types';

// import { useI18n } from 'vue-i18n';
// const { t } = useI18n();

const props = withDefaults(defineProps<{
	paginationData: PaginationData | null;
	showTotalPages?: boolean;
}>(), {
	showTotalPages: true
})

const model = defineModel<string | number>();
</script>

<template>
	<div v-if="paginationData" :class="[
		'my-1 px-5 py-3 flex flex-col md:flex-row gap-5 items-center  border-t border-secondary-light/20 dark:border-none',
		showTotalPages ? 'justify-between' : 'justify-end',
	]">
		<!-- Records per page -->
		<div class="flex flex-col md:flex-row items-center space-x-2" v-if="showTotalPages">
			<div class="whitespace-nowrap">
				<div class="flex items-center space-x-2">
					<p class="text-sm font-medium">{{ $t("index.rows_per_page") }}</p>
					<Select v-model="model">
						<SelectTrigger class="h-8 w-[70px]">
							<SelectValue :placeholder="model" />
						</SelectTrigger>
						<SelectContent side="top">
							<SelectItem v-for="pageSize in [10, 25, 50, 100, 500]" :key="pageSize"
								:value="`${pageSize}`">
								{{ pageSize }}
							</SelectItem>
						</SelectContent>
					</Select>
				</div>

				<p class="text-xs text-muted-foreground ml-1">
					<span class="font-semibold">{{ paginationData.from }}</span>
					{{ ' ' }}
					to
					{{ ' ' }}
					<span class="font-semibold">{{ paginationData.to }}</span>
					{{ ' ' }}
					of
					{{ ' ' }}
					<span class="font-semibold">{{ paginationData.total }}</span>
					{{ ' ' }}
					{{ $t("index.results") }}
				</p>
			</div>
		</div>
		<!-- Pagination -->
		<div class="-mt-2">
			<div class="flex flex-wrap justify-center md:flex-nowrap md:justify-end gap-y-6 gap-x-10 pt-6">
				<nav>
					<ul class="inline-flex items-center space-x-2 rounded-md text-sm">
						<li v-for="(link, key) in paginationData.links" :key="key">
							<Button v-if="!/.*Previous/.test(link.label) && !/Next .*/.test(link.label)" as-child
								:variant="link.active ? 'default' : 'outline'" size="sm"
								:class="['hidden size-8 p-0 lg:flex']" class="bg-blue-800">
								<Link :href="link.url" v-html="link.label">
								</Link>
							</Button>

							<Button v-else-if="/.*Previous/.test(link.label)" as-child variant="outline" size="sm"
								class="size-8 p-0 flex">
								<Link v-if="link.url" :href="link.url">
								<Icon icon="radix-icons:double-arrow-left" class="siz-3" />
								</Link>
								<span v-else>
									<Icon icon="radix-icons:double-arrow-left" class="siz-3" />
								</span>
							</Button>

							<Button v-else-if="/Next .*/.test(link.label)" as-child variant="outline"
								class=" size-8 p-0 flex">
								<Link v-if="link.url" :href="link.url">
								<Icon icon="radix-icons:double-arrow-right" class="siz-3" />
								</Link>
								<span v-else>
									<Icon icon="radix-icons:double-arrow-right" class="siz-3" />
								</span>
							</Button>
						</li>
					</ul>
				</nav>
			</div>
		</div>
	</div>
</template>
