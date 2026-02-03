import { cva, type VariantProps } from 'class-variance-authority'

export { default as Alert } from './Alert.vue'
export { default as AlertDescription } from './AlertDescription.vue'
export { default as AlertTitle } from './AlertTitle.vue'

export const alertVariants = cva(
    'relative w-full rounded-lg border px-4 py-3 text-sm grid has-[>svg]:grid-cols-[calc(var(--spacing)*4)_1fr] grid-cols-[0_1fr] has-[>svg]:gap-x-3 gap-y-0.5 items-start [&>svg]:size-4 [&>svg]:translate-y-0.5 [&>svg]:text-current',
    {
        variants: {
            variant: {
                default: 'bg-card text-card-foreground',
                destructive:
                    'text-destructive bg-card [&>svg]:text-current *:data-[slot=alert-description]:text-destructive/90',
                success: "border-green-500/50 text-green-500 bg-green-100 [&>svg]:text-green-500 [&>[data-slot=alert-description]]:text-green-800",
                warning: "border-yellow-500/50 bg-yellow-100 text-yellow-500 [&>svg]:text-yellow-500 *:data-[slot=alert-description]:text-yellow-800",
                info: "border-blue-500/50 text-blue-500 bg-blue-100 [&>svg]:text-blue-500 *:data-[slot=alert-description]:text-blue-800",
                danger: "border-red-500/50 text-red-500 bg-red-100 [&>svg]:text-red-500 *:data-[slot=alert-description]:text-red-800",
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    },
)
export type AlertVariants = VariantProps<typeof alertVariants>
