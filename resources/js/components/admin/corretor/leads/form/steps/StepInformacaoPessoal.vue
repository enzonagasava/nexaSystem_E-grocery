<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Select from '@/components/ui/select/Select.vue';
import Switch from '@/components/ui/switch/Switch.vue';
import { leadsTooltips } from '@/constants/leadsTooltips';
import Button from '@/components/ui/button/Button.vue';

const props = defineProps<{ formRef: any, errors?: any, fetchCep?: Function }>();

const form = props.formRef;

const removerContato = (index: number) => {
  form.contatos.splice(index, 1)
}


const adicionarRedeSocial = () => {
  form.redes_sociais.push({ plataforma: '', url: '' })
}

const removerRedeSocial = (index: number) => {
  form.redes_sociais.splice(index, 1)
}

const adicionarContato = () => {
  form.contatos.push({ numero: '', tipo: 'celular', principal: false })
}

</script>

<template>
        <div class="space-y-4">
            <h3 class="text-lg font-semibold border-b pb-2">Informações Pessoais</h3>
            
            <div>
                <Input
                    v-model="form.nome_completo"
                    label="Nome Completo *"
                    :tooltip="leadsTooltips.nome_completo"
                    name="endereco"
                    type="text"
                    :error="errors?.nome_completo"
                />
            </div>

            <div>
            <Input
                    v-model="form.email"
                    label="E-mail *"
                    :tooltip="leadsTooltips.email"
                    name="email"
                    type="text"
                    :error="errors?.email"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
            <div>
                <Select
                :label="'Gênero'"
                :tooltip="leadsTooltips.genero"
                v-model="form.genero"
                :error="errors?.genero"
                name="genero"
                title="Gênero do lead. Selecione uma opção para personalizar a comunicação. Opções: Masculino, Feminino, Outro ou Prefiro não informar."
                :options="[
                    { value: 'masculino', label: 'Masculino' },
                    { value: 'feminino', label: 'Feminino' },
                    { value: 'outro', label: 'Outro' },
                    { value: 'prefiro_nao_informar', label: 'Prefiro não informar' } 
                ]"
                />
            </div>

            <div>
                <Input
                v-model="form.data_nascimento"
                label="Data de Nascimento"
                :tooltip="leadsTooltips.data_nascimento"
                name="data_nascimento"
                type="date"
                />
            </div>
            </div>
        </div>
            <div class="space-y-4">
            <h3 class="text-lg font-semibold border-b pb-2">Documentos</h3>
            
            <div>
            <Input
              v-model="form.cpf"
              label="CPF"
              :tooltip="leadsTooltips.cpf"
              name="cpf"
              id="cpf"
              type="text"
              placeholder="000.000.000-00"
              :error="errors?.cpf"
            />
            </div>

            <div>
            <Input
              v-model="form.rg"
              label="RG"
              :tooltip="leadsTooltips.rg"
              name="rg"
              id="rg"
              placeholder="Digite o número do RG"
              :error="errors?.rg"
            />
            </div>
        </div>

        <div class="flex justify-between items-center mt-3 mb-3">
            <h3 class="text-lg font-semibold">Contatos</h3>
            <Button
            @click="adicionarContato"
            >
            + Adicionar Contato
            </Button>
        </div>

        <div v-for="(contato, index) in form.contatos" :key="index" class="grid grid-cols-12 gap-4 items-end mb-3">
            <div class="col-span-5">
            <Input
              v-model="contato.numero"
              label="Número *"
              :tooltip="leadsTooltips['contatos.numero']"
              :name="`contatos[${index}][numero]`"
              :id="`telefone_${index}`"
              type="text"
              placeholder="(11) 99999-9999"
              :error="errors?.contatos"
            />
            </div>

            <div class="col-span-4">
            <Select
                v-model="contato.tipo"
                :label="'Tipo do Número *'"
                :tooltip="leadsTooltips['contatos.tipo']"
                :name="`contatos[${index}][tipo]`"
                :options="[
                    { value: 'whatsApp', label: 'WhatsApp' },
                    { value: 'celular', label: 'Celular' },
                    { value: 'fixo', label: 'Telefone Fixo' },
                    { value: 'comercial', label: 'Comercial' }
                ]"
            />
            </div>

            <div class="col-span-2 flex items-center gap-2">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <Switch
                    :label="'Principal'"
                    v-model="contato.principal"
                    :name="`contatos[${index}][principal]`"
                    :tooltip="leadsTooltips['contatos.principal']"
                    :true-value="true"
                    :false-value="false"
                  />
                </div>
              </div>
            </div>

            <div class="col-span-1">
            <Button
                @click="removerContato(index)"
                v-if="form.contatos.length > 1">
                ×
            </Button>
            </div>
        </div>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">Redes Sociais</h3>
                <Button
                type="button"
                @click="adicionarRedeSocial"
                class="bg-primary text-white px-4 py-2 rounded disabled:opacity-50"
                >
                + Adicionar Rede Social
            </Button>
            </div>

            <div v-for="(rede, index) in form.redes_sociais" :key="index" class="grid grid-cols-12 gap-4 items-end">
                <div class="col-span-5">
                <Select
                    :label="'Plataforma'"
                    v-model="rede.plataforma"
                    :name="`redes_sociais[${index}][plataforma]`"
                    title="Selecione a plataforma da rede social: Facebook, Instagram, LinkedIn, Twitter/X, TikTok, YouTube, WhatsApp ou Telegram."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Selecione...</option>
                    <option value="facebook">Facebook</option>
                    <option value="instagram">Instagram</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="twitter">Twitter/X</option>
                    <option value="tiktok">TikTok</option>
                    <option value="youtube">YouTube</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="telegram">Telegram</option>
                </Select>
                </div>

                <div class="col-span-4">
                <Input
                    v-model="rede.url"
                    label="URL/Link"
                    :tooltip="leadsTooltips['redes_sociais.url']"
                    :name="`redes_sociais[${index}][url]`"
                    type="url"
                />
                </div>
                <div class="col-span-1">
                <Button
                    @click="removerRedeSocial(index)"
                >
                    ×
                </Button>
                </div>
            </div>
        </div>

</template>
