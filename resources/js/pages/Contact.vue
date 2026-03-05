<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

import { reactive } from 'vue';

const form = reactive({
    name: '',
    email: '',
    phone: '',
    message: '',
});

const handleSubmit = () => {
    // Aqui você pode fazer um POST para uma rota ou usar o fetch/axios
    console.log('Enviando dados:', form);
};
</script>

<template>
    <Head>
        <title>Família Mogi - Contato</title>
        <meta name="description" content="Anúncio da Família Mogi" />
    </Head>
    <AppLayout>
        <section class=" py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                    <!-- Formulário -->
                    <div>
                        <h2 class="mb-6 text-3xl font-bold">Fale Conosco</h2>
                        <form @submit.prevent="handleSubmit" class="space-y-6 rounded-xl bg-green-50 p-6 shadow-md">
                            <div>
                                <label class="mb-1 block font-medium">Nome:</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Seu nome"
                                    class="w-full rounded-md border border-green-300 px-4 py-2 focus:ring-2 focus:ring-green-400 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="mb-1 block font-medium">Email:</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="Seu e-mail"
                                    class="w-full rounded-md border border-green-300 px-4 py-2 focus:ring-2 focus:ring-green-400 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="mb-1 block font-medium">Telefone:</label>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    placeholder="(99) 9 9999-9999"
                                    class="w-full rounded-md border border-green-300 px-4 py-2 focus:ring-2 focus:ring-green-400 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="mb-1 block font-medium">Assunto:</label>
                                <textarea
                                    v-model="form.message"
                                    rows="4"
                                    placeholder="Escreva sua mensagem aqui..."
                                    class="w-full resize-none rounded-md border border-green-300 px-4 py-2 focus:ring-2 focus:ring-green-400 focus:outline-none"
                                ></textarea>
                            </div>

                            <div class="text-center">
                                <Button
                                    type="submit"
                                    class="rounded-md bg-green-600 px-6 py-2 font-semibold text-white transition duration-200 hover:bg-green-700"
                                >
                                    Enviar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Informações de Contato -->
                    <div class="flex flex-col space-y-6">
                        <div class="rounded-xl border-l-4 border-green-600  p-6 shadow">
                            <h3 class="mb-4 text-xl font-bold">Informações de Contato</h3>
                            <p class="mb-2">
                                <strong>Telefone:</strong><br />
                                (99) 99999-9999
                            </p>
                            <p class="mb-2">
                                <strong>Email:</strong><br />
                                contato@exemplo.com
                            </p>
                            <p class="mb-2">
                                <strong>Endereço:</strong><br />
                                Rua Exemplo, 123 - Bairro Central<br />
                                Mogi das Cruzes - SP
                            </p>
                        </div>

                        <!-- Google Maps -->
                        <div class="h-64 w-full overflow-hidden rounded-xl shadow-lg">
                            <iframe
                                class="h-full w-full"
                                frameborder="0"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"
                                loading="lazy"
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d234069.33976631134!2d-46.35132931263291!3d-23.55646526008725!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94cdd81269172647%3A0xb3859ba7fe8bbb07!2sMogi%20das%20Cruzes%20-%20SP!5e0!3m2!1spt-BR!2sbr!4v1752414806190!5m2!1spt-BR!2sbr"
                            >
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
