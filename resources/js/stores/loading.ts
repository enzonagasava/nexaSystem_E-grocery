import { defineStore } from 'pinia';

interface LoadingState {
    isVisible: boolean;
    requestCount: number;
}

export const useLoadingStore = defineStore('loading', {
    state: (): LoadingState => ({
        isVisible: false,
        requestCount: 0,
    }),
    actions: {
        show() {
            this.requestCount++;
            this.isVisible = true;
        },
        hide() {
            this.requestCount = Math.max(0, this.requestCount - 1);
            if (this.requestCount === 0) {
                this.isVisible = false;
            }
        },
        forceHide() {
            this.requestCount = 0;
            this.isVisible = false;
        },
    },
});
