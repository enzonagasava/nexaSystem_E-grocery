<script setup lang="ts">
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
</script>
<template>
  <transition name="fade">
    <div v-if="toast.visible" class="toast" :class="toast.type">
      {{ toast.message }}
    </div>
  </transition>
</template>
<style scoped>
.toast {
  position: fixed;
  bottom: 80px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #333;
  color: white;
  padding: 12px 24px;
  border-radius: 6px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.3);
  font-weight: 600;
  z-index: 9999;
  min-width: 230px;
  text-align: center;
}
.toast.success {
  background-color: #4caf50;
}
.toast.error {
  background-color: #f44336;
}
.toast.warning {
  background-color: #ff9800;
}
.toast.info {
  background-color: #2196f3;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>