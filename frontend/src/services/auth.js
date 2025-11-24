import api from './api'

export const authService = {
  login: (credentials) => api.post('/login', credentials),
  register: (data) => api.post('/register', data),
  logout: () => api.post('/logout'),
  getProfile: () => api.get('/profile'),
  updateProfile: (data) => api.put('/profile/update-profile', data),
  updateAvatar: (formData) => api.post('/profile/avatar', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  removeAvatar: () => api.delete('/profile/avatar'),
  changePassword: (data) => api.post('/profile/change-password', data),
  forgotPassword: (email) => api.post('/forgot-password', { email }),
  resetPassword: (data) => api.post('/reset-password', data),
  resendVerification: (email) => api.post('/email/resend', { email }),
  verifyEmail: (userId, hash) => api.get(`/email/verify/${userId}/${hash}`)
}

