// badge.ts
import { cva, type VariantProps } from 'class-variance-authority'

export { default as Badge } from './Badge.vue'

export const badgeVariants = cva(
    'inline-flex items-center justify-center border px-2 py-0.5 text-xs font-medium w-fit whitespace-nowrap shrink-0 [&>svg]:size-3 gap-1 [&>svg]:pointer-events-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-md transition-colors',
    {
        variants: {
            variant: {
                filled: '',
                outline: 'bg-transparent',
                ghost: 'bg-transparent border-transparent',
                subtle: 'border',
            },
            color: {
                primary: '',
                secondary: '',
                success: '',
                danger: '',
                warning: '',
                info: '',
            },
        },
        compoundVariants: [
            // FILLED
            { variant: 'filled', color: 'primary', class: 'bg-primary text-white border-transparent hover:bg-primary/90' },
            { variant: 'filled', color: 'secondary', class: 'bg-gray-600 text-white border-transparent hover:bg-gray-700' },
            { variant: 'filled', color: 'success', class: 'bg-green-600 text-white border-transparent hover:bg-green-700' },
            { variant: 'filled', color: 'danger', class: 'bg-red-600 text-white border-transparent hover:bg-red-700' },
            { variant: 'filled', color: 'warning', class: 'bg-yellow-500 text-black border-transparent hover:bg-yellow-600' },
            { variant: 'filled', color: 'info', class: 'bg-blue-600 text-white border-transparent hover:bg-blue-700' },

            // OUTLINE
            { variant: 'outline', color: 'primary', class: 'text-primary border-primary hover:bg-indigo-50' },
            { variant: 'outline', color: 'secondary', class: 'text-gray-600 border-gray-600 hover:bg-gray-50' },
            { variant: 'outline', color: 'success', class: 'text-green-600 border-green-600 hover:bg-green-50' },
            { variant: 'outline', color: 'danger', class: 'text-red-600 border-red-600 hover:bg-red-50' },
            { variant: 'outline', color: 'warning', class: 'text-yellow-600 border-yellow-600 hover:bg-yellow-50' },
            { variant: 'outline', color: 'info', class: 'text-blue-600 border-blue-600 hover:bg-blue-50' },

            // GHOST
            { variant: 'ghost', color: 'primary', class: 'text-primary hover:bg-indigo-50' },
            { variant: 'ghost', color: 'secondary', class: 'text-gray-600 hover:bg-gray-50' },
            { variant: 'ghost', color: 'success', class: 'text-green-600 hover:bg-green-50' },
            { variant: 'ghost', color: 'danger', class: 'text-red-600 hover:bg-red-50' },
            { variant: 'ghost', color: 'warning', class: 'text-yellow-600 hover:bg-yellow-50' },
            { variant: 'ghost', color: 'info', class: 'text-blue-600 hover:bg-blue-50' },

            // SUBTLE
            { variant: 'subtle', color: 'primary', class: 'bg-indigo-50 text-primary border-indigo-200 hover:bg-indigo-100' },
            { variant: 'subtle', color: 'secondary', class: 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' },
            { variant: 'subtle', color: 'success', class: 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' },
            { variant: 'subtle', color: 'danger', class: 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' },
            { variant: 'subtle', color: 'warning', class: 'bg-yellow-50 text-yellow-700 border-yellow-200 hover:bg-yellow-100' },
            { variant: 'subtle', color: 'info', class: 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100' },
        ],
        defaultVariants: {
            variant: 'filled',
            color: 'primary',
        },
    },
)

export type BadgeVariants = VariantProps<typeof badgeVariants>
