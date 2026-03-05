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
    subtitle: 'O CRM com IA que ajuda sua equipe a fechar mais negócios. Gestão de leads, automações e assistente inteligente em uma única plataforma.',
    ctaPrimaryLabel: 'Começar Grátis Instantâneo',
    ctaPrimaryHref: '/register',
    ctaSecondaryLabel: 'Ver Demonstração',
    ctaSecondaryHref: '#funcionalidades',
    stats: [
        { value: '+2.500', label: 'empresas' },
        { value: '+40%', label: 'em vendas' },
    ],
};

const features = [
    { title: 'Gestão de Leads', description: 'Centralize todos os leads em um só lugar, com pipeline visual e etapas personalizáveis.' },
    { title: 'Automações de Fluxo', description: 'Automatize follow-ups, tarefas e notificações para nunca perder uma oportunidade.' },
    { title: 'Catálogo de Produtos', description: 'Cadastre produtos ou imóveis e vincule aos leads e negociações.' },
    { title: 'WhatsApp Integrado', description: 'Atendimento e campanhas direto pelo WhatsApp, sem sair do sistema.' },
    { title: 'Relatórios e Analytics', description: 'Dashboards em tempo real para acompanhar desempenho e metas.' },
    { title: 'Segurança e LGPD', description: 'Dados criptografados e conformidade com a Lei Geral de Proteção de Dados.' },
];

const aiBullets = [
    { title: 'Agendamento 24/7' },
    { title: 'Qualificação automática de leads' },
    { title: 'Follow-up inteligente' },
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
        features: ['Leads ilimitados', 'Até 5 usuários', 'Automação com IA', 'Suporte prioritário', 'Relatórios avançados'],
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
        <title>NexaSystem - CRM com IA</title>
        <meta name="description" content="CRM com IA para automatizar vendas e conquistar mais clientes. Gestão de leads, automações e assistente inteligente." />
    </Head>
    <LandingLayout title="NexaSystem - CRM com IA">
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
            description="O assistente com IA qualifica leads, agenda reuniões e faz follow-up automaticamente, integrado ao seu WhatsApp e ao CRM."
            :bullets="aiBullets"
        />
        <LandingPricing
            title-line1="Planos que"
            title-line2="cabem no seu bolso"
            :plans="plans"
        />
    </LandingLayout>
</template>
