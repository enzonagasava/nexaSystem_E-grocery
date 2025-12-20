<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import HeadingSmall from '@/components/ui/header/HeadingSmall.vue';
import { router, Link } from '@inertiajs/vue3';
import { vMaska } from 'maska/vue';
import { reactive, watch } from 'vue';
import type { Paciente } from '@/types';

const props = defineProps<{
    paciente?: Paciente;
    isEditing?: boolean;
}>();

const form = reactive({
    id: props.paciente?.id ?? undefined,
    nome: props.paciente?.nome ?? '',
    cpf: props.paciente?.cpf ?? '',
    data_nascimento: props.paciente?.data_nascimento ?? '',
    sexo: props.paciente?.sexo ?? '' as string,
    telefone: props.paciente?.telefone ?? '',
    email: props.paciente?.email ?? '',
    cep: props.paciente?.cep ?? '',
    endereco: props.paciente?.endereco ?? '',
    numero_endereco: props.paciente?.numero_endereco ?? '',
    bairro: props.paciente?.bairro ?? '',
    cidade: props.paciente?.cidade ?? '',
    estado: props.paciente?.estado ?? '',
    convenio: props.paciente?.convenio ?? '',
    numero_convenio: props.paciente?.numero_convenio ?? '',
    observacoes: props.paciente?.observacoes ?? '',
});

watch(
    () => props.paciente,
    (novo) => {
        if (novo) Object.assign(form, novo);
    },
);

function handleSubmit() {
    if (props.isEditing && form.id) {
        router.put(route('admin.clinica.pacientes.update', form.id), form, {
            onSuccess: () => {},
            onError: (errors) => console.error(errors),
        });
    } else {
        router.post(route('admin.clinica.pacientes.store'), form, {
            onSuccess: () => {},
            onError: (errors) => console.error(errors),
        });
    }
}
</script>

<template>
    <div class="flex min-h-screen bg-gray-100">
        <main class="flex-1 p-10">
            <div class="mx-auto rounded bg-white p-8 shadow">
                <HeadingSmall :title="isEditing ? 'Editar Paciente' : 'Adicionar Novo Paciente'" />

                <form @submit.prevent="handleSubmit" class="grid gap-6">
                    <!-- Dados Pessoais -->
                    <div class="border-b pb-4">
                        <h3 class="mb-4 text-lg font-semibold text-gray-700">Dados Pessoais</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="nome" class="mb-2 block font-semibold text-gray-700">Nome Completo *</label>
                                <input
                                    id="nome"
                                    v-model="form.nome"
                                    type="text"
                                    placeholder="Digite o nome do paciente"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                    required
                                />
                            </div>

                            <div>
                                <label for="cpf" class="mb-2 block font-semibold text-gray-700">CPF</label>
                                <input
                                    id="cpf"
                                    v-model="form.cpf"
                                    v-maska="'###.###.###-##'"
                                    type="text"
                                    placeholder="000.000.000-00"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label for="data_nascimento" class="mb-2 block font-semibold text-gray-700">Data de Nascimento</label>
                                <input
                                    id="data_nascimento"
                                    v-model="form.data_nascimento"
                                    type="date"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="sexo" class="mb-2 block font-semibold text-gray-700">Sexo</label>
                                <select
                                    id="sexo"
                                    v-model="form.sexo"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                >
                                    <option value="">Selecione</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="feminino">Feminino</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>

                            <div>
                                <label for="telefone" class="mb-2 block font-semibold text-gray-700">Telefone *</label>
                                <input
                                    id="telefone"
                                    v-model="form.telefone"
                                    v-maska="'(##) #####-####'"
                                    type="text"
                                    placeholder="(11) 99999-9999"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                    required
                                />
                            </div>

                            <div>
                                <label for="email" class="mb-2 block font-semibold text-gray-700">E-mail</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="email@exemplo.com"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Endereço -->
                    <div class="border-b pb-4">
                        <h3 class="mb-4 text-lg font-semibold text-gray-700">Endereço</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                            <div>
                                <label for="cep" class="mb-2 block font-semibold text-gray-700">CEP</label>
                                <input
                                    id="cep"
                                    v-model="form.cep"
                                    v-maska="'#####-###'"
                                    type="text"
                                    placeholder="00000-000"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label for="endereco" class="mb-2 block font-semibold text-gray-700">Endereço</label>
                                <input
                                    id="endereco"
                                    v-model="form.endereco"
                                    type="text"
                                    placeholder="Rua, Avenida..."
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label for="numero_endereco" class="mb-2 block font-semibold text-gray-700">Número</label>
                                <input
                                    id="numero_endereco"
                                    v-model="form.numero_endereco"
                                    type="text"
                                    placeholder="Nº"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="bairro" class="mb-2 block font-semibold text-gray-700">Bairro</label>
                                <input
                                    id="bairro"
                                    v-model="form.bairro"
                                    type="text"
                                    placeholder="Bairro"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label for="cidade" class="mb-2 block font-semibold text-gray-700">Cidade</label>
                                <input
                                    id="cidade"
                                    v-model="form.cidade"
                                    type="text"
                                    placeholder="Cidade"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label for="estado" class="mb-2 block font-semibold text-gray-700">UF</label>
                                <input
                                    id="estado"
                                    v-model="form.estado"
                                    type="text"
                                    maxlength="2"
                                    placeholder="SP"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Convênio -->
                    <div class="border-b pb-4">
                        <h3 class="mb-4 text-lg font-semibold text-gray-700">Convênio</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="convenio" class="mb-2 block font-semibold text-gray-700">Nome do Convênio</label>
                                <input
                                    id="convenio"
                                    v-model="form.convenio"
                                    type="text"
                                    placeholder="Ex: Unimed, Bradesco Saúde..."
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label for="numero_convenio" class="mb-2 block font-semibold text-gray-700">Número do Convênio</label>
                                <input
                                    id="numero_convenio"
                                    v-model="form.numero_convenio"
                                    type="text"
                                    placeholder="Número da carteirinha"
                                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
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
                            placeholder="Observações adicionais sobre o paciente..."
                            class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <Link :href="route('admin.clinica.pacientes.index')">
                            <Button type="button" variant="outline" class="px-6 py-2">
                                Cancelar
                            </Button>
                        </Link>
                        <Button type="submit" class="rounded bg-green-600 px-6 py-2 font-semibold text-white transition hover:bg-green-700">
                            {{ isEditing ? 'Atualizar Paciente' : 'Salvar Paciente' }}
                        </Button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>
