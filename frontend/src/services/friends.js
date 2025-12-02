import api from './api'

export const friendsService = {
  getAll: () => api.get('/friends'),
  getRequests: () => api.get('/friends/requests'),
  sendRequest: (data) => api.post('/friends/request', data),
  acceptRequest: (id) => api.post(`/friends/accept/${id}`),
  rejectRequest: (id) => api.post(`/friends/reject/${id}`),
  remove: (id) => api.delete(`/friends/${id}`),
  checkStatus: (userId) => api.get(`/friends/status/${userId}`)
}

