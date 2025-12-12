<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{ initialLogo?: string | null }>();
const preview = ref(props.initialLogo || null);

const form = useForm<{ logo: File | null }>({
    logo: null,
});

const handleFileChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] || null;
    form.logo = file;
    preview.value = file ? URL.createObjectURL(file) : null;
};

const submit = () => {
    form.post(route('empresa.config.update.logo'), {
        forceFormData: true,
        onSuccess: () => {
            if (form.logo) URL.revokeObjectURL(preview.value!);
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div>
            <label class="mb-2 block font-semibold text-gray-700">Logo da empresa</label>
            <input type="file" @change="handleFileChange" accept="image/*" />
            <InputError :message="form.errors.logo" />
        </div>

        <div v-if="preview" class="mt-2">
            <img :src="preview" alt="Prévia da logo" class="h-24 rounded shadow" />
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
