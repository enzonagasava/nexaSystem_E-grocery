<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { leadsTooltips } from '@/constants/leadsTooltips';

const props = defineProps<{ formRef: any, errors?: any, corretor?: any, imovel?: any, status:any}>();
const form = props.formRef;

console.log(props.status);
</script>

<template>
    <div class="space-y-4">
        <h3 class="text-lg font-semibold border-b pb-2">Gerenciamento</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
            <Select
                :label="'Corretor responsável'"
                :tooltip="'Corretor responsável pelo atendimento e acompanhamento do lead. Se não atribuído, o lead ficará disponível no rodízio.'"
                v-model="form.corretor_id"
                :error="errors?.corretor_id"
                name="corretor_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :options="[
                    { value: '', label: 'Não atribuído' },
                    ...(corretor || []).map(corretores => ({
                        value: corretores.id,
                        label: `${corretores.name}`
                    }))
                ]"
            />
            </div>

            <div>
                <Select
                    :label="'Imóvel de Interesse'"
                    :tooltip="'Imóvel que o lead demonstrou interesse'"
                    v-model="form.imovel"
                    :error="errors?.imovel"
                    name="imovel"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :options="[
                        { value: '', label: 'Selecione um imóvel' },
                        ...(imovel || []).map(imoveis => ({ 
                            value: imoveis.id,
                            label: `${imoveis.nome} - ${imoveis.codigo}`
                        }))
                    ]"
                />
            </div>
            <div class="flex items-center space-x-4">
            <div class="flex items-center group relative">
                <Input
                v-model="form.adicionar_rodizio"
                name="adicionar_rodizio"
                type="checkbox"
                :true-value="true"
                :false-value="false"
                :error="errors?.corretor_id"
                />
                <label class="ml-2 text-sm text-gray-700">
                Adicionar no Rodízio?
                </label>
                <button type="button" class="ml-2 text-gray-400 hover:text-gray-600 cursor-help transition-colors">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </button>
                <div class="hidden group-hover:block absolute z-10 w-56 p-2 text-xs text-white bg-gray-800 rounded shadow-lg bottom-full right-0 mb-2">
                  {{ leadsTooltips.adicionar_rodizio }}
                </div>
            </div>

            <div class="flex items-center group relative">
                <Select
                    :label="'Status'"
                    :tooltip="'Imóvel que o lead demonstrou interesse'"
                    v-model="form.status_id"
                    :error="errors?.status"
                    name="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :options="[
                        { value: '', label: 'Selecione um status' },
                        ...(status || []).map(status => ({ 
                            value: status.id,
                            label: `${status.nome}`
                        }))
                    ]"
                />
                <button type="button" class="ml-2 text-gray-400 hover:text-gray-600 cursor-help transition-colors">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </button>
                <div class="hidden group-hover:block absolute z-10 w-56 p-2 text-xs text-white bg-gray-800 rounded shadow-lg bottom-full right-0 mb-2">
                  {{ leadsTooltips.status }}
                </div>
            </div>
            </div>
        </div>
    </div>

</template>
