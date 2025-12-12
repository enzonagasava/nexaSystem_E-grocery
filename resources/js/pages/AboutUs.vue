<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Família Mogi - Sobre Nós</title>
        <meta name="description" content="Anúncio da Família Mogi" />
    </Head>
    <AppLayout>
        <div class="flex min-h-screen justify-center px-4 py-12 text-black sm:px-6 lg:px-8">
            <div class="w-[1366px]">
                <h1 style="font-size: 2.2rem; margin-bottom: 15px; font-weight: 700">Sobre a Família Mogi</h1>
                <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 20px">
                    Na Família Mogi, acreditamos que a alimentação saudável começa com ingredientes frescos, naturais e de qualidade. Somos um mercado
                    online dedicado a levar até a sua casa os melhores cogumelos, verduras e legumes cultivados com carinho e responsabilidade.
                </p>
                <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 20px">
                    Nossa missão é conectar você diretamente aos produtores locais, garantindo produtos fresquinhos, colhidos no auge do sabor e da
                    nutrição. Valorizamos a sustentabilidade e o respeito ao meio ambiente, por isso trabalhamos com práticas agrícolas conscientes e
                    parceiros que compartilham desses mesmos valores.
                </p>
                <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 20px">
                    Aqui, você encontra praticidade e confiança para fazer suas compras, além de um atendimento que entende suas necessidades e
                    promove uma experiência de compra simples e agradável.
                </p>
                <p style="font-size: 1.1rem; line-height: 1.6; font-weight: 600">
                    Venha fazer parte da Família Mogi e descubra o prazer de se alimentar bem, cuidando da sua saúde e do planeta!
                </p>
            </div>
        </div>
    </AppLayout>
</template>
