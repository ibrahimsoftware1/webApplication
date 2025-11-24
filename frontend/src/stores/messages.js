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
      return state.messages[conversationId] || []
    },
    isTyping: (state) => (conversationId, userId) => {
      return state.typingUsers[conversationId]?.includes(userId) || false
    }
  },

  actions: {
    async fetchMessages(conversationId, params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await conversationsService.getMessages(conversationId, params)
        const data = response.data.data || response.data
        const messages = Array.isArray(data) ? data : (data.data || [])
        
        if (!this.messages[conversationId]) {
          this.messages[conversationId] = []
        }
        // Reverse to show oldest first, then newest at bottom
        this.messages[conversationId] = messages.reverse()
        
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch messages'
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
        const response = await messagesService.create(conversationId, data)
        const message = response.data.data || response.data
        
        if (!this.messages[conversationId]) {
          this.messages[conversationId] = []
        }
        this.messages[conversationId].push(message)
        
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
      if (!this.messages[conversationId]) {
        this.messages[conversationId] = []
      }
      // Check if message already exists to avoid duplicates
      const exists = this.messages[conversationId].find(m => m.id === message.id)
      if (!exists) {
        this.messages[conversationId].push(message)
        // Sort by created_at to maintain order
        this.messages[conversationId].sort((a, b) => {
          return new Date(a.created_at) - new Date(b.created_at)
        })
      }
    },

    setTypingUsers(conversationId, userIds) {
      if (!this.typingUsers[conversationId]) {
        this.typingUsers[conversationId] = []
      }
      this.typingUsers[conversationId] = userIds
    }
  }
})

