<script setup lang="ts">
export interface AIBullet {
    title: string;
}

const defaultMessages = [
    { author: 'user' as const, text: 'Olá, estou interessado no apartamento do Jardim São Judas.' },
    { author: 'ai' as const, text: 'Olá! Que ótimo. Posso agendar uma visita para você. Qual o melhor dia e horário?' },
    { author: 'user' as const, text: 'Amanhã às 14h pode ser?' },
    { author: 'ai' as const, text: 'Perfeito! Visita agendada para amanhã às 14h. Você receberá a confirmação por WhatsApp.' },
];

defineProps<{
    titleLine1: string;
    titleLine2: string;
    description: string;
    bullets: AIBullet[];
    chatMessages?: { author: 'user' | 'ai'; text: string }[];
}>();
</script>

<template>
    <section
        id="automacao-ia"
        class="px-4 py-16 sm:px-6 lg:px-8"
        style="background-color: var(--landing-bg);"
    >
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">
                        <span style="color: var(--landing-foreground);">{{ titleLine1 }}</span>
                        <span style="color: var(--landing-accent-cyan);"> {{ titleLine2 }}</span>
                    </h2>
                    <p class="mt-4 text-lg" style="color: var(--landing-muted);">
                        {{ description }}
                    </p>
                    <ul class="mt-6 space-y-4">
                        <li
                            v-for="(bullet, i) in bullets"
                            :key="i"
                            class="flex items-center gap-3"
                        >
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                                style="background-color: var(--primary); color: var(--primary-foreground);"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span style="color: var(--landing-foreground);">{{ bullet.title }}</span>
                        </li>
                    </ul>
                </div>
                <div>
                    <!-- Chat mockup -->
                    <div
                        class="rounded-xl border p-4 shadow-xl"
                        style="background-color: var(--card); border-color: var(--border);"
                    >
                        <div class="mb-4 flex items-center gap-2">
                            <div
                                class="h-2 w-2 rounded-full"
                                style="background-color: var(--landing-accent-cyan);"
                            />
                            <span class="text-sm font-medium" style="color: var(--landing-muted);">AI NexaSystem</span>
                        </div>
                        <div class="space-y-3">
                            <div
                                v-for="(msg, i) in (chatMessages ?? defaultMessages)"
                                :key="i"
                                class="flex"
                                :class="msg.author === 'user' ? 'justify-end' : 'justify-start'"
                            >
                                <div
                                    class="max-w-[85%] rounded-lg px-4 py-2 text-sm"
                                    :style="msg.author === 'user'
                                        ? { backgroundColor: 'var(--primary)', color: 'var(--primary-foreground)' }
                                        : { backgroundColor: 'var(--muted)', color: 'var(--card-foreground)' }"
                                >
                                    {{ msg.text }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
