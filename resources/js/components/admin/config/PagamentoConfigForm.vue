<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button, ButtonPasswordToggle } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { MetodoPagamentoForm } from '@/types/forms/metodo-pagamento';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    MetodoPagamento: MetodoPagamentoForm;
}>();

const showAccess = ref(false);

const form = useForm({
    empresa_id: props.MetodoPagamento.empresa_id,
    public_key: props.MetodoPagamento.public_key,
    access_key: props.MetodoPagamento.access_key,
});

const submit = () => {
    form.patch(route('config.pagamento.update'), {
        preserveScroll: true,
        onSuccess: () => {
            Object.assign(props.MetodoPagamento, form.data());
        },
    });
};
</script>

<template>
    <h2 class="mb-2 font-bold">Api Mercado Pago</h2>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-2">
            <Label for="public_key">Public_Key</Label>
            <Input id="public_key" type="text" v-model="form.public_key" required />
            <InputError class="mt-2" :message="form.errors.public_key" />
        </div>

        <div class="grid gap-2">
            <Label for="access_key">Access_Key</Label>

            <div class="relative">
                <Input id="access_key" :type="showAccess ? 'text' : 'password'" v-model="form.access_key" required class="pr-10" />

                <!-- botão dentro do input -->
                <ButtonPasswordToggle v-model="showAccess" class="absolute inset-y-0 right-2 flex items-center" />
            </div>
            <InputError class="mt-2" :message="form.errors.access_key" />
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
