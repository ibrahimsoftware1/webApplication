import api from './api'

export const conversationsService = {
  getAll: (params = {}) => api.get('/conversations', { params }),
  getCommunityChat: () => api.get('/conversations/community-chat'),
  getById: (id) => api.get(`/conversations/${id}`),
  create: (data) => api.post('/conversations', data),
  update: (id, data) => api.put(`/conversations/${id}`, data),
  delete: (id) => api.delete(`/conversations/${id}`),
  addParticipants: (id, data) => api.post(`/conversations/${id}/participants`, data),
  removeParticipant: (id, userId) => api.delete(`/conversations/${id}/participants/${userId}`),
  markAsRead: (id) => api.post(`/conversations/${id}/read`),
  getMessages: (id, params = {}) => api.get(`/conversations/${id}/messages`, { params }),
  join: (id) => api.post(`/conversations/${id}/join`)
}

