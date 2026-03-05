<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import { leadsTooltips } from '@/constants/leadsTooltips';

const props = defineProps<{ formRef: any, errors?: any, fetchCep?: Function }>();

</script>

<template>
        <div class="space-y-4">
        <h3 class="text-lg font-semibold border-b pb-2">Conta Bancária</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
            <Label class="block text-sm font-medium text-[var(--text-primary)] mb-2">Banco</Label>
            <select
                v-model="formRef.banco_codigo"
                name="banco_codigo"
                title="Código do banco (COMPE) com 3 dígitos. Exemplo: 001 para Banco do Brasil, 033 para Santander."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Selecione o banco...</option>
                <option value="001">001 - Banco do Brasil</option>
                <option value="033">033 - Santander</option>
                <option value="104">104 - Caixa Econômica</option>
                <option value="237">237 - Bradesco</option>
                <option value="341">341 - Itaú</option>
                <option value="745">745 - Citibank</option>
                <option value="260">260 - Nubank</option>
            </select>
            <p v-if="errors?.banco_codigo" class="text-red-600 text-xs mt-1">{{ errors.banco_codigo }}</p>
            </div>

            <div>
            <Input
                v-model="formRef.banco_nome"
                label="Nome do Banco"
                :tooltip="leadsTooltips.banco_nome"
                name="banco_nome"
                type="text"
                placeholder="Nome completo do banco"
                :error="errors?.banco_nome"
            />
            </div>

            <div>
            <Input
                v-model="formRef.agencia"
                label="Agência"
                :tooltip="leadsTooltips.agencia"
                name="agencia"
                type="text"
                placeholder="0000"
                :error="errors?.agencia"
            />
            </div>

            <div>
            <Input
                v-model="formRef.conta"
                label="Conta"
                :tooltip="leadsTooltips.conta"
                name="conta"
                type="text"
                placeholder="00000-0"
                :error="errors?.conta"
            />
            </div>

            <div>
            <Label class="block text-sm font-medium text-[var(--text-primary)] mb-2">Tipo de Conta</Label>
            <select
                v-model="formRef.conta_tipo"
                name="conta_tipo"
                title="Tipo de conta bancária: Conta Corrente (para transações diárias) ou Conta Poupança (para investimentos)."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Selecione...</option>
                <option value="corrente">Conta Corrente</option>
                <option value="poupanca">Conta Poupança</option>
            </select>
            <p v-if="errors?.conta_tipo" class="text-red-600 text-xs mt-1">{{ errors.conta_tipo }}</p>
            </div>

            <div>
            <Label class="block text-sm font-medium text-[var(--text-primary)] mb-2">Tipo de PIX</Label>
            <select
                v-model="formRef.pix_tipo"
                name="pix_tipo"
                title="Tipo da chave PIX cadastrada pelo lead. Opções: CPF, Celular, E-mail ou Chave Aleatória."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Selecione...</option>
                <option value="cpf">CPF</option>
                <option value="celular">Celular</option>
                <option value="email">Email</option>
                <option value="aleatorio">Chave Aleatória</option>
            </select>
            </div>

            <div class="md:col-span-2">
            <Input
                v-model="formRef.pix"
                label="Chave PIX"
                :tooltip="leadsTooltips.pix"
                name="pix"
                type="text"
                :placeholder="placeholderPix"
                :error="errors?.pix"
            />
            </div>
        </div>
        </div>

</template>
