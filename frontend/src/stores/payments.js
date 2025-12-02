import { defineStore } from 'pinia'
import { paymentsService } from '@/services/payments'
import { useToast } from '@/composables/useToast'

export const usePaymentsStore = defineStore('payments', {
  state: () => ({
    payments: [],
    currentPayment: null,
    loading: false,
    error: null
  }),

  getters: {
    getPaymentById: (state) => (id) => {
      return state.payments.find(p => p.id === id) || state.currentPayment
    }
  },

  actions: {
    async fetchPayments(params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await paymentsService.getAll(params)
        const data = response.data.data || response.data
        
        // Handle paginated response
        if (data && data.data && Array.isArray(data.data)) {
          this.payments = data.data
        } else if (Array.isArray(data)) {
          this.payments = data
        } else {
          this.payments = []
        }
        
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch payments'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async createPayment(data) {
      this.loading = true
      this.error = null
      try {
        const response = await paymentsService.create(data)
        const payment = response.data.data || response.data
        this.currentPayment = payment
        this.payments.unshift(payment)
        useToast().success('Payment created successfully!')
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create payment'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async getPayment(id) {
      this.loading = true
      this.error = null
      try {
        const response = await paymentsService.getById(id)
        const payment = response.data.data || response.data
        this.currentPayment = payment
        
        const index = this.payments.findIndex(p => p.id === id)
        if (index !== -1) {
          this.payments[index] = payment
        } else {
          this.payments.push(payment)
        }
        
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch payment'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async checkPaymentStatus(id) {
      this.loading = true
      this.error = null
      try {
        const response = await paymentsService.checkStatus(id)
        const paymentData = response.data.data || response.data
        
        // Extract URL and QR code from response if they exist
        const paymentUrl = paymentData.payment_url 
          || paymentData.businessAppLink 
          || paymentData.personalAppLink
          || (paymentData.fib_response && (paymentData.fib_response.businessAppLink || paymentData.fib_response.personalAppLink || paymentData.fib_response.payment_url))
        
        const qrCode = paymentData.qr_code 
          || paymentData.qrCode
          || (paymentData.fib_response && paymentData.fib_response.qrCode)
        
        // Merge extracted data
        const payment = {
          ...paymentData,
          payment_url: paymentUrl || paymentData.payment_url,
          qr_code: qrCode || paymentData.qr_code
        }
        
        // Find and update payment in the list
        const index = this.payments.findIndex(p => p.id === id || p.id === payment.id)
        if (index !== -1) {
          // Merge to preserve any existing fields
          this.payments[index] = { ...this.payments[index], ...payment }
        } else {
          // If not found, add it
          this.payments.push(payment)
        }
        
        // Update current payment if it matches
        if (this.currentPayment && (this.currentPayment.id === id || this.currentPayment.id === payment.id)) {
          this.currentPayment = { ...this.currentPayment, ...payment }
        }
        
        // Only show toast if explicitly requested (not during polling)
        // Don't show toast messages during automatic polling
        
        return { ...response, data: { ...response.data, data: payment } }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to check payment status'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async retryPayment(id) {
      this.loading = true
      this.error = null
      try {
        const response = await paymentsService.retry(id)
        const payment = response.data.data || response.data
        
        // Find and update payment in the list
        const index = this.payments.findIndex(p => p.id === id)
        if (index !== -1) {
          this.payments[index] = { ...this.payments[index], ...payment }
        } else {
          this.payments.push(payment)
        }
        
        // Update current payment if it matches
        if (this.currentPayment?.id === id) {
          this.currentPayment = { ...this.currentPayment, ...payment }
        }
        
        useToast().success('Payment reprocessed successfully!')
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to retry payment'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updatePaymentStatus(id, status) {
      this.loading = true
      this.error = null
      try {
        const response = await paymentsService.updateStatus(id, status)
        const payment = response.data.data || response.data
        
        // Find and update payment in the list
        const index = this.payments.findIndex(p => p.id === id)
        if (index !== -1) {
          this.payments[index] = { ...this.payments[index], ...payment }
        } else {
          this.payments.push(payment)
        }
        
        // Update current payment if it matches
        if (this.currentPayment?.id === id) {
          this.currentPayment = { ...this.currentPayment, ...payment }
        }
        
        useToast().success(`Payment status updated to ${status}!`)
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update payment status'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async cancelPayment(id) {
      this.loading = true
      this.error = null
      try {
        const response = await paymentsService.cancel(id)
        const payment = response.data.data || response.data
        
        const index = this.payments.findIndex(p => p.id === id)
        if (index !== -1) {
          this.payments[index] = { ...this.payments[index], ...payment }
        }
        
        if (this.currentPayment?.id === id) {
          this.currentPayment = { ...this.currentPayment, ...payment }
        }
        
        useToast().success('Payment cancelled successfully!')
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to cancel payment'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})

