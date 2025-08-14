<template>
  <div class="calendar">
    <!-- Calendar Header -->
    <div class="flex items-center justify-between mb-4">
      <button
        @click="previousMonth"
        class="p-2 rounded-md hover:bg-gray-100"
        :disabled="isCurrentMonth"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>

      <h3 class="text-lg font-semibold text-gray-900">
        {{ currentMonthYear }}
      </h3>

      <button
        @click="nextMonth"
        class="p-2 rounded-md hover:bg-gray-100"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>

    <!-- Day Headers -->
    <div class="grid grid-cols-7 gap-1 mb-2">
      <div
        v-for="day in dayNames"
        :key="day"
        class="text-center text-sm font-medium text-gray-500 py-2"
      >
        {{ day }}
      </div>
    </div>

    <!-- Calendar Grid -->
    <div class="grid grid-cols-7 gap-1">
      <div
        v-for="day in calendarDays"
        :key="day.key"
        @click="selectDate(day)"
        :class="[
          'relative p-3 text-center cursor-pointer rounded-md transition-colors',
          day.isCurrentMonth
            ? day.isAvailable
              ? 'hover:bg-blue-50 hover:text-blue-700'
              : 'text-gray-400 cursor-not-allowed'
            : 'text-gray-300 cursor-not-allowed',
          day.isSelected
            ? 'bg-blue-600 text-white hover:bg-blue-700'
            : day.isToday
            ? 'bg-blue-100 text-blue-900'
            : ''
        ]"
        :disabled="!day.isAvailable || !day.isCurrentMonth"
      >
        <span class="text-sm font-medium">{{ day.dayNumber }}</span>

        <!-- Available indicator -->
        <div
          v-if="day.isAvailable && day.isCurrentMonth && !day.isSelected"
          class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-green-500 rounded-full"
        ></div>
      </div>
    </div>

    <!-- Legend -->
    <div class="mt-4 flex items-center justify-center space-x-4 text-xs text-gray-600">
      <div class="flex items-center">
        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
        <span>Available</span>
      </div>
      <div class="flex items-center">
        <div class="w-3 h-3 bg-blue-600 rounded-full mr-2"></div>
        <span>Selected</span>
      </div>
      <div class="flex items-center">
        <div class="w-3 h-3 bg-blue-100 rounded-full mr-2"></div>
        <span>Today</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';

export default {
  name: 'Calendar',
  props: {
    selectedDate: {
      type: String,
      default: null,
    },
  },
  emits: ['update:selectedDate', 'date-selected'],
  setup(props, { emit }) {
    const currentDate = ref(new Date());
    const selectedDate = ref(props.selectedDate ? new Date(props.selectedDate) : null);

    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    // Computed properties
    const currentMonthYear = computed(() => {
      return currentDate.value.toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric',
      });
    });

    const isCurrentMonth = computed(() => {
      const now = new Date();
      return (
        currentDate.value.getMonth() === now.getMonth() &&
        currentDate.value.getFullYear() === now.getFullYear()
      );
    });

    const calendarDays = computed(() => {
      const year = currentDate.value.getFullYear();
      const month = currentDate.value.getMonth();

      // Get first day of month and last day of month
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);

      // Get start of calendar (including previous month's days)
      const startDate = new Date(firstDay);
      startDate.setDate(startDate.getDate() - firstDay.getDay());

      const days = [];
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);

        const isCurrentMonth = date.getMonth() === month;
        const isToday = date.getTime() === today.getTime();
        const isSelected = selectedDate.value && date.toDateString() === selectedDate.value.toDateString();
        const isAvailable = isDateAvailable(date);

        days.push({
          key: date.toISOString(),
          date: date,
          dayNumber: date.getDate(),
          isCurrentMonth,
          isToday,
          isSelected,
          isAvailable,
        });
      }

      return days;
    });

    // Methods
    const isDateAvailable = (date) => {
      // Only allow future dates
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      if (date < today) return false;

      // Allow all days - availability will be determined by working hours
      return true;
    };

    const selectDate = (day) => {
      if (!day.isAvailable || !day.isCurrentMonth) return;

      selectedDate.value = day.date;
      // Format date as YYYY-MM-DD without timezone issues
      const year = day.date.getFullYear();
      const month = String(day.date.getMonth() + 1).padStart(2, '0');
      const dayNum = String(day.date.getDate()).padStart(2, '0');
      const dateString = `${year}-${month}-${dayNum}`;

      emit('update:selectedDate', dateString);
      emit('date-selected', dateString);
    };

    const previousMonth = () => {
      currentDate.value = new Date(
        currentDate.value.getFullYear(),
        currentDate.value.getMonth() - 1,
        1
      );
    };

    const nextMonth = () => {
      currentDate.value = new Date(
        currentDate.value.getFullYear(),
        currentDate.value.getMonth() + 1,
        1
      );
    };

    // Watch for prop changes
    watch(() => props.selectedDate, (newDate) => {
      if (newDate) {
        selectedDate.value = new Date(newDate);
      } else {
        selectedDate.value = null;
      }
    });

    return {
      currentDate,
      selectedDate,
      dayNames,
      currentMonthYear,
      isCurrentMonth,
      calendarDays,
      selectDate,
      previousMonth,
      nextMonth,
    };
  },
};
</script>
