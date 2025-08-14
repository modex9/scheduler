<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-900">
              Appointment Scheduler
            </h1>
          </div>
          <div class="flex items-center space-x-4">
            <button
              @click="currentView = 'booking'"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium',
                currentView === 'booking'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              Book Appointment
            </button>
            <button
              @click="currentView = 'admin'"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium',
                currentView === 'admin'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              Admin Panel
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>

        <!-- Booking View -->
        <BookingView v-else-if="currentView === 'booking'" />

        <!-- Admin View -->
        <AdminView v-else-if="currentView === 'admin'" />
      </div>
    </main>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import BookingView from './components/BookingView.vue';
import AdminView from './components/AdminView.vue';

export default {
  name: 'App',
  components: {
    BookingView,
    AdminView,
  },
  setup() {
    const currentView = ref(localStorage.getItem('currentView') || 'booking');
    const loading = ref(false);

    onMounted(() => {
      // Initialize the app
      console.log('App mounted');
    });

    // Watch for view changes and save to localStorage
    watch(currentView, (newView) => {
      localStorage.setItem('currentView', newView);
    });

    return {
      currentView,
      loading,
    };
  },
};
</script>
