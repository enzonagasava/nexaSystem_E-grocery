<script setup lang="ts">
import type { CheckoutForm } from '@/types/forms/checkout-form';
import { onMounted, reactive, watch } from 'vue';

const props = defineProps<{
    form: useForm<CheckoutForm>;
    cartItems: any[];
    cartTotal: any;
}>();

const emit = defineEmits(['update:form', 'whatsapp']);

const localForm = reactive({ ...props.form });

watch(
    () => props.form,
    (newForm) => {
        Object.assign(localForm, newForm);
    },
);

watch(
    localForm,
    (newVal) => {
        emit('update:form', newVal);
    },
    { deep: true },
);

function onSubmit() {
    emit('submit');
}

function onWhatsapp() {
    emit('whatsapp');
}

function registrarListenerCheckout() {
    window.addEventListener('message', (event) => {
        const data = event.data;

        if (!data) return;

        console.log('Mensagem recebida:', data);

        if (data.type === 'checkout_close') {
            console.log('Popup fechado pelo usuário');
            return;
        }

        if (data.type === 'result') {
            console.log('Resultado do pagamento:', data);

            if (data.status === 'approved') {
                window.location.href = '/checkout/sucesso';
            } else {
                window.location.href = '/checkout/falha';
            }
        }
    });
}

function carregarSecurityScript() {
    if (document.getElementById('mp-security')) return;

    const script = document.createElement('script');
    script.id = 'mp-security';
    script.src = 'https://www.mercadopago.com/v2/security.js';
    script.setAttribute('view', 'checkout');
    script.async = true;

    document.head.appendChild(script);
}

async function checarLogin() {
    const res = await fetch('/api/auth/check', {
        credentials: 'include',
    });

    const data = await res.json();
    return data.authenticated;
}

onMounted(() => {
    registrarListenerCheckout();
    carregarSecurityScript();
    checarLogin();
});

const getPublicKey = async () => {
    const r = await fetch('pagamento/keys');
    const json = await r.json();
    return json.public_key;
};

const iniciarPagamento = async () => {
    try {
        const publicKey = await getPublicKey();

        const mp = new window.MercadoPago(publicKey, {
            locale: 'pt-BR',
        });

        const logado = await checarLogin();

        if (!logado) {
            window.location.href = '/login';
            return;
        }

        const items = props.cartItems.map((item) => ({
            title: item.nome || item.key || 'Produto',
            quantity: item.quantidade ?? item.qtd ?? 1,
            unit_price: Number(item.preco),
        }));

        const res = await fetch('api/checkout/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: localForm.name,
                email: localForm.email,
                phone: localForm.telefone,
                price: props.cartTotal,
                items,
                street_name: localForm.endereco,
            }),
        });

        const data = await res.json();
        console.log('DADOS DO BACKEND:', data);

        if (!data.preference_id) {
            console.error('❌ preference_id não retornado');
            return;
        }

        window.pedidoId = data.pedido_id;

        mp.checkout({
            preference: { id: data.preference_id },
            autoOpen: true,
            onReady: () => console.log('Checkout pronto!'),
        });
    } catch (error) {
        console.error('Erro ao iniciar pagamento:', error);
    }
};
</script>

<template>
    <section class="form-section">
        <h2>Informações do Comprador</h2>
        <form @submit.prevent="onSubmit">
            <label for="name">Nome Completo</label>
            <input id="name" v-model="localForm.name" type="text" placeholder="Seu nome completo" required />

            <label for="email">E-mail</label>
            <input id="email" v-model="localForm.email" type="email" placeholder="seu@email.com" required />

            <label for="telefone">Telefone</label>
            <input id="telefone" v-model="localForm.telefone" type="text" placeholder="0000000" required />

            <label for="CEP">CEP</label>
            <input id="CEP" v-model="localForm.CEP" type="text" required />

            <label for="endereco">Endereço</label>
            <input id="endereco" v-model="localForm.endereco" type="text" required />

            <label for="numero">Número</label>
            <input id="numero" v-model="localForm.numero" type="text" required />

            <label for="Cidade">Cidade</label>
            <input id="Cidade" v-model="localForm.Cidade" type="text" required />

            <label for="Estado">Estado</label>
            <input id="Estado" v-model="localForm.Estado" type="text" required />

            <div class="flex h-auto gap-4">
                <button type="submit" @click="iniciarPagamento">Finalizar Pagamento</button>
                <button type="button" @click="onWhatsapp">Pedir no WhatsApp</button>
            </div>
        </form>
    </section>
</template>

<style scoped>
.form-section,
.summary-section {
    flex: 1;
}
h2 {
    margin-bottom: 20px;
    color: #333;
}
label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #555;
}
input,
select {
    width: 100%;
    padding: 10px 14px;
    margin-bottom: 20px;
    border: 1.5px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}
input:focus,
select:focus {
    border-color: #48bb78;
    outline: none;
}
.card-inputs {
    display: flex;
    gap: 15px;
}
.card-inputs > div {
    flex: 1;
}
.summary-section {
    background: #f9fafb;
    border-radius: 10px;
    padding: 20px 25px;
    box-shadow: inset 0 0 10px #e0e5e8;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
button {
    background-color: #48bb78;
    color: white;
    border: none;
    padding: 15px;
    font-size: 18px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 20px;
}
button:hover {
    background-color: #3ca663;
}
@media (max-width: 768px) {
    .container {
        flex-direction: column;
        padding: 20px;
    }
    .form-section,
    .summary-section {
        width: 100%;
    }
    .card-inputs {
        flex-direction: column;
    }
}
</style>
