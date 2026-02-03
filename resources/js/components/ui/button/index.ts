import { cva, type VariantProps } from 'class-variance-authority';

export { default as Button } from './Button.vue';

export const buttonVariants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm transition-all disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*=\'size-\'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
    {
        variants: {
            variant: {
                default:
                    'bg-primary text-primary-foreground  hover:bg-primary/80',
                destructive:
                    'bg-destructive text-white  hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60',
                outline:
                    'border bg-background  hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:border-input dark:hover:bg-input/50',
                secondary:
                    'bg-accent/50 border border-muted-foreground/30 text-secondary-foreground  hover:bg-secondary/80 hover:text-primary hover:border-primary',
                ghost:
                    'hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50',
                link: 'text-primary underline-offset-4 hover:underline',
                success:
                    'bg-green-600 text-white hover:bg-green-600 focus-visible:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus-visible:ring-green-500',
                danger:
                    'bg-red-600 text-white hover:bg-red-600 focus-visible:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus-visible:ring-red-500',
                info:
                    'bg-blue-500 text-white hover:bg-blue-600 focus-visible:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus-visible:ring-blue-500',
                warning:
                    'bg-yellow-400 text-black hover:bg-yellow-500 focus-visible:ring-yellow-300 dark:bg-yellow-500 dark:hover:bg-yellow-600 dark:focus-visible:ring-yellow-400'
            },
            size: {
                default: 'h-9 px-4 py-2 has-[>svg]:px-3 leading-none',
                xs: 'h-6 gap-1.5 px-3 has-[>svg]:px-1.5 leading-none text-xs rounded-sm',
                sm: 'h-8 gap-1.5 px-3 has-[>svg]:px-2.5 leading-none text-sm',
                lg: 'h-10 px-6 has-[>svg]:px-4 leading-tight',
                icon: 'size-9',
            },
            rounded: {
                none: 'rounded-none',
                sm: 'rounded-sm',
                md: 'rounded-md',
                lg: 'rounded-lg',
                full: 'rounded-full',
                extra: 'rounded-2xl', // or use `rounded-xl` if you prefer
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
            rounded: 'md',
        }
    }
);

export type ButtonVariants = VariantProps<typeof buttonVariants>
