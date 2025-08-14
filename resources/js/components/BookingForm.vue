<template>
  <div class="booking-form">
    <!-- Appointment Summary -->
    <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Appointment Summary</h4>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <p class="text-sm font-medium text-gray-500">Date</p>
          <p class="text-lg font-semibold text-gray-900">{{ formatDate(selectedDate) }}</p>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">Time</p>
          <p class="text-lg font-semibold text-gray-900">{{ formatTime(selectedTime.time) }}</p>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">Service</p>
          <p class="text-lg font-semibold text-gray-900">{{ selectedService.name }}</p>
        </div>
      </div>
      <div class="mt-4 pt-4 border-t border-gray-200">
        <div class="flex justify-between items-center">
          <div>
            <p class="text-sm text-gray-500">Duration</p>
            <p class="text-sm font-medium text-gray-900">{{ formatDuration(selectedService.duration_minutes) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Booking Form -->
    <form @submit.prevent="submitBooking" class="space-y-6">
      <!-- Personal Information -->
      <div>
        <h4 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="client_email" class="block text-sm font-medium text-gray-700 mb-1">
              Email Address *
            </label>
            <input
              id="client_email"
              v-model="form.client_email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter your email address"
            />
            <p v-if="errors.client_email" class="mt-1 text-sm text-red-600">{{ errors.client_email }}</p>
          </div>
        </div>
      </div>



      <!-- Submit Button -->
      <div class="flex justify-end">
        <button
          type="submit"
          :disabled="submitting"
          :class="[
            'px-6 py-3 text-base font-medium rounded-md text-white transition-colors',
            submitting
              ? 'bg-gray-400 cursor-not-allowed'
              : 'bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500'
          ]"
        >
          <span v-if="submitting" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Booking Appointment...
          </span>
          <span v-else>Confirm Booking</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, reactive } from 'vue';

export default {
  name: 'BookingForm',
  props: {
    selectedDate: {
      type: String,
      required: true,
    },
    selectedTime: {
      type: Object,
      required: true,
    },
    selectedService: {
      type: Object,
      required: true,
    },
  },
  emits: ['booking-submitted'],
  setup(props, { emit }) {
    const submitting = ref(false);
    const errors = reactive({});

    const form = reactive({
      client_email: '',
    });

    const formatDate = (dateString) => {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
    };

    const formatTime = (time) => {
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

    const validateForm = () => {
      // Clear previous errors
      Object.keys(errors).forEach(key => delete errors[key]);

      if (!form.client_email.trim()) {
        errors.client_email = 'Email address is required';
      } else if (!isValidEmail(form.client_email)) {
        errors.client_email = 'Please enter a valid email address';
      }



      return Object.keys(errors).length === 0;
    };

    const isValidEmail = (email) => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
    };

    const submitBooking = async () => {
      if (!validateForm()) return;

      submitting.value = true;

      try {
        const bookingData = {
          appointment_date: props.selectedDate,
          appointment_time: props.selectedTime.time,
          service_id: props.selectedService.id,
          client_email: form.client_email.trim(),
        };

        emit('booking-submitted', bookingData);
      } catch (error) {
        console.error('Error submitting booking:', error);
      } finally {
        submitting.value = false;
      }
    };

    return {
      form,
      errors,
      submitting,
      formatDate,
      formatTime,
      formatDuration,
      submitBooking,
    };
  },
};
</script>
