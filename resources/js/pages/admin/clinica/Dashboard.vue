<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
<<<<<<< HEAD
import { Head, usePage } from '@inertiajs/vue3';
import { Calendar, Users, FileText, Clock, Proportions } from 'lucide-vue-next';

const props = defineProps<{
    modulo?: string;
}>();
const page = usePage();
const pp = page.props as any;
=======
import { Head } from '@inertiajs/vue3';
import { Calendar, Users, FileText, Clock } from 'lucide-vue-next';

defineProps<{
    modulo: string;
}>();
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

const quickLinks = [
    {
        title: 'Pacientes',
        description: 'Gerenciar cadastro de pacientes',
        icon: Users,
        href: '#', // Futuro: route('admin.clinica.pacientes.index')
        color: 'bg-blue-500',
    },
    {
        title: 'Agendamentos',
        description: 'Visualizar e criar agendamentos',
        icon: Calendar,
        href: '#', // Futuro: route('admin.clinica.agendamentos.index')
        color: 'bg-green-500',
    },
    {
        title: 'Prontuários',
        description: 'Acessar prontuários médicos',
        icon: FileText,
        href: '#', // Futuro: route('admin.clinica.prontuarios.index')
        color: 'bg-purple-500',
    },
    {
        title: 'Consultas',
        description: 'Histórico de consultas',
        icon: Clock,
        href: '#', // Futuro: route('admin.clinica.consultas.index')
        color: 'bg-orange-500',
    },
];
</script>

<template>
    <Head>
        <title>Clínica - Dashboard</title>
        <meta name="description" content="Dashboard do módulo Clínica Médica" />
    </Head>

<<<<<<< HEAD
    <AuthLayout :modulo="pp.modulo">
=======
    <AuthLayout>
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
        <div class="flex min-h-screen justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="w-full">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Dashboard Clínica Médica
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Gerencie pacientes, consultas e agendamentos
                    </p>
                </div>

                <!-- Quick Links Grid -->
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="link in quickLinks"
                        :key="link.title"
<<<<<<< HEAD
                        class="rounded-lg  p-6 shadow transition-shadow hover:shadow-lg dark:"
=======
                        class="rounded-lg bg-white p-6 shadow transition-shadow hover:shadow-lg dark:bg-gray-800"
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
                    >
                        <div class="flex items-center">
                            <div :class="[link.color, 'rounded-lg p-3']">
                                <component :is="link.icon" class="h-6 w-6 text-white" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ link.title }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ link.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Placeholder Content -->
<<<<<<< HEAD
                <div class="rounded-lg  p-6 shadow dark:">
=======
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Bem-vindo ao Painel da Clínica
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Este é o dashboard do módulo de clínica médica. Em breve você poderá
                        gerenciar pacientes, consultas, prontuários e agendamentos diretamente
                        por aqui.
                    </p>
                    <div
                        class="mt-4 rounded-lg border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600"
                    >
                        <p class="text-gray-500 dark:text-gray-400">
                            Funcionalidades específicas da clínica serão implementadas aqui.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
