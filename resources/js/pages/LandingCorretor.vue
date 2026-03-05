<script setup lang="ts">
import LandingLayout from '@/layouts/LandingLayout.vue';
import LandingHero from '@/components/landing/LandingHero.vue';
import LandingFeatures from '@/components/landing/LandingFeatures.vue';
import LandingAIAssistant from '@/components/landing/LandingAIAssistant.vue';
import LandingPricing from '@/components/landing/LandingPricing.vue';
import { Head } from '@inertiajs/vue3';

const hero = {
    titleLine1: 'Automatize suas vendas.',
    titleLine2: 'Conquiste mais clientes.',
    subtitle: 'O CRM feito sob medida para corretores de imóveis. Feche mais negócios com gestão de leads, automação com IA e WhatsApp integrado.',
    ctaPrimaryLabel: 'Começar Grátis Instantâneo',
    ctaPrimaryHref: '/register',
    ctaSecondaryLabel: 'Ver Demonstração',
    ctaSecondaryHref: '#funcionalidades',
    stats: [
        { value: '+2.500', label: 'corretores' },
        { value: '+40%', label: 'em vendas' },
    ],
};

const features = [
    { title: 'Autoatendimento com IA', description: 'Atendimento 24h para qualificar leads e agendar visitas automaticamente.' },
    { title: 'Gestão de Leads', description: 'Pipeline visual para acompanhar cada lead do primeiro contato até a venda.' },
    { title: 'Gerenciamento de Agendas', description: 'Calendário integrado e lembretes para visitas e follow-ups.' },
    { title: 'Catálogo de Imóveis', description: 'Cadastre imóveis, fotos e documentos em um só lugar.' },
    { title: 'WhatsApp Integrado', description: 'Conversas e campanhas direto pelo WhatsApp, sem trocar de tela.' },
    { title: 'Automações de Fluxo', description: 'Automatize respostas, qualificação e follow-up para nunca perder um lead.' },
    { title: 'Segurança e LGPD', description: 'Seus dados e dos clientes protegidos em conformidade com a LGPD.' },
];

const aiBullets = [
    { title: 'Agendamento 24/7' },
    { title: 'Qualificação automática' },
    { title: 'Follow-up inteligente' },
];

const chatMessages = [
    { author: 'user' as const, text: 'Olá, estou interessado no apartamento do Jardim São Judas.' },
    { author: 'ai' as const, text: 'Olá! Que ótimo. Posso agendar uma visita para você. Qual o melhor dia e horário?' },
    { author: 'user' as const, text: 'Amanhã às 14h pode ser?' },
    { author: 'ai' as const, text: 'Perfeito! Visita agendada para amanhã às 14h. Você receberá a confirmação por WhatsApp.' },
];

const plans = [
    {
        name: 'Starter',
        price: 'R$ 97/mês',
        features: ['Até 500 leads', '1 usuário', 'Suporte por e-mail', 'Integração WhatsApp'],
        ctaLabel: 'Começar Agora',
        ctaHref: '/register',
        highlighted: false,
    },
    {
        name: 'Profissional',
        price: 'R$ 197/mês',
        features: ['Leads ilimitados', 'Até 5 usuários', 'Automação com IA', 'Suporte prioritário', 'Catálogo de imóveis'],
        ctaLabel: 'Começar Agora',
        ctaHref: '/register',
        highlighted: true,
    },
    {
        name: 'Enterprise',
        price: 'Sob consulta',
        features: ['Tudo do Profissional', 'Usuários ilimitados', 'API dedicada', 'Gerente de sucesso'],
        ctaLabel: 'Falar com Vendas',
        ctaHref: 'https://wa.me/5511988404647',
        highlighted: false,
    },
];
</script>

<template>
    <Head>
        <title>NexaSystem - CRM para Corretores</title>
        <meta name="description" content="CRM com IA para corretores de imóveis. Gestão de leads, automação, catálogo de imóveis e WhatsApp integrado." />
    </Head>
    <LandingLayout
        title="NexaSystem - CRM para Corretores"
        show-corretor-subtitle
    >
        <LandingHero
            :title-line1="hero.titleLine1"
            :title-line2="hero.titleLine2"
            :subtitle="hero.subtitle"
            :cta-primary-label="hero.ctaPrimaryLabel"
            :cta-primary-href="hero.ctaPrimaryHref"
            :cta-secondary-label="hero.ctaSecondaryLabel"
            :cta-secondary-href="hero.ctaSecondaryHref"
            :stats="hero.stats"
        />
        <LandingFeatures
            title-line1="Tudo que você precisa em"
            title-line2="um só lugar"
            :features="features"
        />
        <LandingAIAssistant
            title-line1="Seu assistente de vendas que"
            title-line2="nunca dorme"
            description="O assistente qualifica leads e agenda visitas automaticamente, 24 horas. Integrado ao WhatsApp para confirmações e follow-up."
            :bullets="aiBullets"
            :chat-messages="chatMessages"
        />
        <LandingPricing
            title-line1="Planos que"
            title-line2="cabem no seu bolso"
            :plans="plans"
        />
    </LandingLayout>
</template>
