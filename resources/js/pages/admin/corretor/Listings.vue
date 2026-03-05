<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-vue-next';
import ViewToggle from '@/components/ui/toggle/ViewToggle.vue';
import ListingCardGrid from '@/components/listing/ListingCardGrid.vue';
import ListingListRow from '@/components/listing/ListingListRow.vue';
import { ref, onMounted, watch } from 'vue';

defineProps<{
  listings?: any;
}>();

const page = usePage();
const viewMode = ref<'grid' | 'list'>('grid');

// Recupera viewMode do localStorage ao montar o componente
onMounted(() => {
  const saved = localStorage.getItem('listings-view-mode')
  if (saved === 'grid' || saved === 'list') {
    viewMode.value = saved
  }
})

// Salva viewMode no localStorage quando muda
watch(viewMode, (newVal: 'grid' | 'list') => {
  localStorage.setItem('listings-view-mode', newVal)
})

function handleOpenDetails(listing: any) {
  window.location.href = `/admin/corretor/listings/${listing.id}`;
}
</script>

<template>
  <Head>
    <title>Meus Anúncios</title>
    <meta name="description" content="Lista de anúncios do corretor" />
  </Head>

  <AuthLayout :modulo="String(page.props.modulo)">
    <div class="p-6">
      <!-- Header com título e toggle de visualização -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-xl font-semibold">Meus Anúncios</h1>
          <p class="text-sm text-muted-foreground">Anúncios criados a partir dos imóveis</p>
        </div>
        <div class="flex items-center gap-3">
          <Link :href="route('admin.corretor.listings.create')">
            <Button variant="primary">
              <Plus class="w-4 h-4 mr-2" />
              Criar Anúncio
            </Button>
          </Link>
          <ViewToggle v-model="viewMode" />
        </div>
      </div>

      <!-- Conteúdo da listagem -->
      <div v-if="listings && listings.data && listings.data.length" class="space-y-4">
        <!-- Visualização em Grid -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 auto-rows-fr">
          <ListingCardGrid
            v-for="listing in listings.data"
            :key="listing.id"
            :listing="listing"
            @open-details="handleOpenDetails"
          />
        </div>

        <!-- Visualização em Lista -->
        <div v-else class="space-y-2">
          <ListingListRow
            v-for="listing in listings.data"
            :key="listing.id"
            :listing="listing"
            @open-details="handleOpenDetails"
          />
        </div>
      </div>

      <div v-else class="mt-4 rounded-lg border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
        <p class="text-gray-500 dark:text-gray-400 mb-4">Nenhum anúncio encontrado.</p>
        <Link :href="route('admin.corretor.listings.create')">
          <Button variant="primary">
            <Plus class="w-4 h-4 mr-2" />
            Criar Primeiro Anúncio
          </Button>
        </Link>
      </div>
    </div>
  </AuthLayout>
</template>

