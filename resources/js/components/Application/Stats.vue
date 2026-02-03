<script setup>
const props = defineProps({
	heading: String,
	title: String,
	description: String,
	route: String,
	icon: String,
});
</script>

<template>
	<Card class="!p-0">
		<div class="px-4 py-4 flex flex-col gape-y-0 w-full h-full">
			<div v-if="heading || icon" class="flex items-center justify-between gap-3 mb-3">
				<h4 class="tracking-tight font-medium">{{ heading }}</h4>
				<Icon :icon="icon" class="size-5 text-muted-foreground" />
			</div>
			<CardTitle v-if="title" class="text-2xl fon-semibold">{{ title }}</CardTitle>
			<CardDescription>{{ description }}</CardDescription>
		</div>
	</Card>
	<!-- <SectionCard class="custom-card-section">
        <div>
            <div class="flex justify-between items-start px-5 py-3">
                <div>
                    <h1 class="font-semibold text-2xl">{{ title }}</h1>
                    <p class="text-light">{{ __(description) }}</p>
                </div>
                <div>
                    <span v-if="route" class="custom-card-section-span">
                        <Link :href="route"><Icon :name="icon" class="h-8  "/></Link>
					</span>
                    <span v-else class="custom-card-section-span">
                        <Icon :name="icon" class="h-8"/>
					</span>
                </div>
            </div>
        </div>
    </SectionCard> -->
</template>
