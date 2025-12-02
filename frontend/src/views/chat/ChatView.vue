<template>
  <BaseLayout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $t('chat.title') }}</h1>
          <div class="flex gap-2">
            <button
              v-if="!deleteMode"
              @click="deleteMode = true"
              class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600 transition-colors"
            >
              🗑️ {{ $t('chat.deleteConversations') }}
            </button>
            <div v-else class="flex gap-2 items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ selectedConversations.length }} {{ $t('chat.selected') }}
              </span>
              <button
                @click="handleDeleteSelected"
                :disabled="selectedConversations.length === 0"
                class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                🗑️ {{ $t('chat.deleteSelected') }} ({{ selectedConversations.length }})
              </button>
              <button
                @click="deleteMode = false; selectedConversations = []"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
              >
                {{ $t('common.cancel') }}
              </button>
            </div>
          </div>
        </div>
        
        <div v-if="conversationsStore.loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        </div>
        
        <div v-else-if="conversationsStore.error" class="card bg-red-50 dark:bg-red-900 border-red-200 dark:border-red-700">
          <p class="text-red-800 dark:text-red-200">{{ conversationsStore.error }}</p>
        </div>
        
        <div v-else-if="!conversationsStore.hasConversations" class="card text-center py-12">
          <p class="text-gray-600 dark:text-gray-400">{{ $t('chat.noConversations') }}</p>
        </div>
        
        <div v-else class="space-y-4">
          <Card
            v-for="conversation in conversationsStore.conversations"
            :key="conversation.id"
            class="hover:shadow-md transition-shadow"
            :class="deleteMode ? 'cursor-default' : 'cursor-pointer'"
            @click="deleteMode ? null : $router.push(`/chat/${conversation.id}`)"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4 flex-1">
                <!-- Checkbox for delete mode -->
                <div v-if="deleteMode" class="flex items-center flex-shrink-0">
                  <input
                    type="checkbox"
                    :value="conversation.id"
                    v-model="selectedConversations"
                    class="w-5 h-5 cursor-pointer"
                    @click.stop
                  />
                </div>
                <div class="flex-shrink-0">
                  <div class="h-12 w-12 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white flex items-center justify-center font-semibold overflow-hidden">
                    <img
                      v-if="getConversationAvatar(conversation)"
                      :src="getConversationAvatar(conversation)"
                      :alt="getConversationName(conversation)"
                      class="w-full h-full object-cover"
                    />
                    <span v-else class="text-white">
                      {{ getConversationInitial(conversation) }}
                    </span>
                  </div>
                </div>
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ getConversationName(conversation) }}
                  </h3>
                  <p v-if="conversation.last_message" class="text-sm text-gray-600 dark:text-gray-400">
                    {{ conversation.last_message.content }}
                  </p>
                </div>
              </div>
              <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(conversation.last_message_at) }}
              </div>
            </div>
          </Card>
        </div>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useConversationsStore } from '@/stores/conversations'
import { useAuthStore } from '@/stores/auth'
import { useAvatar } from '@/composables/useAvatar'
import { useToast } from '@/composables/useToast'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'

const router = useRouter()
const { t } = useI18n()
const conversationsStore = useConversationsStore()
const authStore = useAuthStore()
const { getAvatarUrl } = useAvatar()

const deleteMode = ref(false)
const selectedConversations = ref([])

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}

const getConversationName = (conversation) => {
  if (!conversation) {
    return t('chat.conversation')
  }
  if (conversation.type === 'private' && conversation.users) {
    const otherUser = conversation.users.find(u => u.id !== authStore.user?.id)
    return otherUser?.name || 'User'
  }
  return conversation.name || 'Group Chat'
}

const getConversationAvatar = (conversation) => {
  if (!conversation) return null
  if (conversation.type === 'private' && conversation.users) {
    const otherUser = conversation.users.find(u => u.id !== authStore.user?.id)
    return otherUser?.avatar ? getAvatarUrl(otherUser.avatar) : null
  }
  return conversation.avatar ? getAvatarUrl(conversation.avatar) : null
}

const getConversationInitial = (conversation) => {
  const name = getConversationName(conversation)
  return name.charAt(0).toUpperCase()
}

const handleDeleteSelected = async () => {
  if (selectedConversations.value.length === 0) return
  
  const count = selectedConversations.value.length
  const confirmMessage = `Are you sure you want to delete ${count} conversation${count > 1 ? 's' : ''}? This action cannot be undone.`
  
  if (!confirm(confirmMessage)) return

  try {
    // Delete all selected conversations (don't show individual toasts)
    for (const conversationId of selectedConversations.value) {
      await conversationsStore.deleteConversation(conversationId, false)
    }
    
    // Clear selection and exit delete mode
    selectedConversations.value = []
    deleteMode.value = false
    
    useToast().success(`Successfully deleted ${count} conversation${count > 1 ? 's' : ''}`)
  } catch (error) {
    console.error('Failed to delete conversations:', error)
    useToast().error('Failed to delete some conversations')
  }
}

onMounted(() => {
  conversationsStore.fetchConversations()
})
</script>

