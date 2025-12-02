import api from './api'

export const subscriptionsService = {
  getAll: () => api.get('/subscriptions'),
  getVerifiedStatus: () => api.get('/subscriptions/verified-status'),
  purchaseVerified: () => api.post('/subscriptions/purchase-verified') // Fixed amount: 1000 IQD
}

