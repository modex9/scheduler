<template>
  <div class="max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900">Book Your Appointment</h2>
        <p class="mt-1 text-sm text-gray-600">
          Select a date, choose your service, and pick an available time slot.
        </p>
      </div>

      <!-- Booking Steps -->
      <div class="p-6">
        <!-- Step 1: Date Selection -->
        <div class="mb-8">
          <h3 class="text-lg font-medium text-gray-900 mb-4">1. Select a Date</h3>
          <Calendar
            v-model:selectedDate="selectedDate"
            @date-selected="onDateSelected"
          />
        </div>

        <!-- Step 2: Service Selection -->
        <div v-if="selectedDate" class="mb-8">
          <h3 class="text-lg font-medium text-gray-900 mb-4">2. Choose a Service</h3>
          <ServiceSelector
            v-model:selectedService="selectedService"
            :services="services"
            @service-selected="onServiceSelected"
          />
        </div>

        <!-- Step 3: Time Slot Selection -->
        <div v-if="selectedService && selectedDate" class="mb-8">
          <h3 class="text-lg font-medium text-gray-900 mb-4">3. Pick a Time</h3>
          <TimeSlotSelector
            v-model:selectedTime="selectedTime"
            :availableSlots="availableSlots"
            :loading="loadingSlots"
            @time-selected="onTimeSelected"
          />
        </div>

        <!-- Step 4: Booking Form -->
        <div v-if="selectedTime && selectedService && selectedDate" class="mb-8">
          <h3 class="text-lg font-medium text-gray-900 mb-4">4. Complete Your Booking</h3>
          <BookingForm
            :selectedDate="selectedDate"
            :selectedTime="selectedTime"
            :selectedService="selectedService"
            @booking-submitted="onBookingSubmitted"
          />
        </div>

        <!-- Success Message -->
        <div v-if="bookingSuccess" class="mb-8">
          <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">
                  Booking Successful!
                </h3>
                <div class="mt-2 text-sm text-green-700">
                  <p>Your appointment has been confirmed. You will receive a confirmation email shortly.</p>
                </div>
                <div class="mt-4">
                  <button
                    @click="resetBooking"
                    class="bg-green-100 text-green-800 px-4 py-2 rounded-md text-sm font-medium hover:bg-green-200"
                  >
                    Book Another Appointment
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mb-8">
          <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  Error
                </h3>
                <div class="mt-2 text-sm text-red-700">
                  <p>{{ error }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import Calendar from './Calendar.vue';
import ServiceSelector from './ServiceSelector.vue';
import TimeSlotSelector from './TimeSlotSelector.vue';
import BookingForm from './BookingForm.vue';
import api from '../services/api.js';

export default {
  name: 'BookingView',
  components: {
    Calendar,
    ServiceSelector,
    TimeSlotSelector,
    BookingForm,
  },
  setup() {
    const selectedDate = ref(null);
    const selectedService = ref(null);
    const selectedTime = ref(null);
    const services = ref([]);
    const availableSlots = ref([]);
    const loadingSlots = ref(false);
    const bookingSuccess = ref(false);
    const error = ref('');

    // Load services on mount
    onMounted(async () => {
      try {
        const response = await api.getServices();
        services.value = response.data.services;
      } catch (err) {
        error.value = 'Failed to load services. Please try again.';
        console.error('Error loading services:', err);
      }
    });

    // Watch for date changes to load availability
    watch([selectedDate, selectedService], async ([newDate, newService]) => {
      if (newDate && newService) {
        await loadAvailableSlots(newDate, newService.id);
      } else {
        availableSlots.value = [];
      }
    });

    const loadAvailableSlots = async (date, serviceId) => {
      loadingSlots.value = true;
      error.value = '';

      try {
        const response = await api.getAvailableSlots(date, serviceId);
        availableSlots.value = response.data.available_slots;
      } catch (err) {
        error.value = 'Failed to load available time slots. Please try again.';
        console.error('Error loading slots:', err);
      } finally {
        loadingSlots.value = false;
      }
    };

    const onDateSelected = (date) => {
      selectedDate.value = date;
      selectedTime.value = null; // Reset time when date changes
    };

    const onServiceSelected = (service) => {
      selectedService.value = service;
      selectedTime.value = null; // Reset time when service changes
    };

    const onTimeSelected = (time) => {
      selectedTime.value = time;
    };

    const onBookingSubmitted = async (bookingData) => {
      // Clear any previous success/error messages
      bookingSuccess.value = false;
      error.value = '';

      try {
        await api.bookAppointment(bookingData);
        bookingSuccess.value = true;

        // Reload available slots to show updated availability
        if (selectedDate.value && selectedService.value) {
          selectedTime.value = null; // Clear selected time to show updated availability
          await loadAvailableSlots(selectedDate.value, selectedService.value.id);
        }
      } catch (err) {
        if (err.response?.data?.errors) {
          // Handle validation errors
          const validationErrors = err.response.data.errors;
          const errorMessages = [];

          // Extract error messages from validation errors
          Object.keys(validationErrors).forEach(field => {
            if (Array.isArray(validationErrors[field])) {
              errorMessages.push(...validationErrors[field]);
            } else {
              errorMessages.push(validationErrors[field]);
            }
          });

          error.value = errorMessages.join('. ');
        } else {
          error.value = err.response?.data?.message || 'Failed to book appointment. Please try again.';
        }
        console.error('Error booking appointment:', err);
      }
    };

    const resetBooking = () => {
      selectedDate.value = null;
      selectedService.value = null;
      selectedTime.value = null;
      availableSlots.value = [];
      bookingSuccess.value = false;
      error.value = '';
    };

    return {
      selectedDate,
      selectedService,
      selectedTime,
      services,
      availableSlots,
      loadingSlots,
      bookingSuccess,
      error,
      onDateSelected,
      onServiceSelected,
      onTimeSelected,
      onBookingSubmitted,
      resetBooking,
    };
  },
};
</script>
