<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import Button from './Button.vue';
import { Calendar } from 'lucide-vue-next';

interface Props {
  variant?: 'primary' | 'secondary' | 'filter' | 'outline' | 'ghost';
  modelValue?: { start: string | null; end: string | null };
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'filter',
});

const emit = defineEmits<{
  'update:modelValue': [value: { start: string | null; end: string | null }];
  'apply': [value: { start: string | null; end: string | null }];
}>();

const isOpen = ref(false);
const buttonRef = ref<HTMLElement | null>(null);
const dropdownRef = ref<HTMLElement | null>(null);

const startDate = ref<string | null>(props.modelValue?.start ?? null);
const endDate = ref<string | null>(props.modelValue?.end ?? null);
// hovered date while selecting end -> used to render temporary trail/range
const hoveredDate = ref<string | null>(null);

const currentMonth = ref(new Date());
const selectionMode = ref<'start' | 'end'>('start');

// Generate calendar days for current month
const calendarDays = computed(() => {
  const year = currentMonth.value.getFullYear();
  const month = currentMonth.value.getMonth();
  
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  
  const startPadding = firstDay.getDay(); // 0 = Sunday
  const days: Array<{ date: Date; isCurrentMonth: boolean; dateString: string }> = [];
  
  // Previous month padding
  for (let i = startPadding - 1; i >= 0; i--) {
    const date = new Date(year, month, -i);
    days.push({
      date,
      isCurrentMonth: false,
      dateString: formatISODateLocal(date),
    });
  }
  
  // Current month days
  for (let i = 1; i <= lastDay.getDate(); i++) {
    const date = new Date(year, month, i);
    days.push({
      date,
      isCurrentMonth: true,
      dateString: formatISODateLocal(date),
    });
  }
  
  // Next month padding to complete the grid
  const remaining = 7 - (days.length % 7);
  if (remaining < 7) {
    for (let i = 1; i <= remaining; i++) {
      const date = new Date(year, month + 1, i);
      days.push({
        date,
        isCurrentMonth: false,
        dateString: formatISODateLocal(date),
      });
    }
  }
  
  return days;
});

const monthName = computed(() => {
  return currentMonth.value.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
});

// Formata uma Date para `YYYY-MM-DD` usando valores locais (evita shift por timezone)
function formatISODateLocal(d: Date) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

// Parseia uma string ISO (YYYY-MM-DD) como data local, sem shift de timezone
function parseISODateLocal(dateString: string): Date {
  const [year, month, day] = dateString.split('-').map(Number);
  return new Date(year, month - 1, day);
}

function toggleCalendar() {
  isOpen.value = !isOpen.value;
}

function closeCalendar() {
  isOpen.value = false;
}

function selectDate(dateString: string) {
  // If both start and end are already selected, reset selection and start a new range
  if (startDate.value && endDate.value) {
    startDate.value = dateString;
    endDate.value = null;
    selectionMode.value = 'end';
    hoveredDate.value = null;
    return;
  }

  if (selectionMode.value === 'start') {
    startDate.value = dateString;
    selectionMode.value = 'end';
    // Clear end if new start is after current end
    if (endDate.value && dateString > endDate.value) {
      endDate.value = null;
    }
  } else {
    // If selecting end and it's before start, swap them
    if (startDate.value && dateString < startDate.value) {
      endDate.value = startDate.value;
      startDate.value = dateString;
    } else {
      endDate.value = dateString;
    }
    selectionMode.value = 'start';
  }
  // clear temporary hover when a date is chosen
  hoveredDate.value = null;
}

function isSelected(dateString: string): boolean {
  return dateString === startDate.value || dateString === endDate.value;
}

function isInRange(dateString: string): boolean {
  // If both start and end selected -> normal range
  if (startDate.value && endDate.value) {
    return dateString > startDate.value && dateString < endDate.value;
  }

  // If start selected and user is hovering another date -> show temporary range between start and hovered
  if (startDate.value && !endDate.value && hoveredDate.value) {
    const a = startDate.value < hoveredDate.value ? startDate.value : hoveredDate.value;
    const b = startDate.value < hoveredDate.value ? hoveredDate.value : startDate.value;
    return dateString > a && dateString < b;
  }

  return false;
}

function isStartDate(dateString: string): boolean {
  return dateString === startDate.value;
}

function isEndDate(dateString: string): boolean {
  return dateString === endDate.value;
}

function previousMonth() {
  currentMonth.value = new Date(
    currentMonth.value.getFullYear(),
    currentMonth.value.getMonth() - 1,
    1
  );
}

function nextMonth() {
  currentMonth.value = new Date(
    currentMonth.value.getFullYear(),
    currentMonth.value.getMonth() + 1,
    1
  );
}

function handleDayMouseEnter(dateString: string) {
  if (startDate.value && !endDate.value) hoveredDate.value = dateString;
}

function handleDayMouseLeave() {
  hoveredDate.value = null;
}

function applyDates() {
  const value = { start: startDate.value, end: endDate.value };
  emit('update:modelValue', value);
  emit('apply', value);
  closeCalendar();
}

function clearDates() {
  startDate.value = null;
  endDate.value = null;
  selectionMode.value = 'start';
}

function handleClickOutside(event: MouseEvent) {
  if (!isOpen.value) return;
  const target = event.target as Node;
  // dropdown DOM
  const dd = dropdownRef.value as HTMLElement | null;
  // buttonRef may be a component proxy or an HTMLElement
  let btnEl: HTMLElement | null = null;
  try {
    if (!buttonRef.value) btnEl = null;
    else if ((buttonRef.value as any).$el) btnEl = (buttonRef.value as any).$el as HTMLElement;
    else btnEl = buttonRef.value as unknown as HTMLElement;
  } catch (e) {
    btnEl = null;
  }

  const clickedInsideDropdown = dd ? dd.contains(target) : false;
  const clickedInsideButton = btnEl ? btnEl.contains(target) : false;

  if (!clickedInsideDropdown && !clickedInsideButton) {
    closeCalendar();
  }
}

// Add/remove click listener when dropdown opens/closes
watch(isOpen, (open: boolean) => {
  if (open) {
    // Use setTimeout to avoid immediate trigger
    setTimeout(() => {
      // Use capture phase so clicks that stopPropagation still reach this handler
      document.addEventListener('click', handleClickOutside, true);
    }, 0);
  } else {
    document.removeEventListener('click', handleClickOutside, true);
  }
});

const formattedRange = computed(() => {
  if (startDate.value && endDate.value) {
    const start = parseISODateLocal(startDate.value).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    const end = parseISODateLocal(endDate.value).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    return `${start} - ${end}`;
  }
  return 'Custom';
});

const formattedStartDate = computed(() => {
  return startDate.value ? parseISODateLocal(startDate.value).toLocaleDateString('pt-BR') : null;
});

const formattedEndDate = computed(() => {
  return endDate.value ? parseISODateLocal(endDate.value).toLocaleDateString('pt-BR') : null;
});
</script>

<template>
  <div class="relative inline-block">
    <Button
      ref="buttonRef"
      :variant="variant"
      @click.prevent="toggleCalendar"
      class="flex items-center gap-2"
    >
      <Calendar class="w-4 h-4" />
      {{ formattedRange }}
    </Button>

    <Transition name="calendar-fade">
      <div
        v-if="isOpen"
        ref="dropdownRef"
        class="absolute top-full mt-2 right-0 z-50 w-80 bg-card border border-primary/10 rounded-lg shadow-lg p-4"
      >
        <!-- Month Navigation -->
        <div class="flex items-center justify-between mb-4">
          <button
            @click.prevent="previousMonth"
            class="p-1 hover:bg-primary/10 rounded transition-colors"
            type="button"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <span class="text-sm font-semibold capitalize">{{ monthName }}</span>
          <button
            @click.prevent="nextMonth"
            class="p-1 hover:bg-primary/10 rounded transition-colors"
            type="button"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <!-- Weekday headers -->
        <div class="grid grid-cols-7 gap-1 mb-2">
          <div
            v-for="day in ['D', 'S', 'T', 'Q', 'Q', 'S', 'S']"
            :key="day"
            class="text-xs font-medium text-center text-foreground/60 py-1"
          >
            {{ day }}
          </div>
        </div>

        <!-- Calendar grid -->
        <div class="grid grid-cols-7 gap-1 mb-4">
          <button
            v-for="day in calendarDays"
            :key="day.dateString"
            @click.prevent="selectDate(day.dateString)"
            @mouseenter="handleDayMouseEnter(day.dateString)"
            @mouseleave="handleDayMouseLeave"
            type="button"
            :class="[
              'aspect-square text-sm rounded-md transition-all',
              day.isCurrentMonth ? 'text-foreground' : 'text-foreground/30',
              isStartDate(day.dateString) || isEndDate(day.dateString)
                ? 'bg-primary text-primary-foreground font-semibold'
                : isInRange(day.dateString)
                ? 'bg-primary/20'
                : 'hover:bg-primary/10',
              !day.isCurrentMonth && 'opacity-50',
            ]"
          >
            {{ day.date.getDate() }}
          </button>
        </div>

        <!-- Selection info -->
        <div class="text-xs text-foreground/60 mb-3 space-y-1">
          <div v-if="startDate" class="flex items-center justify-between">
            <span>Início:</span>
            <span class="font-medium">{{ formattedStartDate }}</span>
          </div>
          <div v-if="endDate" class="flex items-center justify-between">
            <span>Fim:</span>
            <span class="font-medium">{{ formattedEndDate }}</span>
          </div>
          <div v-if="!startDate && !endDate" class="text-center py-2">
            Selecione a data inicial
          </div>
          <div v-else-if="startDate && !endDate" class="text-center py-2">
            Selecione a data final
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
          <Button
            variant="outline"
            size="sm"
            @click.prevent="clearDates"
            class="flex-1"
            type="button"
          >
            Limpar
          </Button>
          <Button
            variant="primary"
            size="sm"
            @click.prevent="applyDates"
            :disabled="!startDate || !endDate"
            class="flex-1"
            type="button"
          >
            Aplicar
          </Button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.calendar-fade-enter-active,
.calendar-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.calendar-fade-enter-from,
.calendar-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
