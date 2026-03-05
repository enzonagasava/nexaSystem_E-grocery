<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

export interface PlanItem {
    name: string;
    price: string;
    description?: string;
    features: string[];
    ctaLabel: string;
    ctaHref: string;
    highlighted?: boolean;
}

defineProps<{
    titleLine1: string;
    titleLine2: string;
    plans: PlanItem[];
}>();
</script>

<template>
    <section
        id="planos"
        class="px-4 py-16 sm:px-6 lg:px-8"
        style="background-color: var(--landing-bg);"
    >
        <div class="mx-auto max-w-7xl">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">
                    <span style="color: var(--landing-foreground);">{{ titleLine1 }}</span>
                    <span style="color: var(--landing-accent-cyan);"> {{ titleLine2 }}</span>
                </h2>
            </div>
            <div class="mt-12 grid gap-8 lg:grid-cols-3">
                <div
                    v-for="(plan, i) in plans"
                    :key="i"
                    class="rounded-xl border p-6 transition"
                    :class="plan.highlighted ? 'ring-2' : ''"
                    :style="plan.highlighted
                        ? { backgroundColor: 'var(--card)', borderColor: 'var(--primary)', boxShadow: '0 0 20px rgba(81, 35, 136, 0.3)' }
                        : { backgroundColor: 'var(--card)', borderColor: 'var(--border)' }"
                >
                    <h3 class="text-xl font-bold" style="color: var(--landing-foreground);">
                        {{ plan.name }}
                    </h3>
                    <p class="mt-2 text-2xl font-extrabold" style="color: var(--landing-accent-cyan);">
                        {{ plan.price }}
                    </p>
                    <p
                        v-if="plan.description"
                        class="mt-1 text-sm"
                        style="color: var(--landing-muted);"
                    >
                        {{ plan.description }}
                    </p>
                    <ul class="mt-6 space-y-3">
                        <li
                            v-for="(feature, j) in plan.features"
                            :key="j"
                            class="flex items-center gap-2 text-sm"
                            style="color: var(--landing-muted);"
                        >
                            <svg
                                class="h-5 w-5 shrink-0"
                                style="color: var(--primary);"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ feature }}
                        </li>
                    </ul>
                    <Link
                        :href="plan.ctaHref"
                        class="mt-6 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium transition hover:opacity-90"
                        :style="plan.highlighted
                            ? { backgroundColor: 'var(--primary)', color: 'var(--primary-foreground)' }
                            : { borderWidth: '1px', borderColor: 'var(--landing-foreground)', color: 'var(--landing-foreground)' }"
                    >
                        {{ plan.ctaLabel }}
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
