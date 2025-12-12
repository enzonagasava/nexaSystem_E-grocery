<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { User } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { vMaska } from 'maska/vue';

// Recebe o user do pai
const props = defineProps<{
    user: User;
}>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    numero: props.user.numero,
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.patch(route('config.update'), {
        preserveScroll: true,
        onSuccess: () => {
            Object.assign(props.user, form.data());

            form.reset('current_password', 'password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-2">
            <Label for="name">Nome</Label>
            <Input id="name" v-model="form.name" required autocomplete="name" placeholder="Nome completo" />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="email">Email</Label>
            <Input id="email" type="email" v-model="form.email" required autocomplete="email" placeholder="Endereço de email" />
            <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <div class="grid gap-2">
            <Label for="numero">Número <span class="text-xs text-gray-500">* para o bot da IA</span></Label>
            <Input
                id="numero"
                type="text"
                v-maska="'(##) #####-####'"
                v-model="form.numero"
                required
                autocomplete="of"
                placeholder="Ex: (11) 23456-7890"
            />
            <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <div class="grid gap-2">
            <Label for="current_password">Senha atual</Label>
            <Input
                id="current_password"
                ref="currentPasswordInput"
                v-model="form.current_password"
                type="password"
                class="mt-1 block w-full"
                autocomplete="current-password"
                placeholder="Senha atual"
            />
            <InputError :message="form.errors.current_password" />
        </div>

        <div class="grid gap-2">
            <Label for="password">Senha nova</Label>
            <Input
                id="password"
                ref="passwordInput"
                v-model="form.password"
                type="password"
                class="mt-1 block w-full"
                autocomplete="new-password"
                placeholder="Senha nova"
            />
            <InputError :message="form.errors.password" />
        </div>

        <div class="grid gap-2">
            <Label for="password_confirmation">Confirme a senha</Label>
            <Input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="mt-1 block w-full"
                autocomplete="new-password"
                placeholder="Confirme a senha"
            />
            <InputError :message="form.errors.password_confirmation" />
        </div>

        <div class="flex items-center gap-4">
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
