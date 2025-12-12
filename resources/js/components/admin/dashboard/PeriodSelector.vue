<script setup lang="ts">
import { Button } from '@/components/ui/button';

const props = defineProps<{
    modelValue: string;
    label?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const periodos = [
    { value: '7', label: '7 dias' },
    { value: '15', label: '15 dias' },
    { value: '30', label: '30 dias' },
    { value: '60', label: '60 dias' },
    { value: '90', label: '90 dias' },
];
</script>

<template>
    <div class="mb-4">
        <label v-if="label" class="mb-2 block text-sm font-medium text-gray-700">{{ label }}</label>
        <div class="flex gap-2">
            <Button
                v-for="periodo in periodos"
                :key="periodo.value"
                :variant="props.modelValue === periodo.value ? 'default' : 'outline'"
                size="sm"
                @click="emit('update:modelValue', periodo.value)"
            >
                {{ periodo.label }}
            </Button>
        </div>
    </div>
</template>
