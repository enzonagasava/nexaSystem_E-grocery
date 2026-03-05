import { cva, type VariantProps } from 'class-variance-authority'

export { default as Checkbox } from './Checkbox.vue'
export { default as CheckboxSolid } from './CheckboxSolid.vue'
export { default as CheckboxOutline } from './CheckboxOutline.vue'

export const checkboxVariants = cva(
	'peer shrink-0 transition-shadow outline-none disabled:cursor-not-allowed disabled:opacity-50',
	{
		variants: {
			variant: {
				default:
					'size-4 rounded-[4px] border shadow-xs data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground data-[state=checked]:border-primary focus-visible:border-ring focus-visible:ring-ring/50',
				solid:
					'size-5 rounded-md border shadow-sm data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground data-[state=checked]:border-primary focus-visible:border-ring focus-visible:ring-ring/50',
				outline:
					'size-4 rounded-[4px] border-[var(--border)] bg-[var(--card)] data-[state=checked]:text-primary data-[state=checked]:border-primary focus-visible:border-ring focus-visible:ring-ring/50',
			},
		},
		defaultVariants: {
			variant: 'default',
		},
	},
)

export type CheckboxVariants = VariantProps<typeof checkboxVariants>
