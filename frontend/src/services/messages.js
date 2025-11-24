import api from './api'

export const messagesService = {
  create: (conversationId, data) => {
    // If data is FormData, don't set Content-Type header (browser will set it with boundary)
    if (data instanceof FormData) {
      return api.post(`/messages/conversations/${conversationId}`, data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    return api.post(`/messages/conversations/${conversationId}`, data)
  },
  update: (id, data) => api.put(`/messages/${id}`, data),
  delete: (id) => api.delete(`/messages/${id}`),
  markAsRead: (id) => api.post(`/messages/${id}/read`),
  typing: (conversationId) => api.post(`/messages/conversations/${conversationId}/typing`),
  stopTyping: (conversationId) => api.post(`/messages/conversations/${conversationId}/stop-typing`)
}

