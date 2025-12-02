<template>
  <BaseLayout>
    <div class="flex flex-col" style="height: calc(100vh - 120px); min-height: 500px;">
      <!-- Header -->
      <div class="bg-gradient-to-r from-primary-600 to-purple-600 text-white px-4 py-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
          <router-link
            to="/chat"
            class="px-3 py-1 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-sm hover:bg-opacity-30 transition-colors"
          >
            {{ $t('chat.back') }}
          </router-link>
          <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center font-bold text-lg flex-shrink-0 overflow-hidden">
            <img
              v-if="conversationAvatar"
              :src="conversationAvatar"
              :alt="conversationName"
              class="w-full h-full object-cover"
            />
            <span v-else>{{ conversationName?.charAt(0).toUpperCase() || 'C' }}</span>
          </div>
          <div class="flex-1">
            <div class="font-semibold text-lg flex items-center gap-2">
              {{ conversationName }}
              <!-- Show badge for other user in conversation -->
              <VerifiedBadge 
                v-if="currentConversation?.users?.find(u => u.id !== authStore.user?.id)"
                :show="!!(currentConversation?.users?.find(u => u.id !== authStore.user?.id)?.is_verified)" 
                size="sm" 
              />
              <!-- Show badge for current user if they're verified -->
              <VerifiedBadge 
                v-else-if="authStore.user"
                :show="!!authStore.user.is_verified" 
                size="sm" 
              />
            </div>
            <div class="flex items-center gap-2 mt-1">
              <div
                :class="[
                  'w-2 h-2 rounded-full',
                  otherUserOnline ? 'bg-green-400 animate-pulse' : 'bg-gray-400'
                ]"
              ></div>
              <span class="text-xs opacity-90">
                {{ otherUserOnline ? $t('chat.online') : $t('chat.offline') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Messages Area -->
      <div
        ref="messagesContainer"
        class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-800 p-4 space-y-4"
      >
        <div v-if="messagesStore.loading && messages.length === 0" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          <p class="mt-2 text-gray-500 dark:text-gray-400">{{ $t('chat.loading') || 'Loading messages...' }}</p>
        </div>
        <div v-else-if="messages.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
          {{ $t('chat.noMessages') }}
        </div>

        <div
          v-for="(message, index) in messages"
          :key="message.id"
          class="flex gap-2"
          :class="message.user_id === authStore.user?.id ? 'justify-end' : 'justify-start'"
        >
          <div
            class="flex gap-2 max-w-[80%]"
            :class="message.user_id === authStore.user?.id ? 'flex-row-reverse' : ''"
          >
            <!-- Avatar (only for other users, and only on last message in sequence) -->
            <div
              v-if="shouldShowAvatar(message, index)"
              class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white flex items-center justify-center font-bold flex-shrink-0"
            >
              <img
                v-if="message.user?.avatar"
                :src="getAvatarUrl(message.user.avatar)"
                :alt="message.user.name"
                class="w-full h-full rounded-full object-cover"
              />
              <span v-else>{{ (message.user?.name || 'U').charAt(0).toUpperCase() }}</span>
            </div>
            <!-- Spacer for messages without avatar to maintain alignment -->
            <div
              v-else-if="message.user_id !== authStore.user?.id"
              class="w-10 flex-shrink-0"
            ></div>
            <div class="flex-1">
              <!-- Time for all messages (sender name removed) -->
              <div
                class="flex items-center gap-2 mb-1"
                :class="message.user_id === authStore.user?.id ? 'justify-end' : 'justify-start'"
              >
                <span class="text-xs text-gray-400">{{ formatTime(message.created_at) }}</span>
              </div>
              <!-- Message bubble -->
              <div
                :class="[
                  'px-4 py-2 rounded-xl inline-block max-w-full',
                  message.user_id === authStore.user?.id
                    ? 'bg-sky-300 dark:bg-sky-600 text-white'
                    : 'bg-sky-300 dark:bg-sky-700 text-gray-900 dark:text-gray-100'
                ]"
              >
                <p v-if="message.content" class="m-0 break-words">{{ message.content }}</p>
                <div v-if="message.attachments?.length > 0" class="mt-2">
                  <img
                    v-for="attachment in message.attachments"
                    :key="attachment.id"
                    v-if="attachment?.file_type?.startsWith('image/')"
                    :src="attachment.file_url || attachment.full_url"
                    alt="Image"
                    class="max-w-[300px] max-h-[300px] rounded-lg cursor-pointer"
                    @click="window.open(attachment.file_url || attachment.full_url, '_blank')"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Typing Indicator -->
      <div v-if="typingUser" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 italic">
          <span>{{ typingUser }} is typing</span>
          <div class="flex gap-1">
            <span class="w-1 h-1 bg-gray-600 dark:bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></span>
            <span class="w-1 h-1 bg-gray-600 dark:bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
            <span class="w-1 h-1 bg-gray-600 dark:bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
          </div>
        </div>
      </div>

      <!-- Message Input -->
      <div class="px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex gap-2 items-center">
        <input
          v-model="messageContent"
          type="text"
          :placeholder="$t('chat.typeMessage')"
          class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
          @keyup.enter="handleSendMessage"
          @input="handleTyping"
          @keydown="handleTyping"
          :disabled="!isChannelSubscribed"
        />
        <Button
          @click="handleSendMessage"
          :disabled="!messageContent.trim() || !isChannelSubscribed"
          class="px-6 py-2 bg-gradient-to-r from-primary-600 to-purple-600 text-white rounded-full font-semibold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ $t('chat.send') }}
        </Button>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useMessagesStore } from '@/stores/messages'
import { useAuthStore } from '@/stores/auth'
import { useConversationsStore } from '@/stores/conversations'
import { useEcho } from '@/composables/useEcho'
import { useAvatar } from '@/composables/useAvatar'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Button from '@/components/ui/Button.vue'
import VerifiedBadge from '@/components/ui/VerifiedBadge.vue'

const route = useRoute()
const { t } = useI18n()
const messagesStore = useMessagesStore()
const authStore = useAuthStore()
const conversationsStore = useConversationsStore()
const { getAvatarUrl } = useAvatar()

const messageContent = ref('')
const messagesContainer = ref(null)
const typingUser = ref(null)
const typingTimer = ref(null)
const isTyping = ref(false)
const lastTypingTime = ref(0)
const isChannelSubscribed = ref(false)
const channel = ref(null)
const echo = ref(null)

const conversationId = computed(() => {
  const id = route.params.id
  return id ? String(id) : null
})

const messages = computed(() => {
  if (!conversationId.value) return []
  return messagesStore.getMessagesByConversation(conversationId.value) || []
})

// Check if this is the last message in a sequence from the same sender
const shouldShowAvatar = (message, index) => {
  // Only show avatar for messages from other users
  if (message.user_id === authStore.user?.id) return false
  
  // If this is the last message, show avatar
  if (index === messages.value.length - 1) return true
  
  // If next message is from a different user, show avatar
  const nextMessage = messages.value[index + 1]
  if (!nextMessage) return true
  
  return nextMessage.user_id !== message.user_id
}

const currentConversation = computed(() => {
  if (!conversationId.value) return null
  return conversationsStore.conversationById(conversationId.value)
})

const conversationName = computed(() => {
  if (!currentConversation.value) return 'Conversation'
  if (currentConversation.value.type === 'private' && currentConversation.value.users) {
    const otherUser = currentConversation.value.users.find(u => u.id !== authStore.user?.id)
    return otherUser?.name || 'User'
  }
  return currentConversation.value.name || 'Group Chat'
})

const conversationAvatar = computed(() => {
  if (!currentConversation.value) return null
  if (currentConversation.value.type === 'private' && currentConversation.value.users) {
    const otherUser = currentConversation.value.users.find(u => u.id !== authStore.user?.id)
    return otherUser?.avatar ? getAvatarUrl(otherUser.avatar) : null
  }
  return currentConversation.value.avatar ? getAvatarUrl(currentConversation.value.avatar) : null
})

const otherUserOnline = computed(() => {
  if (!currentConversation.value || currentConversation.value.type !== 'private') return false
  const otherUser = currentConversation.value.users?.find(u => u.id !== authStore.user?.id)
  return otherUser?.is_online || false
})

const formatTime = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const initializeEcho = async () => {
  if (!authStore.token) return

  try {
    const echoComposable = useEcho(authStore.token)
    echo.value = echoComposable.initializeEcho()
    
    // Wait for connection
    await new Promise((resolve) => {
      if (echo.value?.connector?.pusher?.connection?.state === 'connected') {
        resolve()
      } else {
        const checkConnection = setInterval(() => {
          if (echo.value?.connector?.pusher?.connection?.state === 'connected') {
            clearInterval(checkConnection)
            resolve()
          }
        }, 100)
        setTimeout(() => {
          clearInterval(checkConnection)
          resolve()
        }, 5000)
      }
    })

    connectToChannel()
  } catch (error) {
    console.error('Failed to initialize Echo:', error)
  }
}

const connectToChannel = () => {
  if (!echo.value || !conversationId.value) return

  try {
    // Leave previous channel if exists
    if (channel.value) {
      echo.value.leave(`conversation.${conversationId.value}`)
    }

    channel.value = echo.value.private(`conversation.${conversationId.value}`)
      .subscribed(() => {
        console.log('✅ Successfully subscribed to conversation channel')
        isChannelSubscribed.value = true
      })
      .listen('.online-status-changed', (event) => {
        console.log('🟢 Online status changed:', event)
        if (event.user_id !== authStore.user?.id && currentConversation.value) {
          const otherUser = currentConversation.value.users?.find(u => u.id === event.user_id)
          if (otherUser) {
            otherUser.is_online = event.is_online
          }
        }
      })
      .listen('.message.sent', (event) => {
        console.log('📨 Message event received:', event)
        if (event.message && event.message.user_id !== authStore.user?.id) {
          messagesStore.addMessage(conversationId.value, event.message)
          scrollToBottom()
        }
      })
      .listen('.user.typing', (event) => {
        if (event.user_id !== authStore.user?.id) {
          typingUser.value = event.user_name || 'Someone'
          if (typingTimer.value) {
            clearTimeout(typingTimer.value)
          }
          typingTimer.value = setTimeout(() => {
            typingUser.value = null
          }, 3000)
        }
      })
      .listen('.user.stopped.typing', (event) => {
        if (event.user_id !== authStore.user?.id) {
          typingUser.value = null
          if (typingTimer.value) {
            clearTimeout(typingTimer.value)
            typingTimer.value = null
          }
        }
      })
      .error((error) => {
        console.error('❌ Channel subscription error:', error)
        isChannelSubscribed.value = false
        setTimeout(() => {
          connectToChannel()
        }, 3000)
      })

    console.log('✅ Channel listeners attached')
  } catch (error) {
    console.error('❌ Channel connection failed:', error)
    isChannelSubscribed.value = false
  }
}

const handleSendMessage = async () => {
  if (!messageContent.value.trim() || !isChannelSubscribed.value) return

  try {
    await messagesStore.sendMessage(conversationId.value, {
      content: messageContent.value.trim(),
      type: 'text'
    })
    messageContent.value = ''
    scrollToBottom()
  } catch (error) {
    console.error('Failed to send message:', error)
  }
}

const handleTyping = () => {
  if (!isChannelSubscribed.value) return

  const now = Date.now()
  if (!isTyping.value || (now - lastTypingTime.value) > 2000) {
    isTyping.value = true
    lastTypingTime.value = now
    messagesStore.sendTyping(conversationId.value).catch(() => {})
  }

  if (typingTimer.value) {
    clearTimeout(typingTimer.value)
  }

  typingTimer.value = setTimeout(() => {
    if (isTyping.value) {
      isTyping.value = false
      messagesStore.stopTyping(conversationId.value).catch(() => {})
    }
  }, 2000)
}

onMounted(async () => {
  // Load conversation details
  if (conversationId.value) {
    console.log('Loading conversation:', conversationId.value)
    try {
      await conversationsStore.fetchConversation(conversationId.value)
      console.log('Conversation loaded, fetching messages...')
      await messagesStore.fetchMessages(conversationId.value)
      const loadedMessages = messagesStore.getMessagesByConversation(conversationId.value)
      console.log('Messages loaded:', loadedMessages?.length || 0, loadedMessages)
      scrollToBottom()
      
      // Initialize Echo for real-time
      await initializeEcho()
    } catch (error) {
      console.error('Error loading conversation:', error)
    }
  }
})

watch(conversationId, async (newId) => {
  if (newId) {
    console.log('Conversation ID changed to:', newId)
    // Disconnect from previous channel
    if (channel.value && conversationId.value) {
      echo.value?.leave(`conversation.${conversationId.value}`)
      channel.value = null
      isChannelSubscribed.value = false
    }

    try {
      await conversationsStore.fetchConversation(newId)
      console.log('Conversation loaded, fetching messages...')
      await messagesStore.fetchMessages(newId)
      const loadedMessages = messagesStore.getMessagesByConversation(newId)
      console.log('Messages loaded:', loadedMessages?.length || 0, loadedMessages)
      scrollToBottom()
      
      // Connect to new channel
      if (echo.value) {
        connectToChannel()
      } else {
        await initializeEcho()
      }
    } catch (error) {
      console.error('Error loading conversation:', error)
    }
  }
})

onUnmounted(() => {
  // Clean up
  if (channel.value && conversationId.value) {
    echo.value?.leave(`conversation.${conversationId.value}`)
  }
  if (typingTimer.value) {
    clearTimeout(typingTimer.value)
  }
})
</script>
