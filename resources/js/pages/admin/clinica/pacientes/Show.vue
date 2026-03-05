<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { Paciente } from '@/types';

const page = usePage();
const paciente = page.props.paciente as Paciente;
</script>

<template>
    <Head>
        <title>Detalhes do Paciente - Clínica</title>
    </Head>

    <AuthLayout>
        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden  shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200  p-6">
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-800">Detalhes do Paciente</h2>
                            <div class="flex gap-3">
                                <Link :href="route('admin.clinica.pacientes.edit', paciente.id)">
                                    <Button variant="outline">Editar</Button>
                                </Link>
                                <Link :href="route('admin.clinica.pacientes.index')">
                                    <Button variant="ghost">Voltar</Button>
                                </Link>
                            </div>
                        </div>

                        <!-- Dados Pessoais -->
                        <div class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b pb-2">Dados Pessoais</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="font-medium text-gray-600">Nome:</span>
                                    <p class="text-gray-900">{{ paciente.nome }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">CPF:</span>
                                    <p class="text-gray-900">{{ paciente.cpf || '-' }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Data de Nascimento:</span>
                                    <p class="text-gray-900">{{ paciente.data_nascimento || '-' }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Idade:</span>
                                    <p class="text-gray-900">{{ paciente.idade ? `${paciente.idade} anos` : '-' }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Sexo:</span>
                                    <p class="text-gray-900 capitalize">{{ paciente.sexo || '-' }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Telefone:</span>
                                    <p class="text-gray-900">{{ paciente.telefone }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">E-mail:</span>
                                    <p class="text-gray-900">{{ paciente.email || '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b pb-2">Endereço</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <span class="font-medium text-gray-600">Endereço Completo:</span>
                                    <p class="text-gray-900">{{ paciente.endereco_completo }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">CEP:</span>
                                    <p class="text-gray-900">{{ paciente.cep || '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Convênio -->
                        <div class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b pb-2">Convênio</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="font-medium text-gray-600">Convênio:</span>
                                    <p class="text-gray-900">{{ paciente.convenio || '-' }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Número do Convênio:</span>
                                    <p class="text-gray-900">{{ paciente.numero_convenio || '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Observações -->
                        <div v-if="paciente.observacoes" class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b pb-2">Observações</h3>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ paciente.observacoes }}</p>
                        </div>

                        <!-- Metadados -->
                        <div class="text-sm text-gray-500">
                            Cadastrado em: {{ paciente.created_at_formatted }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
