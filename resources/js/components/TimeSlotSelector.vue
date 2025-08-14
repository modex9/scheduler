<template>
  <div class="time-slot-selector">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <span class="ml-3 text-gray-600">Loading available time slots...</span>
    </div>

    <!-- No Slots Available -->
    <div v-else-if="availableSlots.length === 0" class="text-center py-8">
      <div class="text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-lg font-medium text-gray-900 mb-2">No Available Time Slots</p>
        <p class="text-sm text-gray-600">
          There are no available time slots for the selected date and service.
          <br>Please try a different date or service.
        </p>
      </div>
    </div>

    <!-- Time Slots Grid -->
    <div v-else>
      <div class="mb-4">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Available Time Slots</h4>
        <p class="text-xs text-gray-500">
          Click on a time slot to select it. All times are in your local timezone.
        </p>
      </div>

      <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
        <button
          v-for="slot in availableSlots"
          :key="`${slot.time}-${slot.service_id}`"
          @click="selectTimeSlot(slot)"
          :disabled="!slot.is_available"
                      :class="[
              'time-slot-btn px-3 py-2 text-sm font-medium rounded-md transition-all duration-200',
              slot.is_booked
                ? 'bg-red-50 border border-red-200 text-red-400 cursor-not-allowed'
                : slot.insufficient_gap
                  ? 'bg-orange-50 border border-orange-200 text-orange-400 cursor-not-allowed'
                  : selectedTime && selectedTime.time === slot.time && selectedTime.service_id === slot.service_id
                    ? 'bg-blue-600 text-white shadow-md'
                    : 'bg-white border border-gray-300 text-gray-700 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700'
            ]"
        >
          <div class="text-center">
            <div class="font-semibold">{{ formatTime(slot.time) }}</div>
            <div v-if="slot.is_booked" class="text-xs text-red-500 mt-1">Booked</div>
            <div v-else-if="slot.insufficient_gap" class="text-xs text-orange-500 mt-1">Too Short</div>
          </div>
        </button>
      </div>

      <!-- Selected Time Summary -->
      <div v-if="selectedTime" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <h4 class="text-lg font-semibold text-blue-900 mb-2">Selected Time</h4>
        <div class="flex justify-between items-center">
          <div>
            <p class="font-medium text-blue-900">{{ formatTime(selectedTime.time) }}</p>
            <p class="text-sm text-blue-700">{{ selectedTime.service_name }}</p>
          </div>
          <div class="text-right">
            <p class="text-sm text-blue-700">Duration: {{ formatDuration(selectedTime.duration_minutes) }}</p>
            <p class="text-lg font-bold text-blue-900">${{ selectedTime.price }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'TimeSlotSelector',
  props: {
    availableSlots: {
      type: Array,
      default: () => [],
    },
    selectedTime: {
      type: Object,
      default: null,
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update:selectedTime', 'time-selected'],
  setup(props, { emit }) {
    const formatTime = (time) => {
      // Convert 24-hour format to 12-hour format
      const [hours, minutes] = time.split(':');
      const hour = parseInt(hours);
      const ampm = hour >= 12 ? 'PM' : 'AM';
      const displayHour = hour % 12 || 12;
      return `${displayHour}:${minutes} ${ampm}`;
    };

    const formatDuration = (minutes) => {
      const hours = Math.floor(minutes / 60);
      const remainingMinutes = minutes % 60;

      if (hours > 0) {
        return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}m` : `${hours}h`;
      }
      return `${remainingMinutes}m`;
    };

    const selectTimeSlot = (slot) => {
      if (!slot.is_available) return;
      emit('update:selectedTime', slot);
      emit('time-selected', slot);
    };

    return {
      formatTime,
      formatDuration,
      selectTimeSlot,
    };
  },
};
</script>

<style scoped>
.time-slot-btn {
  min-height: 60px;
}

.time-slot-btn:hover {
  transform: translateY(-1px);
}
</style>
