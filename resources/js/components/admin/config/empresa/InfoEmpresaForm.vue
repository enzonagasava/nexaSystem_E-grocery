<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { EmpresaForm } from '@/types/forms/empresa-form';
import { useForm } from '@inertiajs/vue3';
import { vMaska } from 'maska/vue';

const props = defineProps<{ initialData?: Partial<EmpresaForm> }>();

const form = useForm<EmpresaForm>({
    nome: props.initialData?.nome ?? '',
    email: props.initialData?.email ?? '',
    numero_wpp: props.initialData?.numero_wpp ?? '',
    telefone: props.initialData?.telefone ?? '',
    cnpj: props.initialData?.cnpj ?? '',
    endereco: props.initialData?.endereco ?? '',
    cep: props.initialData?.cep ?? '',
    numero_endereco: props.initialData?.numero_endereco ?? '',
    municipio: props.initialData?.municipio ?? '',
    estado: props.initialData?.estado ?? '',
});

const submit = () => {
    form.patch(route('empresa.config.update.geral'));
};
</script>

<template>
    <form @submit.prevent="submit">
        <div class="mb-6 grid gap-6 md:grid-cols-3">
            <div>
                <Label class="mb-2 block font-semibold text-gray-700" for="nome">Nome da Empresa</Label>
                <Input id="nome" v-model="form.nome" required placeholder="Nome da Empresa" />
                <InputError class="mt-2" :message="form.errors.nome" />
            </div>

            <div>
                <Label class="mb-2 block font-semibold text-gray-700" for="email">Email Empresarial</Label>
                <Input id="email" type="email" v-model="form.email" required placeholder="Email empresarial" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <Label class="mb-2 block font-semibold text-gray-700" for="numero_wpp">Número WhatsApp</Label>
                <Input id="numero_wpp" type="text" v-maska="'(##) #####-####'" v-model="form.numero_wpp" required placeholder="(11) 98765-4321" />
                <InputError class="mt-2" :message="form.errors.numero_wpp" />
            </div>

            <div>
                <Label class="mb-2 block font-semibold text-gray-700" for="telefone">Telefone Fixo</Label>
                <Input id="telefone" type="text" v-maska="'(##) ####-####'" v-model="form.telefone" placeholder="(11) 3333-3333" />
                <InputError class="mt-2" :message="form.errors.telefone" />
            </div>

            <div>
                <Label class="mb-2 block font-semibold text-gray-700" for="cnpj">CNPJ</Label>
                <Input id="cnpj" type="text" v-maska="'##.###.###/####-##'" v-model="form.cnpj" placeholder="00.000.000/0000-00" />
                <InputError class="mt-2" :message="form.errors.cnpj" />
            </div>
        </div>

        <div>
            <h3 class="mb-2 block font-semibold text-gray-700">Endereço:</h3>
            <div class="mb-5 grid gap-6 md:grid-cols-3">
                <div>
                    <Label class="mb-2 block font-semibold text-gray-700" for="cep">CEP</Label>
                    <Input id="cep" type="text" v-maska="'#####-###'" v-model="form.cep" placeholder="00000-000" />
                    <InputError class="mt-2" :message="form.errors.cep" />
                </div>

                <div class="md:col-span-2">
                    <Label class="mb-2 block font-semibold text-gray-700" for="endereco">Endereço</Label>
                    <Input id="endereco" type="text" v-model="form.endereco" placeholder="Rua Exemplo, Bairro" />
                    <InputError class="mt-2" :message="form.errors.endereco" />
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <div>
                    <Label class="mb-2 block font-semibold text-gray-700" for="numero_endereco">Número</Label>
                    <Input id="numero_endereco" type="text" v-model="form.numero_endereco" placeholder="123" />
                    <InputError class="mt-2" :message="form.errors.numero_endereco" />
                </div>
                <div>
                    <Label class="mb-2 block font-semibold text-gray-700" for="municipio">Município</Label>
                    <Input id="municipio" type="text" v-model="form.municipio" placeholder="São Paulo" />
                    <InputError class="mt-2" :message="form.errors.municipio" />
                </div>

                <div>
                    <Label class="mb-2 block font-semibold text-gray-700" for="estado">UF</Label>
                    <Input id="estado" type="text" v-model="form.estado" placeholder="SP" />
                    <InputError class="mt-2" :message="form.errors.estado" />
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-4">
            <Button :disabled="form.processing">Salvar</Button>

            <Transition
                enter-active-class="transition ease-in-out"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out"
                leave-to-class="opacity-0"
            >
                <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Salvo com sucesso.</p>
            </Transition>
        </div>
    </form>
</template>
