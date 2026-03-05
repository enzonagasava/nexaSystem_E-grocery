<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import LeadsForm from '@/components/admin/corretor/leads/form/LeadsForm.vue';
const props = defineProps<{ lead?: any, corretor?: any, imovel?: any, status: any }>();
</script>

<template>
  <Head>
    <title>Adicionar Lead - Corretor</title>
  </Head>
  <AuthLayout>
    <div class="p-6">
      <h1 class="text-xl font-semibold">{{ props.lead ? 'Editar Lead' : 'Adicionar Lead' }}</h1>
      <LeadsForm :lead="props.lead" :corretor="props.corretor" :imovel="props.imovel" :status="props.status"/>
    </div>
  </AuthLayout>
</template>
