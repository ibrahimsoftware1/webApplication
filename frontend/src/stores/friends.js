import { defineStore } from 'pinia'
import { friendsService } from '@/services/friends'
import { useToast } from '@/composables/useToast'

export const useFriendsStore = defineStore('friends', {
  state: () => ({
    friends: [],
    requests: {
      sent: [],
      received: []
    },
    loading: false,
    error: null
  }),

  getters: {
    hasFriends: (state) => state.friends.length > 0,
    hasRequests: (state) => state.requests.sent.length > 0 || state.requests.received.length > 0
  },

  actions: {
    async fetchFriends() {
      this.loading = true
      this.error = null
      try {
        const response = await friendsService.getAll()
        this.friends = response.data.data || response.data || []
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch friends'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchRequests() {
      this.loading = true
      this.error = null
      try {
        const response = await friendsService.getRequests()
        const data = response.data.data || response.data
        this.requests = {
          sent: data.sent || data.sent_requests || [],
          received: data.received || data.received_requests || []
        }
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch requests'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async sendRequest(data) {
      this.loading = true
      this.error = null
      try {
        const response = await friendsService.sendRequest(data)
        useToast().success('Friend request sent!')
        await this.fetchRequests()
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to send friend request'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async acceptRequest(id) {
      this.loading = true
      this.error = null
      try {
        const response = await friendsService.acceptRequest(id)
        useToast().success('Friend request accepted!')
        await this.fetchFriends()
        await this.fetchRequests()
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to accept request'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async rejectRequest(id) {
      this.loading = true
      this.error = null
      try {
        await friendsService.rejectRequest(id)
        useToast().success('Friend request rejected')
        await this.fetchRequests()
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to reject request'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async removeFriend(id) {
      this.loading = true
      this.error = null
      try {
        await friendsService.remove(id)
        this.friends = this.friends.filter(f => f.id !== id)
        useToast().success('Friend removed')
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to remove friend'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async checkStatus(userId) {
      try {
        const response = await friendsService.checkStatus(userId)
        return response.data.data || response.data
      } catch (error) {
        return null
      }
    }
  }
})

