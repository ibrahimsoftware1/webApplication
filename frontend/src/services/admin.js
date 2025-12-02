import api from './api'

export const adminService = {
  getDashboard: () => api.get('/admin/dashboard'),
  getUsers: (params = {}) => api.get('/admin/users', { params }),
  getUserDetails: (id) => api.get(`/admin/users/${id}`),
  banUser: (id) => api.post(`/admin/users/${id}/ban`),
  unbanUser: (id) => api.post(`/admin/users/${id}/unban`),
  assignRole: (id, data) => api.post(`/admin/users/${id}/assign-role`, data),
  deleteUser: (id) => api.delete(`/admin/users/${id}`),
  removeUserFromCommunity: (id) => api.post(`/admin/users/${id}/remove-from-community`),
  deleteMessage: (id) => api.delete(`/admin/messages/${id}`)
}

