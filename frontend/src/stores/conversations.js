import { defineStore } from 'pinia'
import { conversationsService } from '@/services/conversations'
import { useToast } from '@/composables/useToast'

export const useConversationsStore = defineStore('conversations', {
  state: () => ({
    conversations: [],
    currentConversation: null,
    communityChat: null,
    loading: false,
    error: null
  }),

  getters: {
    hasConversations: (state) => state.conversations.length > 0,
    conversationById: (state) => (id) => {
      if (id === undefined || id === null) return undefined
      const normalizedId = typeof id === 'number' ? id : parseInt(id, 10)
      if (Number.isNaN(normalizedId)) {
        return state.conversations.find(c => String(c.id) === String(id))
      }
      return state.conversations.find(c => c.id === normalizedId)
    }
  },

  actions: {
    async fetchConversations() {
      this.loading = true
      this.error = null
      try {
        const response = await conversationsService.getAll()
        const data = response.data.data || response.data
        this.conversations = Array.isArray(data) ? data : (data.data || [])
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch conversations'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchCommunityChat() {
      this.loading = true
      this.error = null
      try {
        const response = await conversationsService.getCommunityChat()
        this.communityChat = response.data.data || response.data
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch community chat'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchConversation(id) {
      this.loading = true
      this.error = null
      try {
        const response = await conversationsService.getById(id)
        const conversation = response.data.data || response.data
        this.currentConversation = conversation
        
        const index = this.conversations.findIndex(c => c.id === id)
        if (index !== -1) {
          this.conversations[index] = conversation
        } else {
          this.conversations.push(conversation)
        }
        
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch conversation'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async createConversation(data) {
      this.loading = true
      this.error = null
      try {
        const response = await conversationsService.create(data)
        const conversation = response.data.data || response.data
        this.conversations.unshift(conversation)
        useToast().success('Conversation created successfully!')
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create conversation'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateConversation(id, data) {
      this.loading = true
      this.error = null
      try {
        const response = await conversationsService.update(id, data)
        const conversation = response.data.data || response.data
        
        const index = this.conversations.findIndex(c => c.id === id)
        if (index !== -1) {
          this.conversations[index] = conversation
        }
        
        if (this.currentConversation?.id === id) {
          this.currentConversation = conversation
        }
        
        useToast().success('Conversation updated successfully!')
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update conversation'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteConversation(id, showToast = true) {
      this.loading = true
      this.error = null
      try {
        await conversationsService.delete(id)
        this.conversations = this.conversations.filter(c => c.id !== id)
        if (this.currentConversation?.id === id) {
          this.currentConversation = null
        }
        if (showToast) {
          useToast().success('Conversation deleted successfully!')
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete conversation'
        if (showToast) {
          useToast().error(this.error)
        }
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})

