<script setup lang="ts">
import ConsultaModal from '@/components/admin/clinica/consultas/ConsultasModal.vue';
import { Button, ButtonTable } from '@/components/ui/button';
import HeadingSmall from '@/components/ui/header/HeadingSmall.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { Eye, Pencil } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import type { ConsultaListItem } from '@/types';

const page = usePage();
const consultas = ref<ConsultaListItem[]>(Array.isArray(page.props.consultas) ? page.props.consultas : []);
const statusFiltro = ref(page.props.statusFiltro as string || 'todas');

const showModal = ref(false);
const selectedConsulta = ref<ConsultaListItem | null>(null);

function verConsulta(consulta: ConsultaListItem) {
    selectedConsulta.value = consulta;
    showModal.value = true;
}

function filtrarPorStatus(status: string) {
    router.get(route('admin.clinica.consultas.index'), { status }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function getStatusClass(status: string): string {
    switch (status) {
        case 'agendada':
            return 'bg-blue-100 text-blue-800';
        case 'em-andamento':
            return 'bg-yellow-100 text-yellow-800';
        case 'realizada':
            return 'bg-green-100 text-green-800';
        case 'cancelada':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getStatusLabel(status: string): string {
    switch (status) {
        case 'agendada':
            return 'Agendada';
        case 'em-andamento':
            return 'Em Andamento';
        case 'realizada':
            return 'Realizada';
        case 'cancelada':
            return 'Cancelada';
        default:
            return status;
    }
}
</script>

<template>
    <div class="mb-6 flex items-center justify-between">
        <HeadingSmall title="Gerenciar Consultas" />
        <Link :href="route('admin.clinica.consultas.create')">
            <Button> + Nova Consulta </Button>
        </Link>
    </div>

    <!-- Filtros de Status -->
    <div class="mb-6 flex gap-2">
        <button
            @click="filtrarPorStatus('todas')"
            :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition',
                statusFiltro === 'todas' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            ]"
        >
            Todas
        </button>
        <button
            @click="filtrarPorStatus('agendada')"
            :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition',
                statusFiltro === 'agendada' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-600 hover:bg-blue-200'
            ]"
        >
            Agendadas
        </button>
        <button
            @click="filtrarPorStatus('em-andamento')"
            :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition',
                statusFiltro === 'em-andamento' ? 'bg-yellow-600 text-white' : 'bg-yellow-100 text-yellow-600 hover:bg-yellow-200'
            ]"
        >
            Em Andamento
        </button>
        <button
            @click="filtrarPorStatus('realizada')"
            :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition',
                statusFiltro === 'realizada' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-600 hover:bg-green-200'
            ]"
        >
            Realizadas
        </button>
        <button
            @click="filtrarPorStatus('cancelada')"
            :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition',
                statusFiltro === 'cancelada' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-600 hover:bg-red-200'
            ]"
        >
            Canceladas
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Id</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Paciente</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Data</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Horário</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Tipo</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Status</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Valor</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr v-for="consulta in consultas" :key="consulta.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ consulta.id }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        <div>{{ consulta.paciente_nome }}</div>
                        <div class="text-xs text-gray-500">{{ consulta.paciente_telefone }}</div>
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ consulta.data_formatada }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ consulta.horario_formatado }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ consulta.tipo }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusClass(consulta.status)]">
                            {{ getStatusLabel(consulta.status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        R$ {{ consulta.valor }}
                    </td>
                    <td class="flex justify-end gap-3 px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                        <Link :href="route('admin.clinica.consultas.edit', consulta.id)">
                            <ButtonTable :icon="Pencil" label="Editar" variant="ghost" class="text-indigo-600 hover:text-indigo-900" />
                        </Link>
                        <ButtonTable :icon="Eye" label="Ver" variant="ghost" class="text-blue-600 hover:text-blue-900" @click="verConsulta(consulta)" />
                    </td>
                </tr>
                <tr v-if="consultas.length === 0">
                    <td colspan="8" class="py-6 text-center text-gray-500">Nenhuma consulta encontrada.</td>
                </tr>
            </tbody>
        </table>
        <ConsultaModal :show="showModal" :consulta="selectedConsulta" @close="showModal = false" />
    </div>
</template>
