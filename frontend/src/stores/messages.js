import { defineStore } from 'pinia'
import { messagesService } from '@/services/messages'
import { conversationsService } from '@/services/conversations'
import { useToast } from '@/composables/useToast'

export const useMessagesStore = defineStore('messages', {
  state: () => ({
    messages: {},
    typingUsers: {},
    loading: false,
    error: null
  }),

  getters: {
    getMessagesByConversation: (state) => (conversationId) => {
      // Normalize ID to handle both string and number
      const normalizedId = String(conversationId)
      return state.messages[normalizedId] || state.messages[Number(normalizedId)] || []
    },
    isTyping: (state) => (conversationId, userId) => {
      const normalizedId = String(conversationId)
      return state.typingUsers[normalizedId]?.includes(userId) || state.typingUsers[Number(normalizedId)]?.includes(userId) || false
    }
  },

  actions: {
    // Helper to normalize conversation ID
    normalizeId(id) {
      return String(id)
    },

    async fetchMessages(conversationId, params = {}) {
      this.loading = true
      this.error = null
      try {
        const normalizedId = this.normalizeId(conversationId)
        const response = await conversationsService.getMessages(conversationId, params)
        
        // Handle paginated response structure
        // Backend returns: { status, message, data: { data: [...messages...], current_page, ... } }
        let messages = []
        if (response.data) {
          // Check if it's a paginated response (response.data.data.data contains the array)
          if (response.data.data && response.data.data.data && Array.isArray(response.data.data.data)) {
            messages = response.data.data.data
          }
          // Check if response.data.data is directly the array (non-paginated)
          else if (response.data.data && Array.isArray(response.data.data)) {
            messages = response.data.data
          }
          // Check if response.data is directly an array
          else if (Array.isArray(response.data)) {
            messages = response.data
          }
          // Check for messages property
          else if (response.data.messages && Array.isArray(response.data.messages)) {
            messages = response.data.messages
          }
        }
        
        // Ensure messages array exists
        if (!Array.isArray(messages)) {
          console.warn('Unexpected messages response structure:', response.data)
          messages = []
        }
        
        // Initialize if needed
        if (!this.messages[normalizedId]) {
          this.messages[normalizedId] = []
        }
        
        // Reverse to show oldest first, then newest at bottom
        this.messages[normalizedId] = messages.reverse()
        
        console.log(`Loaded ${messages.length} messages for conversation ${normalizedId}`, {
          responseStructure: response.data,
          messagesCount: messages.length
        })
        
        return response
      } catch (error) {
        console.error('Error fetching messages:', error)
        this.error = error.response?.data?.message || error.response?.data?.errors || 'Failed to fetch messages'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async sendMessage(conversationId, data) {
      this.loading = true
      this.error = null
      try {
        const normalizedId = this.normalizeId(conversationId)
        const response = await messagesService.create(conversationId, data)
        const message = response.data.data || response.data
        
        if (!this.messages[normalizedId]) {
          this.messages[normalizedId] = []
        }
        this.messages[normalizedId].push(message)
        
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to send message'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateMessage(id, data) {
      this.loading = true
      this.error = null
      try {
        const response = await messagesService.update(id, data)
        const message = response.data.data || response.data
        
        // Update message in all conversations
        Object.keys(this.messages).forEach(conversationId => {
          const index = this.messages[conversationId].findIndex(m => m.id === id)
          if (index !== -1) {
            this.messages[conversationId][index] = message
          }
        })
        
        useToast().success('Message updated successfully!')
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update message'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteMessage(id) {
      this.loading = true
      this.error = null
      try {
        await messagesService.delete(id)
        
        // Remove message from all conversations
        Object.keys(this.messages).forEach(conversationId => {
          this.messages[conversationId] = this.messages[conversationId].filter(m => m.id !== id)
        })
        
        useToast().success('Message deleted successfully!')
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete message'
        useToast().error(this.error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async markAsRead(messageId) {
      try {
        await messagesService.markAsRead(messageId)
      } catch (error) {
        console.error('Failed to mark message as read:', error)
      }
    },

    async sendTyping(conversationId) {
      try {
        await messagesService.typing(conversationId)
      } catch (error) {
        console.error('Failed to send typing indicator:', error)
      }
    },

    async stopTyping(conversationId) {
      try {
        await messagesService.stopTyping(conversationId)
      } catch (error) {
        console.error('Failed to stop typing indicator:', error)
      }
    },

    addMessage(conversationId, message) {
      const normalizedId = this.normalizeId(conversationId)
      if (!this.messages[normalizedId]) {
        this.messages[normalizedId] = []
      }
      // Check if message already exists to avoid duplicates
      const exists = this.messages[normalizedId].find(m => m.id === message.id)
      if (!exists) {
        this.messages[normalizedId].push(message)
        // Sort by created_at to maintain order
        this.messages[normalizedId].sort((a, b) => {
          return new Date(a.created_at) - new Date(b.created_at)
        })
      }
    },

    setTypingUsers(conversationId, userIds) {
      const normalizedId = this.normalizeId(conversationId)
      if (!this.typingUsers[normalizedId]) {
        this.typingUsers[normalizedId] = []
      }
      this.typingUsers[normalizedId] = userIds
    }
  }
})

