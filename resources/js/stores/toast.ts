import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useToastStore = defineStore('toast', () => {
    const message = ref('');
    const type = ref('info');
    const visible = ref(false);
    let timeoutId: number | undefined;

    function show(newMessage: string, newType = 'info', duration = 3000) {
        message.value = newMessage;
        type.value = newType;
        visible.value = true;

        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => {
            visible.value = false;
        }, duration);
    }

    return { message, type, visible, show };
});
