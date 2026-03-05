<script setup lang="ts">
import Select from '@/components/ui/select/Select.vue';
import Input from '@/components/ui/input/Input.vue';
import { imovelTooltips } from '@/constants/imovelTooltips';

const props = defineProps<{ formRef: any, errors?: any }>();
</script>

<template>
  <div class="mt-4 border p-4 rounded">
    <h2 class="font-semibold">Identificação</h2>
    <div class="mt-2">
      <Input 
        v-model="formRef.nome"
        label="Nome do imóvel"
        tooltip="Nome de identificação do imóvel"
        name="nome"
        id="nome"
        :error="errors?.nome"
      />
    </div>
    <div class="mt-2">
      <Input 
        v-model="formRef.codigo"
        label="Código interno"
        :tooltip="imovelTooltips.codigo"
        name="codigo"
        id="codigo"
        :error="errors?.codigo"
      />
    </div>

    <div class="mt-2 grid grid-cols-3 gap-3">
      <div>
        <Select 
          v-model="formRef.status"
          label="Status"
          :tooltip="imovelTooltips.status"
          name="status"
          :error="errors?.status"
          :options="[
            { value: 'ativo', label: 'Ativo' },
            { value: 'inativo', label: 'Inativo' },
            { value: 'vendido', label: 'Vendido' },
            { value: 'reservado', label: 'Reservado' }
          ]"
        />
      </div>

      <div>
        <Select 
          v-model="formRef.categoria"
          label="Categoria"
          :tooltip="imovelTooltips.categoria"
          name="categoria"
          :error="errors?.categoria"
          :options="[
            { value: '', label: '-' },
            { value: 'apartamento', label: 'Apartamento' },
            { value: 'casa', label: 'Casa' },
            { value: 'terreno', label: 'Terreno' },
            { value: 'comercial', label: 'Comercial' }
          ]"
        />
      </div>

      <div>
        <Select 
          v-model="formRef.exclusividade"
          label="Exclusividade"
          :tooltip="imovelTooltips.exclusividade"
          name="exclusividade"
          :error="errors?.exclusividade"
          :options="[
            { value: 0, label: 'Não' },
            { value: 1, label: 'Sim' }
          ]"
        />
      </div>
    </div>

    <div class="mt-2">
      <Select 
        v-model="formRef.condicao"
        label="Condição"
        :tooltip="imovelTooltips.condicao"
        name="condicao"
        :error="errors?.condicao"
        :options="[
          { value: 'novo', label: 'Novo' },
          { value: 'usado', label: 'Usado' },
          { value: 'lancamento', label: 'Lançamento' }
        ]"
      />
    </div>
  </div>
</template>
