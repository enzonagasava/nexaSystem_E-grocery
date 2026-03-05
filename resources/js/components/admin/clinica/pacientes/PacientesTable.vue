<script setup lang="ts">
import PacienteModal from '@/components/admin/clinica/pacientes/PacientesModal.vue';
import { Button, ButtonTable } from '@/components/ui/button';
import HeadingSmall from '@/components/ui/header/HeadingSmall.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Eye, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';
import type { Paciente } from '@/types';

const page = usePage();
const pacientes = ref<Paciente[]>(Array.isArray(page.props.pacientes) ? page.props.pacientes : []);

const showModal = ref(false);
const selectedPaciente = ref<Paciente | null>(null);

function verPaciente(paciente: Paciente) {
    selectedPaciente.value = paciente;
    showModal.value = true;
}
</script>

<template>
    <div class="mb-6 flex items-center justify-between">
        <HeadingSmall title="Gerenciar Pacientes" />
        <Link :href="route('admin.clinica.pacientes.create')">
            <Button> + Adicionar Novo Paciente </Button>
        </Link>
    </div>

    <div class="overflow-x-auto">
<<<<<<< HEAD
        <table class="min-w-full ">
=======
        <table class="min-w-full bg-white">
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Id</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Nome</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">CPF</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Telefone</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">E-mail</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Convênio</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Cadastro</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr v-for="paciente in pacientes" :key="paciente.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ paciente.id }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ paciente.nome }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ paciente.cpf || '-' }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ paciente.telefone }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ paciente.email || '-' }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ paciente.convenio || '-' }}
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-900">
                        {{ paciente.created_at_formatted }}
                    </td>
                    <td class="flex justify-end gap-3 px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                        <Link :href="route('admin.clinica.pacientes.edit', paciente.id)">
                            <ButtonTable :icon="Pencil" label="Editar" variant="ghost" class="text-indigo-600 hover:text-indigo-900" />
                        </Link>
                        <ButtonTable :icon="Eye" label="Ver" variant="ghost" class="text-blue-600 hover:text-blue-900" @click="verPaciente(paciente)" />
                    </td>
                </tr>
                <tr v-if="pacientes.length === 0">
                    <td colspan="8" class="py-6 text-center text-gray-500">Nenhum paciente encontrado.</td>
                </tr>
            </tbody>
        </table>
        <PacienteModal :show="showModal" :paciente="selectedPaciente" @close="showModal = false" />
    </div>
</template>
