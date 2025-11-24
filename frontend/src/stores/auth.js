import { defineStore } from 'pinia'
import { authService } from '@/services/auth'
import { useToast } from '@/composables/useToast'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false
  }),

  getters: {
    userName: (state) => state.user?.name || '',
    userEmail: (state) => state.user?.email || '',
    isAdmin: (state) => {
      if (!state.user) return false
      const roles = state.user.roles
      if (Array.isArray(roles)) {
        return roles.includes('admin') || roles.some(r => (typeof r === 'string' ? r : r.name) === 'admin')
      }
      return state.user.has_admin_role === true
    }
  },

  actions: {
    async login(credentials) {
      try {
        const response = await authService.login(credentials)
        // Backend returns: { status, message, data: { user, access_token } }
        this.token = response.data.data?.access_token || response.data.access_token || response.data.token
        this.user = response.data.data?.user || response.data.user
        this.isAuthenticated = true
        
        // Store token in localStorage for persistence
        if (this.token) {
          localStorage.setItem('auth_token', this.token)
        }
        
        useToast().success('Login successful!')
        return response
      } catch (error) {
        // Check if error is due to unverified email
        if (error.response?.status === 403 && error.response?.data?.data?.email_verified === false) {
          const emailVerificationError = {
            ...error,
            emailVerificationRequired: true,
            email: credentials.email
          }
          throw emailVerificationError
        }
        // Backend returns errors in format: { errors: "...", status: 400 }
        const errorMessage = error.response?.data?.errors || error.response?.data?.message || 'Login failed'
        useToast().error(typeof errorMessage === 'string' ? errorMessage : 'Login failed')
        throw error
      }
    },

    async register(data) {
      try {
        const response = await authService.register(data)
        // Registration doesn't auto-login, user needs to verify email first
        useToast().success('Registration successful! Please check your email to verify your account.')
        return response
      } catch (error) {
        const errorMessage = error.response?.data?.message || error.response?.data?.errors || 'Registration failed'
        useToast().error(errorMessage)
        throw error
      }
    },

    async resendVerificationEmail(email) {
      try {
        const response = await authService.resendVerification(email)
        useToast().success('Verification email sent! Please check your inbox.')
        return response
      } catch (error) {
        const errorMessage = error.response?.data?.message || 'Failed to resend verification email'
        useToast().error(errorMessage)
        throw error
      }
    },

    async verifyEmail(userId, hash) {
      try {
        const response = await authService.verifyEmail(userId, hash)
        useToast().success('Email verified successfully! You can now log in.')
        return response
      } catch (error) {
        const errorMessage = error.response?.data?.message || 'Email verification failed'
        useToast().error(errorMessage)
        throw error
      }
    },

    async logout() {
      try {
        await authService.logout()
      } catch (error) {
        console.error('Logout error:', error)
        // Continue with logout even if API call fails
      } finally {
        // Clear all auth state
        this.user = null
        this.token = null
        this.isAuthenticated = false
        
        // Clear localStorage
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth') // Clear persisted Pinia state
        
        // Show success message
        useToast().success('Logged out successfully')
      }
    },

    async fetchProfile() {
      try {
        const response = await authService.getProfile()
        // Backend returns: { status, message, data: { user } }
        this.user = response.data.data?.user || response.data.data || response.data.user
        return response
      } catch (error) {
        console.error('Fetch profile error:', error)
        throw error
      }
    },

    initializeAuth() {
      const token = localStorage.getItem('auth_token')
      if (token) {
        this.token = token
        this.isAuthenticated = true
        // Fetch user profile
        this.fetchProfile().catch(() => {
          // If profile fetch fails, clear auth
          this.logout()
        })
      }
    }
  },

  persist: {
    key: 'auth',
    paths: ['token', 'isAuthenticated']
  }
})

