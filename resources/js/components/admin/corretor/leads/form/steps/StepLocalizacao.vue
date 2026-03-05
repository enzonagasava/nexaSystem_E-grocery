<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import { Select } from '@/components/ui/select';
import { leadsTooltips } from '@/constants/leadsTooltips';
import { vMaska } from 'maska/vue';
import { watch } from 'vue';

const props = defineProps<{ formRef: any, errors?: any }>();

async function fetchCep() {
  const form = props.formRef.value || props.formRef;
  const cepRaw = String(form.cep || '').replace(/\D/g, '');
  if (!cepRaw || cepRaw.length !== 8) return;
  
  try {
    const res = await fetch(`https://viacep.com.br/ws/${cepRaw}/json/`);
    const data = await res.json();
    
    if (!data || data.erro) return;
    
    // Atualizar diretamente - o Vue 3 gerencia a reatividade
    form.rua = data.logradouro || form.rua;
    form.bairro = data.bairro || form.bairro;
    form.cidade = data.localidade || form.cidade;
    form.estado = data.uf || form.estado;
  } catch (err) {
    console.error('Erro ao buscar CEP:', err);
  }
}

// ✅ Watch seguro
watch(
  () => props.formRef?.cep,
  (newCep) => {
    if (newCep && newCep.replace(/\D/g, '').length === 8) {
      fetchCep();
    }
  }
);
</script>

<template>
    <div class="space-y-4">
        <h3 class="text-lg font-semibold border-b pb-2">Endereço</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
            <Input
              v-model="formRef.cep"
              label="CEP *"
              :tooltip="leadsTooltips.cep"
              @blur="fetchCep"
              name="cep"
              type="text"
              v-maska="'#####-###'"
              placeholder="00000-000"
              :error="errors?.cep"
            />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
            <Input
            v-model="formRef.rua"
            label="Endereço (rua) *"
            :tooltip="leadsTooltips.endereco"
            name="rua"
            type="text"
            :error="errors?.endereco"
            />
            </div>

            <div class="grid grid-cols-2 gap-4">
            <div>
                <Input
                  v-model="formRef.numero"
                  label="Número"
                  :tooltip="leadsTooltips.numero"
                  name="numero"
                  type="number"
                />
            </div>

            <div>
                <Input
                  v-model="formRef.complemento"
                  label="Complemento"
                  :tooltip="leadsTooltips.complemento"
                  name="complemento"
                  type="text"
                  :error="errors?.complemento"
                />
            </div>
            </div>

            <div>
            <Input             
              v-model="formRef.bairro"
              label="Bairro *"
              :tooltip="leadsTooltips.bairro"
              name="bairro"
              type="text"
              :error="errors?.bairro"
            />
            </div>

            <div>
            <Input
              v-model="formRef.cidade"
              label="Cidade *"
              :tooltip="leadsTooltips.cidade"
              name="cidade"
              type="text"
              :error="errors?.cidade"
            />
            </div>

            <div>
                <Select
                    v-model="formRef.estado"
                    name="estado"
                    title="Estado/UF de residência. Selecionado automaticamente ao consultar o CEP."
                >
                    <!-- Opções via slot -->
                    <option value="">Selecione o estado</option>
                    <option value="AC">Acre</option>
                    <option value="AL">Alagoas</option>
                    <option value="AP">Amapá</option>
                    <option value="AM">Amazonas</option>
                    <option value="BA">Bahia</option>
                    <option value="CE">Ceará</option>
                    <option value="DF">Distrito Federal</option>
                    <option value="ES">Espírito Santo</option>
                    <option value="GO">Goiás</option>
                    <option value="MA">Maranhão</option>
                    <option value="MT">Mato Grosso</option>
                    <option value="MS">Mato Grosso do Sul</option>
                    <option value="MG">Minas Gerais</option>
                    <option value="PA">Pará</option>
                    <option value="PB">Paraíba</option>
                    <option value="PR">Paraná</option>
                    <option value="PE">Pernambuco</option>
                    <option value="PI">Piauí</option>
                    <option value="RJ">Rio de Janeiro</option>
                    <option value="RN">Rio Grande do Norte</option>
                    <option value="RS">Rio Grande do Sul</option>
                    <option value="RO">Rondônia</option>
                    <option value="RR">Roraima</option>
                    <option value="SC">Santa Catarina</option>
                    <option value="SP">São Paulo</option>
                    <option value="SE">Sergipe</option>
                    <option value="TO">Tocantins</option>
                </Select>
            <p v-if="errors?.estado" class="text-red-600 text-xs mt-1">{{ errors.estado }}</p>
            </div>
        </div>
        </div>

</template>
