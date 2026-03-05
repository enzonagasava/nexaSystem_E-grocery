<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { EmpresaForm } from '@/types/forms/empresa-form';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{ initialData?: EmpresaForm['redes_sociais'] }>();

const form = useForm({
    facebook: props.initialData?.facebook ?? '',
    instagram: props.initialData?.instagram ?? '',
    linkedin: props.initialData?.linkedin ?? '',
    youtube: props.initialData?.youtube ?? '',
    tiktok: props.initialData?.tiktok ?? '',
    x: props.initialData?.x ?? '',
});

const submit = () => {
    form.patch(route('empresa.config.update.redes'));
};
</script>

<template>
    <form @submit.prevent="submit" class="grid gap-4 md:grid-cols-3">
        <div>
            <Label class="mb-2 block font-semibold text-gray-700" for="facebook">Facebook</Label>
            <Input id="facebook" v-model="form.facebook" placeholder="https://facebook.com/..." />
        </div>

        <div>
            <Label class="mb-2 block font-semibold text-gray-700" for="instagram">Instagram</Label>
            <Input id="instagram" v-model="form.instagram" placeholder="https://instagram.com/..." />
        </div>

        <div>
            <Label class="mb-2 block font-semibold text-gray-700" for="linkedin">LinkedIn</Label>
            <Input id="linkedin" v-model="form.linkedin" placeholder="https://linkedin.com/..." />
        </div>

        <div>
            <Label class="mb-2 block font-semibold text-gray-700" for="youtube">YouTube</Label>
            <Input id="youtube" v-model="form.youtube" placeholder="https://youtube.com/..." />
        </div>

        <div>
            <Label class="mb-2 block font-semibold text-gray-700" for="tiktok">TikTok</Label>
            <Input id="tiktok" v-model="form.tiktok" placeholder="https://tiktok.com/..." />
        </div>

        <div>
            <Label class="mb-2 block font-semibold text-gray-700" for="x">X (Twitter)</Label>
            <Input id="x" v-model="form.x" placeholder="https://x.com/..." />
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
