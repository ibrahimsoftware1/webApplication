import { defineStore } from 'pinia'
import { usersService } from '@/services/users'
import { useToast } from '@/composables/useToast'

export const useUsersStore = defineStore('users', {
  state: () => ({
    users: [],
    currentUser: null,
    loading: false,
    error: null
  }),

  getters: {
    hasUsers: (state) => state.users.length > 0
  },

  actions: {
    async fetchUsers(params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await usersService.getAll(params)
        const data = response.data.data || response.data
        this.users = Array.isArray(data) ? data : (data.data || [])
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch users'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchUser(id) {
      this.loading = true
      this.error = null
      try {
        const response = await usersService.getById(id)
        this.currentUser = response.data.data || response.data
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch user'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
