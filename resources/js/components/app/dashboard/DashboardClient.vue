<script setup lang="ts">
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

const page = usePage();

const emailVerificado = computed(() => {
    return !!page.props.user.email_verified_at;
});

const user = reactive({
    name: page.props.user.name || '',
    telefone: page.props.user.telefone || '',
    email: page.props.user.email || '',
});

const novaSenha = ref('');
const confirmarSenha = ref('');
const editando = ref(false);

const activeTab = ref('info');

function salvar() {
    if (novaSenha.value !== confirmarSenha.value) {
        alert('As senhas não conferem!');
        return;
    }

    // Dados a enviar
    const dados = {
        name: user.name,
        telefone: user.telefone,
        email: user.email,
    };

    // Se a senha nova foi preenchida, envia também
    if (novaSenha.value) {
        dados['password'] = novaSenha.value;
        dados['password_confirmation'] = confirmarSenha.value;
    }

    // Envia via Inertia para a rota de atualização (exemplo: 'user.update')
    Inertia.post('/user/update', dados, {
        onSuccess: () => {
            editando.value = false;
            novaSenha.value = '';
            confirmarSenha.value = '';
            alert('Dados atualizados com sucesso!');
        },
        onError: (errors) => {
            alert('Erro ao atualizar: ' + JSON.stringify(errors));
        },
    });
}
</script>

<template>
    <div class="mx-auto flex max-w-6xl gap-8 p-6">
        <!-- Barra vertical de abas -->
        <nav class="flex w-48 flex-col border-r border-gray-300 pr-4">
            <button
                :class="[
                    'rounded px-4 py-3 text-left',
                    activeTab === 'info' ? 'bg-blue-600 font-semibold text-white' : 'text-gray-700 hover:bg-gray-100',
                ]"
                @click="activeTab = 'info'"
            >
                Informações Gerais
            </button>
            <button
                :class="[
                    'mt-2 rounded px-4 py-3 text-left',
                    activeTab === 'endereco' ? 'bg-blue-600 font-semibold text-white' : 'text-gray-700 hover:bg-gray-100',
                ]"
                @click="activeTab = 'endereco'"
            >
                Endereço
            </button>
            <button
                :class="[
                    'mt-2 rounded px-4 py-3 text-left',
                    activeTab === 'historico' ? 'bg-blue-600 font-semibold text-white' : 'text-gray-700 hover:bg-gray-100',
                ]"
                @click="activeTab = 'historico'"
            >
                Histórico de Compras
            </button>
        </nav>

        <!-- Conteúdo das abas -->
        <section class="flex-1">
            <h1 class="mb-6 text-2xl font-bold">Dashboard do Cliente</h1>

            <div v-if="activeTab === 'info'">
                <h2 class="mb-4 text-xl font-semibold">Informações Gerais & Configurar Email e Senha</h2>

                <form @submit.prevent="salvar" class="max-w-md space-y-4">
                    <div>
                        <label class="mb-1 block font-medium" for="nome">Nome:</label>
                        <input id="nome" type="text" v-model="user.name" class="w-full rounded border p-2" required />
                    </div>

                    <div>
                        <label class="mb-1 block font-medium" for="telefone">Telefone:</label>
                        <input id="telefone" type="tel" v-model="user.telefone" class="w-full rounded border p-2" required />
                    </div>

                    <div>
                        <label class="mb-1 block font-medium" for="email">Email:</label>
                        <div class="flex items-center">
                            <input id="email" type="email" v-model="user.email" class="w-full rounded border p-2" required />

                            <span v-if="emailVerificado" title="Email verificado" class="text-green-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2.5"
                                    stroke="currentColor"
                                    class="h-6 w-6"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </span>

                            <span v-else title="Email não verificado" class="ml-2 text-red-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2.5"
                                    stroke="currentColor"
                                    class="h-6 w-6"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9.75 9.75l4.5 4.5M14.25 9.75l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block font-medium" for="novaSenha">Nova senha:</label>
                        <input
                            id="novaSenha"
                            type="password"
                            v-model="novaSenha"
                            class="w-full rounded border p-2"
                            placeholder="Deixe vazio para não alterar"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block font-medium" for="confirmarSenha">Confirmar senha:</label>
                        <input
                            id="confirmarSenha"
                            type="password"
                            v-model="confirmarSenha"
                            class="w-full rounded border p-2"
                            placeholder="Confirme a nova senha"
                        />
                    </div>

                    <div class="flex space-x-4">
                        <button v-if="!editando" type="button" class="rounded bg-blue-600 px-4 py-2 text-white" @click="editando = true">
                            Editar
                        </button>

                        <button v-else type="submit" class="rounded bg-green-600 px-4 py-2 text-white">Salvar</button>

                        <button
                            v-if="editando"
                            type="button"
                            class="rounded bg-gray-400 px-4 py-2 text-black"
                            @click="
                                editando = false;
                                novaSenha = '';
                                confirmarSenha = '';
                            "
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>

            <div v-if="activeTab === 'endereco'">
                <h2 class="mb-4 text-xl font-semibold">Endereço</h2>
                <p><strong>Rua:</strong></p>
                <p><strong>Número:</strong></p>
                <p><strong>Cidade:</strong></p>
                <p><strong>Estado:</strong></p>
                <p><strong>CEP:</strong></p>
            </div>

            <div v-if="activeTab === 'historico'">
                <h2 class="mb-4 text-xl font-semibold">Histórico de Compras</h2>
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 p-2 text-left">ID</th>
                            <th class="border border-gray-300 p-2 text-left">Produto</th>
                            <th class="border border-gray-300 p-2 text-left">Data</th>
                            <th class="border border-gray-300 p-2 text-left">Valor (R$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-gray-300 p-2"></td>
                            <td class="border border-gray-300 p-2"></td>
                            <td class="border border-gray-300 p-2"></td>
                            <td class="border border-gray-300 p-2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* Você pode ajustar estilos adicionais aqui */
</style>
