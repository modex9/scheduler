<template>
  <div class="service-selector">
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="services.length === 0" class="text-center py-8">
      <p class="text-gray-500">No services available at the moment.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="service in services"
        :key="service.id"
        @click="selectService(service)"
        :class="[
          'service-card p-4 border rounded-lg cursor-pointer transition-all duration-200',
          selectedService && selectedService.id === service.id
            ? 'border-blue-500 bg-blue-50 shadow-md'
            : 'border-gray-200 hover:border-blue-300 hover:shadow-sm'
        ]"
      >
        <!-- Service Header -->
        <div class="flex justify-between items-start mb-3">
          <h4 class="text-lg font-semibold text-gray-900">{{ service.name }}</h4>
          <div class="text-right">
            <div class="text-sm text-gray-500">{{ formatDuration(service.duration_minutes) }}</div>
          </div>
        </div>

        <!-- Selection Indicator -->
        <div v-if="selectedService && selectedService.id === service.id" class="flex items-center text-blue-600">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <span class="text-sm font-medium">Selected</span>
        </div>

        <!-- Service Status -->
        <div v-if="!service.is_active" class="mt-2">
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
            Unavailable
          </span>
        </div>
      </div>
    </div>

    <!-- Selected Service Summary -->
    <div v-if="selectedService" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
      <h4 class="text-lg font-semibold text-blue-900 mb-2">Selected Service</h4>
      <div class="flex justify-between items-center">
        <div>
          <p class="font-medium text-blue-900">{{ selectedService.name }}</p>
          <p class="text-sm text-blue-700">{{ formatDuration(selectedService.duration_minutes) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'ServiceSelector',
  props: {
    services: {
      type: Array,
      default: () => [],
    },
    selectedService: {
      type: Object,
      default: null,
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update:selectedService', 'service-selected'],
  setup(props, { emit }) {
    const formatDuration = (minutes) => {
      const hours = Math.floor(minutes / 60);
      const remainingMinutes = minutes % 60;

      if (hours > 0) {
        return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}m` : `${hours}h`;
      }
      return `${remainingMinutes}m`;
    };

    const selectService = (service) => {
      if (!service.is_active) return;

      emit('update:selectedService', service);
      emit('service-selected', service);
    };

    return {
      formatDuration,
      selectService,
    };
  },
};
</script>

<style scoped>
.service-card {
  min-height: 120px;
}

.service-card:hover {
  transform: translateY(-1px);
}
</style>
