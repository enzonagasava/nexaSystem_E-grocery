declare module 'vue-draggable-plus' {
  import { DefineComponent } from 'vue'
  const VueDraggable: DefineComponent<any, any, any>
  export { VueDraggable }
  export default VueDraggable
}

declare function getCookie(name: string): string | null

// Provide the SFC compiler helpers as globals so type-argument usages work
declare function defineProps<T>(): T
declare function defineEmits<T>(): T

// Minimal shims for named `vue` imports used in .vue files to satisfy TS server
declare module 'vue' {
  export function ref<T = any>(value?: T): any
  export function computed<T = any>(fn: any): any
  export function watch(source: any, cb: any, opts?: any): any
  export function onMounted(fn: () => void): void
  export function onUnmounted(fn: () => void): void
  export function reactive<T = any>(obj: T): T
  export function nextTick(fn?: () => void): Promise<void>
  export function useAttrs(): any
  export type DefineComponent = any
  export type HTMLAttributes = Record<string, any>
}
