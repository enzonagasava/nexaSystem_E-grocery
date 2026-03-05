import { cva, type VariantProps } from 'class-variance-authority'

export { default as Button } from './Button.vue'
export { default as ButtonWithLoading } from './Button.vue'
export { default as ButtonTable } from './ButtonTable.vue'
export { default as ButtonPasswordToggle } from './ButtonPasswordToggle.vue'
export { default as ButtonCalendar } from './ButtonCalendar.vue'


export const buttonVariants = cva(
  'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*=\'size-\'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-[var(--ring)] focus-visible:ring-[var(--ring)]/50 focus-visible:ring-[3px] aria-invalid:ring-[var(--destructive)]/20 dark:aria-invalid:ring-[var(--destructive)]/40 aria-invalid:border-[var(--destructive)] cursor-pointer',
  {
    variants: {
      variant: {
        filter:
          'px-3 py-1 rounded-md text-sm bg-[var(--card)] text-[var(--text-primary)] border border-[var(--border)] hover:bg-[var(--primary)] hover:text-[var(--primary-foreground)]',
        section:
          'w-full flex items-center justify-between p-4 hover:bg-secondary/50 rounded-t-lg text-sm font-medium',
        sectionAlt:
          'flex-1 flex items-center justify-between text-left w-full',
        tab:
          'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors',
        primary:
          'bg-[var(--primary)] text-[var(--primary-foreground)] shadow-xs hover:bg-[var(--primary)]/80',
        secondary:
          'bg-[var(--primary)] text-[var(--primary-foreground)] shadow-xs hover:bg-[var(--hover-primary)]',
        destructive:
          'bg-[var(--destructive)] text-[var(--destructive-foreground)] shadow-xs hover:bg-[var(--destructive)]/90 focus-visible:ring-[var(--destructive)]/20 dark:focus-visible:ring-[var(--destructive)]/40',
        outline:
          'border-[var(--input)] bg-[var(--input)] shadow-xs hover:bg-[var(--input)]/50 dark:bg-[var(--input)] dark:border-[var(--input)] dark:hover:bg-[var(--input)]/50',
        ghost:
          'hover:bg-[var(--accent)] hover:text-[var(--accent-foreground)] dark:hover:bg-[var(--accent)]/50',
        link: 'text-[var(--primary)] underline-offset-4 hover:underline',
      },
      size: {
        default: 'h-9 px-4 py-2 has-[>svg]:px-3',
        sm: 'h-8 rounded-md gap-1.5 px-3 has-[>svg]:px-2.5',
        lg: 'h-10 rounded-md px-6 has-[>svg]:px-4',
        icon: 'size-9',
      },
    },
    defaultVariants: {
      variant: 'primary',
      size: 'default',
    },
  },
)

export type ButtonVariants = VariantProps<typeof buttonVariants>
