import axios from 'axios';

// Configure axios defaults
axios.defaults.baseURL = '/api';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Create API service
const api = {
  // Availability endpoints
  async getAvailableSlots(date, serviceId = null) {
    const params = { date };
    if (serviceId) params.service_id = serviceId;

    const response = await axios.get('/availability/slots', { params });
    return response.data;
  },

  async checkSlotAvailability(date, time, serviceId) {
    const params = { date, time, service_id: serviceId };
    const response = await axios.get('/availability/check', { params });
    return response.data;
  },

  // Appointment endpoints
  async bookAppointment(appointmentData) {
    const response = await axios.post('/appointments', appointmentData);
    return response.data;
  },

  async getAppointment(id) {
    const response = await axios.get(`/appointments/${id}`);
    return response.data;
  },

  async cancelAppointment(id) {
    const response = await axios.delete(`/appointments/${id}/cancel`);
    return response.data;
  },

  async getAppointmentsForDateRange(startDate, endDate) {
    const params = { start_date: startDate, end_date: endDate };
    const response = await axios.get('/appointments/range', { params });
    return response.data;
  },

  // Service endpoints
  async getServices() {
    const response = await axios.get('/services');
    return response.data;
  },

  async getService(id) {
    const response = await axios.get(`/services/${id}`);
    return response.data;
  },

  async createService(serviceData) {
    const response = await axios.post('/services', serviceData);
    return response.data;
  },

  async updateService(id, serviceData) {
    const response = await axios.put(`/services/${id}`, serviceData);
    return response.data;
  },

  async deleteService(id) {
    const response = await axios.delete(`/services/${id}`);
    return response.data;
  },

  // Working hours endpoints
  async getWorkingHours() {
    const response = await axios.get('/working-hours');
    return response.data;
  },

  async getWorkingHour(id) {
    const response = await axios.get(`/working-hours/${id}`);
    return response.data;
  },

  async createWorkingHour(workingHourData) {
    const response = await axios.post('/working-hours', workingHourData);
    return response.data;
  },

  async updateWorkingHour(id, workingHourData) {
    const response = await axios.put(`/working-hours/${id}`, workingHourData);
    return response.data;
  },

  async deleteWorkingHour(id) {
    const response = await axios.delete(`/working-hours/${id}`);
    return response.data;
  },
};

export default api;
