import { defineStore } from 'pinia';

interface CartItem {
    id: number;
    quantidade: number;
}

interface CartState {
    items: Record<number, CartItem>;
}

export const useCartStore = defineStore('cart', {
    state: (): CartState => ({
        items: {},
    }),
    getters: {
        cartQuantity: (state) => Object.values(state.items).reduce((sum, item) => sum + item.quantidade, 0),
    },
    actions: {
        setCart(payload: CartItem[] | Record<string, CartItem>) {
            const itensArray = Array.isArray(payload) ? payload : Object.values(payload ?? {});

            this.items = {};
            itensArray.forEach((item) => {
                this.items[item.id] = item;
            });
        },
        addItem(id: number, quantidade = 1) {
            if (this.items[id]) {
                this.items[id].quantidade += quantidade;
            } else {
                this.items[id] = { id, quantidade };
            }
        },
        clearCart() {
            this.items = {};
        },
    },
});
