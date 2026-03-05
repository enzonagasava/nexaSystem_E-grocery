<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
import ContatosForm from '@/components/admin/corretor/contatos/form/ContatosForm.vue';
const props = defineProps<{ leads?: any, corretor?: any, imovel?: any, status:any }>();

</script>

<template>
  <Head>
    <title>Adicionar Contato - Corretor</title>
  </Head>
  <AuthLayout>
    <div class="p-6">
      <h1 class="text-xl font-semibold">{{ props.leads ? 'Editar Contato' : 'Adicionar Contato' }}</h1>
      <ContatosForm :leads="props.leads" :corretores="corretor" :status="props.status" />
    </div>
  </AuthLayout>
</template>
