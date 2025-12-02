import { defineStore } from 'pinia'
import { adminService } from '@/services/admin'
import { useToast } from '@/composables/useToast'

export const useAdminStore = defineStore('admin', {
  state: () => ({
    stats: null,
    users: [],
    currentUser: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchDashboard() {
      this.loading = true
      this.error = null
      try {
        const response = await adminService.getDashboard()
        this.stats = response.data.data?.Stats || response.data.data || {}
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch dashboard'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchUsers(params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await adminService.getUsers(params)
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

    async fetchUserDetails(id) {
      this.loading = true
      this.error = null
      try {
        const response = await adminService.getUserDetails(id)
        this.currentUser = response.data.data || response.data
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch user details'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async banUser(id) {
      try {
        await adminService.banUser(id)
        useToast().success('User banned successfully')
        await this.fetchUsers()
      } catch (error) {
        useToast().error(error.response?.data?.message || 'Failed to ban user')
        throw error
      }
    },

    async unbanUser(id) {
      try {
        await adminService.unbanUser(id)
        useToast().success('User unbanned successfully')
        await this.fetchUsers()
      } catch (error) {
        useToast().error(error.response?.data?.message || 'Failed to unban user')
        throw error
      }
    },

    async assignRole(id, data) {
      try {
        await adminService.assignRole(id, data)
        useToast().success('Role assigned successfully')
        await this.fetchUsers()
      } catch (error) {
        useToast().error(error.response?.data?.message || 'Failed to assign role')
        throw error
      }
    },

    async deleteUser(id) {
      try {
        await adminService.deleteUser(id)
        useToast().success('User deleted successfully')
        await this.fetchUsers()
      } catch (error) {
        useToast().error(error.response?.data?.message || 'Failed to delete user')
        throw error
      }
    },

    async removeUserFromCommunity(id) {
      try {
        await adminService.removeUserFromCommunity(id)
        useToast().success('User removed from community successfully')
        await this.fetchUsers()
      } catch (error) {
        useToast().error(error.response?.data?.message || 'Failed to remove user from community')
        throw error
      }
    },

    async deleteMessage(id) {
      try {
        await adminService.deleteMessage(id)
        useToast().success('Message deleted successfully')
      } catch (error) {
        useToast().error(error.response?.data?.message || 'Failed to delete message')
        throw error
      }
    }
  }
})

