<script setup lang="ts">
export interface FeatureItem {
    title: string;
    description: string;
    icon?: string;
}

defineProps<{
    titleLine1: string;
    titleLine2: string;
    features: FeatureItem[];
}>();
</script>

<template>
    <section
        id="funcionalidades"
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
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(feature, i) in features"
                    :key="i"
                    class="rounded-xl border p-6 transition hover:border-opacity-80"
                    style="background-color: var(--card); border-color: var(--border); color: var(--card-foreground);"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full"
                        style="background: linear-gradient(135deg, var(--primary), var(--sidebar-accent)); color: var(--primary-foreground);"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold" style="color: var(--landing-foreground);">
                        {{ feature.title }}
                    </h3>
                    <p class="mt-2 text-sm" style="color: var(--landing-muted);">
                        {{ feature.description }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
