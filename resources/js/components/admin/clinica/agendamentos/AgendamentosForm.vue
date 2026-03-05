<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import HeadingSmall from '@/components/ui/header/HeadingSmall.vue';
import { router, Link } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import type { Agendamento, Paciente } from '@/types';
import Select from '@/components/ui/select/Select.vue';

const props = defineProps<{
    agendamento?: Agendamento;
    pacientes: Paciente[];
    isEditing?: boolean;
}>();

const form = reactive({
    id: props.agendamento?.id ?? undefined,
    paciente_id: props.agendamento?.paciente_id ?? '',
    data: props.agendamento?.data ?? '',
    hora: props.agendamento?.hora ? props.agendamento.hora.substring(0, 5) : '',
    duracao_minutos: props.agendamento?.duracao_minutos ?? 30,
    tipo: props.agendamento?.tipo ?? 'consulta',
    status: props.agendamento?.status ?? 'pendente',
    observacoes: props.agendamento?.observacoes ?? '',
});

watch(
    () => props.agendamento,
    (novo) => {
        if (novo) {
            Object.assign(form, {
                ...novo,
                hora: novo.hora ? novo.hora.substring(0, 5) : '',
            });
        }
    },
);

function handleSubmit() {
    if (props.isEditing && form.id) {
        router.put(route('admin.clinica.agendamentos.update', form.id), form, {
            onSuccess: () => {},
            onError: (errors) => console.error(errors),
        });
    } else {
        router.post(route('admin.clinica.agendamentos.store'), form, {
            onSuccess: () => {},
            onError: (errors) => console.error(errors),
        });
    }
}

const tiposAgendamento = [
    'Consulta',
    'Retorno',
    'Exame',
    'Procedimento',
    'Telemedicina',
    'Avaliação',
];

const statusOptions = [
    { value: 'pendente', label: 'Pendente' },
    { value: 'confirmado', label: 'Confirmado' },
    { value: 'realizado', label: 'Realizado' },
    { value: 'cancelado', label: 'Cancelado' },
];

const duracaoOptions = [
    { value: 15, label: '15 minutos' },
    { value: 30, label: '30 minutos' },
    { value: 45, label: '45 minutos' },
    { value: 60, label: '1 hora' },
    { value: 90, label: '1h 30min' },
    { value: 120, label: '2 horas' },
];
</script>

<template>
    <div class="flex min-h-screen ">
        <main class="flex-1 p-10">
            <div class="mx-auto rounded  p-8 shadow">
                <HeadingSmall :title="isEditing ? 'Editar Agendamento' : 'Novo Agendamento'" />

                <form @submit.prevent="handleSubmit" class="grid gap-6">
                    <!-- Paciente e Tipo -->
                    <div class="border-b pb-4">
                        <h3 class="mb-4 text-lg font-semibold text-gray-700">Dados do Agendamento</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="paciente_id" class="mb-2 block font-semibold text-gray-700">Paciente *</label>
                                <Select
                                    id="paciente_id"
                                    v-model="form.paciente_id"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                    required
                                >
                                    <option value="">Selecione um paciente</option>
                                    <option v-for="paciente in pacientes" :key="paciente.id" :value="paciente.id">
                                        {{ paciente.nome }} - {{ paciente.telefone }}
                                    </option>
                                </Select>
                            </div>

                            <div>
                                <label for="tipo" class="mb-2 block font-semibold text-gray-700">Tipo *</label>
                                <Select
                                    id="tipo"
                                    v-model="form.tipo"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                    required
                                >
                                    <option v-for="tipo in tiposAgendamento" :key="tipo" :value="tipo.toLowerCase()">
                                        {{ tipo }}
                                    </option>
                                </Select>
                            </div>
                        </div>
                    </div>

                    <!-- Data e Hora -->
                    <div class="border-b pb-4">
                        <h3 class="mb-4 text-lg font-semibold text-gray-700">Data e Horário</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                            <div>
                                <label for="data" class="mb-2 block font-semibold text-gray-700">Data *</label>
                                <input
                                    id="data"
                                    v-model="form.data"
                                    type="date"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                    required
                                />
                            </div>

                            <div>
                                <label for="hora" class="mb-2 block font-semibold text-gray-700">Hora *</label>
                                <input
                                    id="hora"
                                    v-model="form.hora"
                                    type="time"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                    required
                                />
                            </div>

                            <div>
                                <label for="duracao_minutos" class="mb-2 block font-semibold text-gray-700">Duração</label>
                                <Select
                                    id="duracao_minutos"
                                    v-model="form.duracao_minutos"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                >
                                    <option v-for="d in duracaoOptions" :key="d.value" :value="d.value">
                                        {{ d.label }}
                                    </option>
                                </Select>
                            </div>

                            <div>
                                <label for="status" class="mb-2 block font-semibold text-gray-700">Status *</label>
                                <Select
                                    id="status"
                                    v-model="form.status"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                    required
                                >
                                    <option v-for="s in statusOptions" :key="s.value" :value="s.value">
                                        {{ s.label }}
                                    </option>
                                </Select>
                            </div>
                        </div>
                    </div>

                    <!-- Observações -->
                    <div>
                        <label for="observacoes" class="mb-2 block font-semibold text-gray-700">Observações</label>
                        <textarea
                            id="observacoes"
                            v-model="form.observacoes"
                            rows="3"
                            placeholder="Observações adicionais sobre o agendamento..."
                            class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <Link :href="route('admin.clinica.agendamentos.index')">
                            <Button type="button" variant="outline" class="px-6 py-2">
                                Cancelar
                            </Button>
                        </Link>
                        <Button type="submit" class="rounded bg-green-600 px-6 py-2 font-semibold text-white transition hover:bg-green-700">
                            {{ isEditing ? 'Atualizar Agendamento' : 'Salvar Agendamento' }}
                        </Button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>
