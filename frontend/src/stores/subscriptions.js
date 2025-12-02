import { defineStore } from 'pinia'
import { subscriptionsService } from '@/services/subscriptions'
import { useToast } from '@/composables/useToast'

export const useSubscriptionsStore = defineStore('subscriptions', {
  state: () => ({
    subscriptions: [],
    verifiedStatus: null,
    loading: false,
    error: null
  }),

  getters: {
    hasVerifiedBadge: (state) => {
      return state.verifiedStatus?.is_verified === true && state.verifiedStatus?.has_active_subscription === true
    }
  },

  actions: {
    async fetchSubscriptions() {
      this.loading = true
      this.error = null
      try {
        const response = await subscriptionsService.getAll()
        const data = response.data.data || response.data
        this.subscriptions = Array.isArray(data) ? data : []
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch subscriptions'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchVerifiedStatus() {
      this.loading = true
      this.error = null
      try {
        const response = await subscriptionsService.getVerifiedStatus()
        this.verifiedStatus = response.data.data || response.data
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch verified status'
        throw error
      } finally {
        this.loading = false
      }
    },

    async purchaseVerified() {
      this.loading = true
      this.error = null
      try {
        // Fixed amount: 1000 IQD - no need to pass amount
        const response = await subscriptionsService.purchaseVerified()
        const data = response.data.data || response.data
        useToast().success('Payment created successfully! Complete the payment to get verified.')
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to purchase verified badge'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})

