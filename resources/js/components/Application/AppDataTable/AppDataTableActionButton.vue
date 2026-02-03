<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    icon: string;
    tooltip: string;
    href?: string;
    onClick?: () => void;
    as?: 'a' | 'Link' | 'button';
    target?: string;
    buttonClass?: string;
}>();
</script>

<template>
    <TooltipProvider>
        <Tooltip>
            <TooltipTrigger as-child>
                <Button as-child variant="ghost" size="xs" :class="['size-5.5 rounded-full text-muted-foreground', buttonClass]">

                    <a  v-if="as === 'a'" :href="href" :target>
                        <Icon icon="lucide:printer" class="size-4" />
                    </a>

                    <Link v-else-if="href" :href="href">
                        <Icon :icon="icon" class="size-4" />
                    </Link>

                    <button v-else @click="onClick">
                        <Icon :icon="icon" class="size-4" />
                    </button>

                    <!--                    &lt;!&ndash; Fallback to button if `as` is not specified &ndash;&gt;-->
                    <!--                    <component-->
                    <!--                        :is="as ? as : href ? Link : 'button'"-->
                    <!--                        :href="href"-->
                    <!--                        @click="!href && onClick"-->
                    <!--                    >-->
                    <!--                        <Icon :icon="icon" class="size-4" />-->
                    <!--                    </component>-->
                </Button>
            </TooltipTrigger>
            <TooltipContent>
                <span>{{ tooltip }}</span>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
