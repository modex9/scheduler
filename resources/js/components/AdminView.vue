<template>
  <div class="admin-view">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Services Management -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900">Services Management</h2>
            <button
              @click="showServiceForm = true"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
              Add Service
            </button>
          </div>
        </div>

        <div class="p-6">
          <div v-if="loadingServices" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>

          <div v-else-if="services.length === 0" class="text-center py-8">
            <p class="text-gray-500">No services found. Add your first service to get started.</p>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="service in services"
              :key="service.id"
              class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
            >
              <div>
                <h3 class="font-semibold text-gray-900">{{ service.name }}</h3>
                <p class="text-sm text-gray-500">
                  Duration: {{ formatDuration(service.duration_minutes) }}
                </p>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="editService(service)"
                  class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                >
                  Edit
                </button>
                <button
                  @click="deleteService(service.id)"
                  class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Working Hours Management -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900">Working Hours</h2>
            <button
              @click="showWorkingHourForm = true"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
              Add Hours
            </button>
          </div>
        </div>

        <div class="p-6">
          <div v-if="loadingWorkingHours" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>

          <div v-else-if="workingHours.length === 0" class="text-center py-8">
            <p class="text-gray-500">No working hours set. Add your working hours to get started.</p>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="workingHour in workingHours"
              :key="workingHour.id"
              class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
            >
              <div>
                <h3 class="font-semibold text-gray-900">{{ getDayName(workingHour.day_of_week) }}</h3>
                <p class="text-sm text-gray-600">
                  {{ formatTime(workingHour.start_time) }} - {{ formatTime(workingHour.end_time) }}
                </p>
                <p class="text-sm text-gray-500">
                  Status: {{ workingHour.is_active ? 'Active' : 'Inactive' }}
                </p>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="editWorkingHour(workingHour)"
                  class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                >
                  Edit
                </button>
                <button
                  @click="deleteWorkingHour(workingHour.id)"
                  class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Service Form Modal -->
    <div v-if="showServiceForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">
          {{ editingService ? 'Edit Service' : 'Add New Service' }}
        </h3>

        <form @submit.prevent="saveService" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Service Name</label>
            <input
              v-model="serviceForm.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
            <input
              v-model="serviceForm.duration_minutes"
              type="number"
              min="15"
              max="480"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md"
            />
          </div>

          <div class="flex items-center">
            <input
              v-model="serviceForm.is_active"
              type="checkbox"
              class="h-4 w-4 text-blue-600"
            />
            <label class="ml-2 text-sm text-gray-700">Active</label>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="cancelServiceForm"
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
              {{ editingService ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Working Hour Form Modal -->
    <div v-if="showWorkingHourForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">
          {{ editingWorkingHour ? 'Edit Working Hours' : 'Add Working Hours' }}
        </h3>

        <form @submit.prevent="saveWorkingHour" class="space-y-4">
          <!-- Error Messages -->
          <div v-if="Object.keys(workingHourErrors).length > 0" class="bg-red-50 border border-red-200 rounded-md p-3">
            <div v-for="(errors, field) in workingHourErrors" :key="field" class="text-sm text-red-600">
              <div v-for="error in errors" :key="error" class="mb-1">{{ error }}</div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Day of Week</label>
            <select
              v-model="workingHourForm.day_of_week"
              required
              :class="[
                'w-full px-3 py-2 border rounded-md',
                workingHourErrors.day_of_week ? 'border-red-300' : 'border-gray-300'
              ]"
            >
              <option value="0" :disabled="!editingWorkingHour && getExistingDays().includes(0)">Sunday</option>
              <option value="1" :disabled="!editingWorkingHour && getExistingDays().includes(1)">Monday</option>
              <option value="2" :disabled="!editingWorkingHour && getExistingDays().includes(2)">Tuesday</option>
              <option value="3" :disabled="!editingWorkingHour && getExistingDays().includes(3)">Wednesday</option>
              <option value="4" :disabled="!editingWorkingHour && getExistingDays().includes(4)">Thursday</option>
              <option value="5" :disabled="!editingWorkingHour && getExistingDays().includes(5)">Friday</option>
              <option value="6" :disabled="!editingWorkingHour && getExistingDays().includes(6)">Saturday</option>
            </select>
            <p v-if="!editingWorkingHour && getExistingDays().includes(workingHourForm.day_of_week)" class="text-sm text-red-600 mt-1">
              Working hours for this day already exist. Please edit the existing hours instead.
            </p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
              <input
                v-model="workingHourForm.start_time"
                type="time"
                required
                :class="[
                  'w-full px-3 py-2 border rounded-md',
                  workingHourErrors.start_time ? 'border-red-300' : 'border-gray-300'
                ]"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
              <input
                v-model="workingHourForm.end_time"
                type="time"
                required
                :class="[
                  'w-full px-3 py-2 border rounded-md',
                  workingHourErrors.end_time ? 'border-red-300' : 'border-gray-300'
                ]"
              />
            </div>
          </div>

          <div class="flex items-center">
            <input
              v-model="workingHourForm.is_active"
              type="checkbox"
              class="h-4 w-4 text-blue-600"
            />
            <label class="ml-2 text-sm text-gray-700">Active</label>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="cancelWorkingHourForm"
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
              {{ editingWorkingHour ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import api from '../services/api.js';

export default {
  name: 'AdminView',
  setup() {
    const services = ref([]);
    const workingHours = ref([]);
    const loadingServices = ref(false);
    const loadingWorkingHours = ref(false);

    const showServiceForm = ref(false);
    const showWorkingHourForm = ref(false);
    const editingService = ref(null);
    const editingWorkingHour = ref(null);

    const serviceForm = reactive({
      name: '',
      duration_minutes: 60,
      is_active: true,
    });

    const workingHourForm = reactive({
      day_of_week: 1,
      start_time: '09:00',
      end_time: '17:00',
      is_active: true,
    });

    const workingHourErrors = ref({});

    // Load data on mount
    onMounted(() => {
      loadServices();
      loadWorkingHours();
    });

    const loadServices = async () => {
      loadingServices.value = true;
      try {
        const response = await api.getServices();
        services.value = response.data.services;
      } catch (error) {
        console.error('Error loading services:', error);
      } finally {
        loadingServices.value = false;
      }
    };

    const loadWorkingHours = async () => {
      loadingWorkingHours.value = true;
      try {
        const response = await api.getWorkingHours();
        workingHours.value = response.data.working_hours;
      } catch (error) {
        console.error('Error loading working hours:', error);
      } finally {
        loadingWorkingHours.value = false;
      }
    };

    const getExistingDays = () => {
      return workingHours.value.map(wh => wh.day_of_week);
    };

    const formatDuration = (minutes) => {
      const hours = Math.floor(minutes / 60);
      const remainingMinutes = minutes % 60;

      if (hours > 0) {
        return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}m` : `${hours}h`;
      }
      return `${remainingMinutes}m`;
    };

    const formatTime = (time) => {
      const [hours, minutes] = time.split(':');
      const hour = parseInt(hours);
      const ampm = hour >= 12 ? 'PM' : 'AM';
      const displayHour = hour % 12 || 12;
      return `${displayHour}:${minutes} ${ampm}`;
    };

    const getDayName = (dayOfWeek) => {
      const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
      return days[dayOfWeek];
    };

    const editService = (service) => {
      editingService.value = service;
      Object.assign(serviceForm, service);
      showServiceForm.value = true;
    };

    const editWorkingHour = (workingHour) => {
      editingWorkingHour.value = workingHour;
      Object.assign(workingHourForm, workingHour);
      showWorkingHourForm.value = true;
    };

    const saveService = async () => {
      try {
        if (editingService.value) {
          await api.updateService(editingService.value.id, serviceForm);
        } else {
          await api.createService(serviceForm);
        }
        await loadServices();
        cancelServiceForm();
      } catch (error) {
        console.error('Error saving service:', error);
      }
    };

    const saveWorkingHour = async () => {
      try {
        workingHourErrors.value = {}; // Clear previous errors

        if (editingWorkingHour.value) {
          await api.updateWorkingHour(editingWorkingHour.value.id, workingHourForm);
        } else {
          // Check if day already exists
          const existingDays = getExistingDays();
          if (existingDays.includes(workingHourForm.day_of_week)) {
            alert('Working hours for this day already exist. Please edit the existing hours instead.');
            return;
          }
          await api.createWorkingHour(workingHourForm);
        }
        await loadWorkingHours();
        cancelWorkingHourForm();
      } catch (error) {
        console.error('Error saving working hour:', error);
        if (error.response?.data?.errors) {
          workingHourErrors.value = error.response.data.errors;
        } else {
          workingHourErrors.value = { general: ['An error occurred while saving working hours.'] };
        }
      }
    };

    const deleteService = async (id) => {
      if (confirm('Are you sure you want to delete this service?')) {
        try {
          await api.deleteService(id);
          await loadServices();
        } catch (error) {
          console.error('Error deleting service:', error);
        }
      }
    };

    const deleteWorkingHour = async (id) => {
      if (confirm('Are you sure you want to delete these working hours?')) {
        try {
          await api.deleteWorkingHour(id);
          await loadWorkingHours();
        } catch (error) {
          console.error('Error deleting working hour:', error);
        }
      }
    };

    const cancelServiceForm = () => {
      showServiceForm.value = false;
      editingService.value = null;
      Object.assign(serviceForm, {
        name: '',
        duration_minutes: 60,
        is_active: true,
      });
    };

    const cancelWorkingHourForm = () => {
      showWorkingHourForm.value = false;
      editingWorkingHour.value = null;
      workingHourErrors.value = {};
      Object.assign(workingHourForm, {
        day_of_week: 1,
        start_time: '09:00',
        end_time: '17:00',
        is_active: true,
      });
    };

    return {
      services,
      workingHours,
      loadingServices,
      loadingWorkingHours,
      showServiceForm,
      showWorkingHourForm,
      editingService,
      editingWorkingHour,
      serviceForm,
      workingHourForm,
      formatDuration,
      formatTime,
      getDayName,
      getExistingDays,
      workingHourErrors,
      editService,
      editWorkingHour,
      saveService,
      saveWorkingHour,
      deleteService,
      deleteWorkingHour,
      cancelServiceForm,
      cancelWorkingHourForm,
    };
  },
};
</script>
