<template>
  <article class="nexa-card" :aria-label="ariaLabel">
    <slot name="image"></slot>
    <div class="nexa-card-body">
      <slot></slot>
    </div>
    <div class="nexa-card-footer">
      <slot name="footer"></slot>
    </div>
  </article>
</template>

<script setup lang="ts">
defineProps<{ ariaLabel?: string }>();
</script>

<style scoped>
.nexa-card {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: var(--card);
  color: var(--card-foreground);
  border-radius: var(--radius);
  box-shadow: 0 1px 3px rgba(16,24,40,0.06);
  overflow: hidden;
  border: 1px solid var(--border);
}

.nexa-card:hover {
  background: var(--list-row-hover);
  border: 1px solid var(--primary);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}
.nexa-card-body { padding: 1rem; flex: 1 1 auto; }
.nexa-card-footer { padding: 0 1rem 1rem; }
.nexa-card [data-card-image] { height: 12rem; width: 100%; display: block; object-fit: cover; }
</style>
