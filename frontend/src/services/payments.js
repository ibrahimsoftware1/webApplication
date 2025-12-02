import api from './api'

export const paymentsService = {
  getAll: (params = {}) => api.get('/payments', { params }),
  create: (data) => api.post('/payments', data),
  getById: (id) => api.get(`/payments/${id}`),
  checkStatus: (id) => api.post(`/payments/${id}/check-status`),
  retry: (id) => api.post(`/payments/${id}/retry`),
  updateStatus: (id, status) => api.post(`/payments/${id}/update-status`, { status }),
  cancel: (id) => api.post(`/payments/${id}/cancel`)
}

