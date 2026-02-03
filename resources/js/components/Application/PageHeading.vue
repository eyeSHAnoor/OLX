<template>
	<div class="md:flex md:items-center md:justify-between lg:my-4 my-3">
		<div class="flex-1 min-w-0">
			<h2 class="text-xl font-semibold leading-7 text-muted-foreground sm:truncate">
				<slot name="title" />
				<slot />
			</h2>
		</div>
		<div class="mt-4 flex md:mt-0 md:ml-4 gap-1.5">
			<slot name="links" class="bg-blue-800" />
		</div>
	</div>
</template>
