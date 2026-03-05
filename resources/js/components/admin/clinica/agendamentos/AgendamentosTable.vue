<script setup lang="ts">
import AgendamentoModal from '@/components/admin/clinica/agendamentos/AgendamentosModal.vue';
import { Button, ButtonTable } from '@/components/ui/button';
import HeadingSmall from '@/components/ui/header/HeadingSmall.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { Eye, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';
import type { AgendamentoListItem } from '@/types';

const page = usePage();
const agendamentos = ref<AgendamentoListItem[]>(Array.isArray(page.props.agendamentos) ? page.props.agendamentos : []);
const statusFiltro = ref(page.props.statusFiltro as string || 'todos');

const showModal = ref(false);
const selectedAgendamento = ref<AgendamentoListItem | null>(null);

function verAgendamento(agendamento: AgendamentoListItem) {
    selectedAgendamento.value = agendamento;
    showModal.value = true;
}

function filtrarPorStatus(status: string) {
    router.get(route('admin.clinica.agendamentos.index'), { status }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function getStatusClass(status: string): string {
    switch (status) {
        case 'pendente':
            return 'bg-yellow-100 text-yellow-800';
        case 'confirmado':
            return 'bg-blue-100 text-blue-800';
        case 'realizado':
            return 'bg-green-100 text-green-800';
        case 'cancelado':
            return 'bg-red-100 text-red-800';
        default:
            return ' text-gray-800';
    }
}

function getStatusLabel(status: string): string {
    switch (status) {
        case 'pendente':
            return 'Pendente';
        case 'confirmado':
            return 'Confirmado';
        case 'realizado':
            return 'Realizado';
        case 'cancelado':
            return 'Cancelado';
        default:
            return status;
    }
}
</script>

<template>
    <div class="mb-6 flex items-center justify-between">
        <HeadingSmall title="Gerenciar Agendamentos" />
        <Link :href="route('admin.clinica.agendamentos.create')">
            <Button> + Novo Agendamento </Button>
        </Link>
    </div>

    <!-- Filtros de Status -->
    <div class="mb-6 flex gap-2">
        <Button
            @click="filtrarPorStatus('todos')"
            :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition',
                statusFiltro === 'todos' ? ' text-white' : ' text-gray-600 hover:bg-gray-200'
            ]"
        >
            Todos
        </Button>
        <Button
            @click="filtrarPorStatus('pendente')"
            :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition',
                statusFiltro === 'pendente' ? 'bg-yellow-600 text-white' : 'bg-yellow-100 text-yellow-600 hover:bg-yellow-200'
            ]"
        >
            Pendentes
        </Button>
        <Button
            @click="filtrarPorStatus('confirmado')"
            :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition',
                statusFiltro === 'confirmado' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-600 hover:bg-blue-200'
            ]"
        >
            Confirmados
        </Button>
        <Button
            @click="filtrarPorStatus('realizado')"
            :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition',
                statusFiltro === 'realizado' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-600 hover:bg-green-200'
            ]"
        >
            Realizados
        </Button>
        <Button
            @click="filtrarPorStatus('cancelado')"
            :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition',
                statusFiltro === 'cancelado' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-600 hover:bg-red-200'
            ]"
        >
            Cancelados
        </Button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full ">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Id</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Paciente</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Data</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Hora</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Duração</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Tipo</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Status</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr v-for="agendamento in agendamentos" :key="agendamento.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ agendamento.id }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        <div>{{ agendamento.paciente_nome }}</div>
                        <div class="text-xs text-gray-500">{{ agendamento.paciente_telefone }}</div>
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ agendamento.data_formatada }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ agendamento.hora_formatada }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ agendamento.duracao_minutos }} min
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ agendamento.tipo }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusClass(agendamento.status)]">
                            {{ getStatusLabel(agendamento.status) }}
                        </span>
                    </td>
                    <td class="flex justify-end gap-3 px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                        <Link :href="route('admin.clinica.agendamentos.edit', agendamento.id)">
                            <ButtonTable :icon="Pencil" label="Editar" variant="ghost" class="text-indigo-600 hover:text-indigo-900" />
                        </Link>
                        <ButtonTable :icon="Eye" label="Ver" variant="ghost" class="text-blue-600 hover:text-blue-900" @click="verAgendamento(agendamento)" />
                    </td>
                </tr>
                <tr v-if="agendamentos.length === 0">
                    <td colspan="8" class="py-6 text-center text-gray-500">Nenhum agendamento encontrado.</td>
                </tr>
            </tbody>
        </table>
        <AgendamentoModal :show="showModal" :agendamento="selectedAgendamento" @close="showModal = false" />
    </div>
</template>
