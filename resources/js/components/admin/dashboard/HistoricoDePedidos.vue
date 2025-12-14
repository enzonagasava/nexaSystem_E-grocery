  <script setup lang="ts">
const {
    historico,
    carregarPagina
} = defineProps<{
    historico: {
        data: Array<any>;
        current_page: number;
        last_page: number;
        next_page_url: string | null;
        prev_page_url: string | null;
    };
    carregarPagina: (url: string | null) => void;
}>();

  </script>

  <template>
              <div class="bg-white shadow-lg rounded-xl p-6">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Histórico das Últimas Compras</h2>

              <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                      <tr>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produtos</th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                      </tr>
                  </thead>

                  <tbody v-if="historico && historico.data" class="divide-y divide-gray-200 bg-white">
                      <tr v-for="(item, index) in historico.data" :key="index">

                          <td class="px-4 py-3">
                              {{ item.cliente }}
                              <div class="text-xs text-gray-500">
                                  {{ item.itens }} itens • {{ item.tempo }}
                              </div>
                          </td>

                          <td class="px-4 py-3">
                              {{ item.produtos }}
                          </td>

                          <td class="px-4 py-3">
                              R$ {{ Number(item.valor).toFixed(2) }}
                          </td>

                          <td class="px-4 py-3">
                              <span
                                  class="px-3 py-1 text-xs font-semibold rounded-full"
                                  :class="{
                                      'bg-green-100 text-green-700': item.status === 'Finalizado',
                                      'bg-yellow-100 text-yellow-700': item.status === 'A Caminho',
                                      'bg-blue-100 text-blue-700': item.status === 'Em Andamento',
                                      'bg-purple-100 text-purple-700': item.status === 'Pronto',
                                      'bg-orange-100 text-orange-700': item.status === 'Processando',
                                      'bg-red-100 text-red-700': item.status === 'Cancelado',
                                  }">
                                  {{ item.status }}
                              </span>
                          </td>

                          <td class="px-4 py-3">
                              {{ item.data }}
                          </td>

                      </tr>
                  </tbody>
              </table>

              <!-- PAGINAÇÃO -->
              <div class="flex justify-center items-center gap-4 mt-4">

                  <button
                      @click="carregarPagina(historico.prev_page_url)"
                      :disabled="!historico.prev_page_url"
                      class="px-4 py-2 bg-gray-200 rounded disabled:opacity-50"
                  >
                      Anterior
                  </button>

                  <span class="text-gray-600 text-sm">
                      Página {{ historico.current_page }} de {{ historico.last_page }}
                  </span>

                  <button
                      @click="carregarPagina(historico.next_page_url)"
                      :disabled="!historico.next_page_url"
                      class="px-4 py-2 bg-gray-200 rounded disabled:opacity-50"
                  >
                      Próxima
                  </button>

              </div>

          </div>
  </template>
