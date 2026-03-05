<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import HeadingSmall from '@/components/ui/header/HeadingSmall.vue';
import { router, Link } from '@inertiajs/vue3';
import { reactive, watch, computed } from 'vue';
import type { Consulta, Paciente } from '@/types';
import Select from '@/components/ui/select/Select.vue';

const props = defineProps<{
    consulta?: Consulta;
    pacientes: Paciente[];
    isEditing?: boolean;
}>();

const form = reactive({
    id: props.consulta?.id ?? undefined,
    paciente_id: props.consulta?.paciente_id ?? '',
    data_consulta: props.consulta?.data_consulta ?? '',
    hora_inicio: props.consulta?.hora_inicio ? props.consulta.hora_inicio.substring(0, 5) : '',
    hora_fim: props.consulta?.hora_fim ? props.consulta.hora_fim.substring(0, 5) : '',
    tipo: props.consulta?.tipo ?? 'consulta',
    status: props.consulta?.status ?? 'agendada',
    valor: props.consulta?.valor ?? '',
    motivo: props.consulta?.motivo ?? '',
    observacoes: props.consulta?.observacoes ?? '',
    diagnostico: props.consulta?.diagnostico ?? '',
    prescricao: props.consulta?.prescricao ?? '',
});

watch(
    () => props.consulta,
    (novo) => {
        if (novo) {
            Object.assign(form, {
                ...novo,
                hora_inicio: novo.hora_inicio ? novo.hora_inicio.substring(0, 5) : '',
                hora_fim: novo.hora_fim ? novo.hora_fim.substring(0, 5) : '',
            });
        }
    },
);

function handleSubmit() {
    if (props.isEditing && form.id) {
        router.put(route('admin.clinica.consultas.update', form.id), form, {
            onSuccess: () => {},
            onError: (errors) => console.error(errors),
        });
    } else {
        router.post(route('admin.clinica.consultas.store'), form, {
            onSuccess: () => {},
            onError: (errors) => console.error(errors),
        });
    }
}

const tiposConsulta = [
    'Consulta',
    'Retorno',
    'Exame',
    'Procedimento',
    'Emergência',
    'Telemedicina',
];

const statusOptions = [
    { value: 'agendada', label: 'Agendada' },
    { value: 'em-andamento', label: 'Em Andamento' },
    { value: 'realizada', label: 'Realizada' },
    { value: 'cancelada', label: 'Cancelada' },
];
</script>

<template>
    <div class="flex min-h-screen ">
        <main class="flex-1 p-10">
            <div class="mx-auto rounded  p-8 shadow">
                <HeadingSmall :title="isEditing ? 'Editar Consulta' : 'Nova Consulta'" />

                <form @submit.prevent="handleSubmit" class="grid gap-6">
                    <!-- Paciente e Data -->
                    <div class="border-b pb-4">
                        <h3 class="mb-4 text-lg font-semibold text-gray-700">Dados da Consulta</h3>
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
                                    <option v-for="tipo in tiposConsulta" :key="tipo" :value="tipo.toLowerCase()">
                                        {{ tipo }}
                                    </option>
                                </Select>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-4">
                            <div>
                                <label for="data_consulta" class="mb-2 block font-semibold text-gray-700">Data *</label>
                                <input
                                    id="data_consulta"
                                    v-model="form.data_consulta"
                                    type="date"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                    required
                                />
                            </div>

                            <div>
                                <label for="hora_inicio" class="mb-2 block font-semibold text-gray-700">Hora Início *</label>
                                <input
                                    id="hora_inicio"
                                    v-model="form.hora_inicio"
                                    type="time"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                    required
                                />
                            </div>

                            <div>
                                <label for="hora_fim" class="mb-2 block font-semibold text-gray-700">Hora Fim</label>
                                <input
                                    id="hora_fim"
                                    v-model="form.hora_fim"
                                    type="time"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
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

                    <!-- Valor e Motivo -->
                    <div class="border-b pb-4">
                        <h3 class="mb-4 text-lg font-semibold text-gray-700">Informações Adicionais</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="valor" class="mb-2 block font-semibold text-gray-700">Valor (R$)</label>
                                <input
                                    id="valor"
                                    v-model="form.valor"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0,00"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label for="motivo" class="mb-2 block font-semibold text-gray-700">Motivo da Consulta</label>
                                <input
                                    id="motivo"
                                    v-model="form.motivo"
                                    type="text"
                                    placeholder="Motivo ou queixa principal"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Campos Médicos (para edição) -->
                    <div v-if="isEditing" class="border-b pb-4">
                        <h3 class="mb-4 text-lg font-semibold text-gray-700">Registros Médicos</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="diagnostico" class="mb-2 block font-semibold text-gray-700">Diagnóstico</label>
                                <textarea
                                    id="diagnostico"
                                    v-model="form.diagnostico"
                                    rows="3"
                                    placeholder="Diagnóstico do paciente..."
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                ></textarea>
                            </div>

                            <div>
                                <label for="prescricao" class="mb-2 block font-semibold text-gray-700">Prescrição</label>
                                <textarea
                                    id="prescricao"
                                    v-model="form.prescricao"
                                    rows="3"
                                    placeholder="Prescrição médica..."
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                ></textarea>
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
                            placeholder="Observações adicionais..."
                            class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <Link :href="route('admin.clinica.consultas.index')">
                            <Button type="button" variant="outline" class="px-6 py-2">
                                Cancelar
                            </Button>
                        </Link>
                        <Button type="submit" class="rounded bg-green-600 px-6 py-2 font-semibold text-white transition hover:bg-green-700">
                            {{ isEditing ? 'Atualizar Consulta' : 'Salvar Consulta' }}
                        </Button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>
