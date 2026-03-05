<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

export interface HeroStat {
    label: string;
    value: string;
}

defineProps<{
    titleLine1: string;
    titleLine2: string;
    subtitle: string;
    ctaPrimaryLabel?: string;
    ctaPrimaryHref?: string;
    ctaSecondaryLabel?: string;
    ctaSecondaryHref?: string;
    stats?: HeroStat[];
}>();
</script>

<template>
    <section class="relative overflow-hidden px-4 pb-16 pt-12 sm:px-6 lg:px-8 lg:pt-20">
        <div class="mx-auto max-w-7xl">
            <div class="mx-auto max-w-3xl text-center">
                <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl md:text-6xl">
                    <span style="color: var(--landing-foreground);">{{ titleLine1 }}</span>
                    <br />
                    <span style="color: var(--landing-accent-cyan);">{{ titleLine2 }}</span>
                </h1>
                <p
                    class="mx-auto mt-4 max-w-2xl text-lg"
                    style="color: var(--landing-muted);"
                >
                    {{ subtitle }}
                </p>
                <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <Link
                        v-if="ctaPrimaryLabel && ctaPrimaryHref"
                        :href="ctaPrimaryHref"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg px-6 py-3 text-base font-medium transition hover:opacity-90 sm:w-auto"
                        style="background-color: var(--primary); color: var(--primary-foreground);"
                    >
                        {{ ctaPrimaryLabel }}
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                    <Link
                        v-if="ctaSecondaryLabel && ctaSecondaryHref"
                        :href="ctaSecondaryHref"
                        class="inline-flex w-full items-center justify-center rounded-lg border px-6 py-3 text-base font-medium transition hover:opacity-90 sm:w-auto"
                        style="border-color: var(--landing-foreground); color: var(--landing-foreground);"
                    >
                        {{ ctaSecondaryLabel }}
                    </Link>
                </div>
                <div
                    v-if="stats && stats.length"
                    class="mt-10 flex flex-wrap items-center justify-center gap-6 text-sm"
                    style="color: var(--landing-muted);"
                >
                    <span
                        v-for="(stat, i) in stats"
                        :key="i"
                        class="flex items-center gap-2"
                    >
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full"
                            style="background-color: var(--primary); color: var(--primary-foreground);"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        {{ stat.value }} {{ stat.label }}
                    </span>
                </div>
            </div>
            <!-- Hero visual slot (mockup / image) -->
            <div class="mt-12 flex justify-center">
                <slot>
                    <!-- Placeholder: CRM mockup style block -->
                    <div
                        class="max-w-2xl rounded-xl border p-6 shadow-2xl"
                        style="background-color: var(--card); border-color: var(--border); color: var(--card-foreground);"
                    >
                        <div class="mb-4 flex items-center gap-2">
                            <div
                                class="h-3 w-3 rounded-full"
                                style="background-color: var(--primary);"
                            />
                            <div
                                class="h-3 w-3 rounded-full opacity-60"
                                style="background-color: var(--primary);"
                            />
                            <div
                                class="h-3 w-3 rounded-full opacity-40"
                                style="background-color: var(--primary);"
                            />
                            <span class="ml-2 text-sm font-medium" style="color: var(--landing-muted);">CRM - Lead Management</span>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div
                                class="rounded-lg p-4"
                                style="background-color: var(--muted);"
                            >
                                <div class="h-2 w-1/2 rounded" style="background-color: var(--primary); opacity: 0.5;" />
                                <div class="mt-2 h-2 w-3/4 rounded" style="background-color: var(--muted-foreground); opacity: 0.3;" />
                            </div>
                            <div
                                class="rounded-lg p-4"
                                style="background-color: var(--muted);"
                            >
                                <div class="h-2 w-1/2 rounded" style="background-color: var(--primary); opacity: 0.5;" />
                                <div class="mt-2 h-2 w-3/4 rounded" style="background-color: var(--muted-foreground); opacity: 0.3;" />
                            </div>
                        </div>
                        <div class="mt-4 h-24 rounded-lg" style="background: linear-gradient(90deg, var(--primary) 0%, var(--sidebar-accent) 100%); opacity: 0.2;" />
                    </div>
                </slot>
            </div>
        </div>
    </section>
</template>
