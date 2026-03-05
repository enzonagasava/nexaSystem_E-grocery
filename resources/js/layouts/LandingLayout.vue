<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';

defineProps<{
    title?: string;
    brandName?: string;
    showCorretorSubtitle?: boolean;
}>();
</script>

<template>
    <Head :title="title" />
    <div
        class="flex min-h-screen flex-col"
        style="background-color: var(--landing-bg); color: var(--landing-foreground);"
    >
        <LandingHeader
            :brand-name="brandName"
            :show-corretor-subtitle="showCorretorSubtitle"
        />
        <main class="flex-1 pt-16">
            <slot />
        </main>
        <LandingFooter :brand-name="brandName" />
    </div>
</template>
