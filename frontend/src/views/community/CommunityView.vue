<template>
  <BaseLayout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $t('community.title') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">
              <span v-if="activePage === 'chat'">{{ $t('community.chat.chatWithCommunity') }}</span>
              <span v-else>{{ $t('community.users.subtitle') }}</span>
            </p>
          </div>
          <div class="flex gap-2 flex-wrap">
            <button
              @click="activePage = 'chat'"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                activePage === 'chat' ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
              ]"
            >
              {{ $t('community.chat.title') }}
            </button>
            <button
              @click="activePage = 'users'"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                activePage === 'users' ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
              ]"
            >
              {{ $t('community.users.title') }}
            </button>
            <button
              @click="showFriendRequests = !showFriendRequests"
              class="relative px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
              {{ $t('community.friendRequests.title') }}
              <span
                v-if="pendingRequestsCount > 0"
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"
              >
                {{ pendingRequestsCount }}
              </span>
            </button>
            <router-link
              to="/friends"
              class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors"
            >
              {{ $t('community.myFriends') }}
            </router-link>
            <router-link
              to="/chat"
              class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
              {{ $t('community.backToConversations') }}
            </router-link>
            <router-link
              to="/profile"
              class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
              {{ $t('community.myProfile') }}
            </router-link>
            <router-link
              v-if="isAdmin"
              to="/admin"
              class="px-3 py-1 rounded-lg text-xs font-semibold bg-red-500 text-white border-2 border-red-600"
            >
              ⚙️ {{ $t('admin.admin') }}
            </router-link>
          </div>
        </div>

        <!-- Friend Requests Panel -->
        <Card v-if="showFriendRequests" class="mb-6">
          <h3 class="text-lg font-semibold mb-4 dark:text-gray-100">{{ $t('community.friendRequests.title') }}</h3>
          
          <!-- Received Requests -->
          <div v-if="friendsStore.requests.received?.length > 0" class="mb-6">
            <h4 class="text-sm text-gray-600 dark:text-gray-400 mb-2">Received ({{ friendsStore.requests.received.length }})</h4>
            <div
              v-for="request in friendsStore.requests.received"
              :key="request.id"
              class="bg-white dark:bg-gray-700 p-4 rounded-lg mb-2 flex justify-between items-center"
            >
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white flex items-center justify-center font-bold overflow-hidden">
                  <img
                    v-if="request.user?.avatar"
                    :src="getAvatarUrl(request.user.avatar)"
                    :alt="request.user?.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else>
                    {{ request.user?.name?.charAt(0).toUpperCase() || 'U' }}
                  </span>
                </div>
                <div>
                  <div class="font-semibold dark:text-gray-100">{{ request.user?.name }}</div>
                  <div class="text-xs text-gray-600 dark:text-gray-400">@{{ request.user?.username || 'no-username' }}</div>
                </div>
              </div>
              <div class="flex gap-2">
                <Button size="sm" @click="handleAcceptRequest(request.id)">Accept</Button>
                <Button size="sm" variant="secondary" @click="handleRejectRequest(request.id)">Reject</Button>
              </div>
            </div>
          </div>

          <!-- Sent Requests -->
          <div v-if="friendsStore.requests.sent?.length > 0">
            <h4 class="text-sm text-gray-600 dark:text-gray-400 mb-2">Sent ({{ friendsStore.requests.sent.length }})</h4>
            <div
              v-for="request in friendsStore.requests.sent"
              :key="request.id"
              class="bg-white dark:bg-gray-700 p-4 rounded-lg mb-2 flex justify-between items-center"
            >
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white flex items-center justify-center font-bold overflow-hidden">
                  <img
                    v-if="request.user?.avatar"
                    :src="getAvatarUrl(request.user.avatar)"
                    :alt="request.user?.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else>
                    {{ request.user?.name?.charAt(0).toUpperCase() || 'U' }}
                  </span>
                </div>
                <div>
                  <div class="font-semibold dark:text-gray-100">{{ request.user?.name }}</div>
                  <div class="text-xs text-gray-600 dark:text-gray-400">@{{ request.user?.username || 'no-username' }}</div>
                </div>
              </div>
              <Button size="sm" variant="secondary" @click="handleCancelRequest(request.user?.id || request.friend?.id)">
                Cancel
              </Button>
            </div>
          </div>

          <div
            v-if="(!friendsStore.requests.received || friendsStore.requests.received.length === 0) && (!friendsStore.requests.sent || friendsStore.requests.sent.length === 0)"
            class="text-center py-10 text-gray-600 dark:text-gray-400"
          >
            No friend requests
          </div>
        </Card>

        <!-- Community Chat Page -->
        <Card v-if="activePage === 'chat'">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold dark:text-gray-100">{{ $t('community.chat.title') }}</h2>
            <div v-if="isAdmin" class="flex gap-2 items-center">
              <button
                v-if="!deleteMode"
                @click="deleteMode = true"
                class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600"
              >
                🗑️ {{ $t('community.chat.deleteMessages') }}
              </button>
              <div v-else class="flex gap-2 items-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ selectedMessages.length }} selected</span>
                <button
                  @click="handleDeleteSelected"
                  :disabled="selectedMessages.length === 0"
                  class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  🗑️ {{ $t('chat.deleteSelected') }} ({{ selectedMessages.length }})
                </button>
                <button
                  @click="deleteMode = false; selectedMessages = []"
                  class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>

          <!-- Messages Area -->
          <div
            ref="messagesContainer"
            class="h-[500px] overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4 bg-gray-50 dark:bg-gray-800"
          >
            <div v-if="communityMessages.length === 0" class="text-center py-10 text-gray-600 dark:text-gray-400">
              No messages yet. Start the conversation!
            </div>
            <div
              v-for="message in communityMessages"
              :key="message.id"
              class="mb-4 flex gap-2"
              :class="message.user_id === authStore.user?.id ? 'justify-end' : 'justify-start'"
            >
              <!-- Checkbox for delete mode -->
              <div v-if="isAdmin && deleteMode" class="flex items-center flex-shrink-0">
                <input
                  type="checkbox"
                  :value="message.id"
                  v-model="selectedMessages"
                  class="w-5 h-5 cursor-pointer"
                />
              </div>
              <div
                class="flex gap-2 max-w-[80%]"
                :class="message.user_id === authStore.user?.id ? 'flex-row-reverse' : ''"
              >
                <!-- Avatar (only for other users) -->
                <div
                  v-if="message.user_id !== authStore.user?.id"
                  @click="showUserProfile(message.user?.id || message.user_id)"
                  class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white flex items-center justify-center font-bold cursor-pointer flex-shrink-0"
                >
                  <img
                    v-if="message.user?.avatar"
                    :src="getAvatarUrl(message.user.avatar)"
                    :alt="message.user.name"
                    class="w-full h-full rounded-full object-cover"
                  />
                  <span v-else>{{ (message.user?.name || 'U').charAt(0).toUpperCase() }}</span>
                </div>
                <div class="flex-1">
                  <!-- Sender name and time (only for other users) -->
                  <div
                    v-if="message.user_id !== authStore.user?.id"
                    class="flex items-center gap-2 mb-1"
                  >
                    <span class="font-semibold text-sm text-gray-700">{{ message.user?.name || 'User' }}</span>
                    <span class="text-xs text-gray-400">{{ formatMessageTime(message.created_at) }}</span>
                  </div>
                  <!-- Time only for own messages -->
                  <div
                    v-if="message.user_id === authStore.user?.id"
                    class="flex items-center justify-end gap-2 mb-1"
                  >
                    <span class="text-xs text-gray-400">{{ formatMessageTime(message.created_at) }}</span>
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

          <!-- Message Input -->
          <div class="flex gap-2 items-center">
            <label
              v-if="isAdmin"
              class="cursor-pointer p-2 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center"
            >
              <input
                ref="imageInput"
                type="file"
                @change="handleImageUpload"
                accept="image/*"
                class="hidden"
              />
              📷
            </label>
            <input
              v-model="newMessage"
              @keyup.enter="handleSendMessage"
              type="text"
              :placeholder="$t('chat.typeMessage')"
              class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
            />
            <Button @click="handleSendMessage" :disabled="!newMessage.trim() && !imageFile">
              {{ $t('chat.send') }}
            </Button>
          </div>
          <!-- Image Preview -->
          <div v-if="imagePreview" class="mt-2 relative inline-block">
            <img
              :src="imagePreview"
              alt="Preview"
              class="max-w-[200px] max-h-[200px] rounded-lg border-2 border-gray-200"
            />
            <button
              @click="imageFile = null; imagePreview = null"
              class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm cursor-pointer hover:bg-red-600"
            >
              ✕
            </button>
          </div>
        </Card>

        <!-- Community Users Page -->
        <div v-if="activePage === 'users'">
          <!-- Search Bar -->
          <div class="mb-6">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search users by name, username, or email..."
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
              @input="handleSearch"
            />
          </div>

          <!-- Users Grid -->
          <div v-if="usersStore.loading" class="text-center py-10">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
          </div>
          <div v-else-if="usersStore.users.length === 0" class="text-center py-10 text-gray-600 dark:text-gray-400">
            No users found
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <Card
              v-for="user in usersStore.users"
              :key="user.id"
              class="hover:shadow-lg transition-shadow cursor-pointer"
            >
              <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white flex items-center justify-center font-bold text-2xl flex-shrink-0 overflow-hidden">
                  <img
                    v-if="user.avatar"
                    :src="getAvatarUrl(user.avatar)"
                    :alt="user.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else>{{ user.name?.charAt(0).toUpperCase() || 'U' }}</span>
                </div>
                <div class="flex-1">
                  <div class="font-semibold text-base mb-1 dark:text-gray-100">
                    {{ user.name }}
                    <span
                      v-if="user.roles && (Array.isArray(user.roles) ? user.roles.includes('admin') : user.roles.some(r => (typeof r === 'string' ? r : r.name) === 'admin'))"
                      class="bg-red-500 text-white px-2 py-0.5 rounded text-xs ml-1"
                    >
                      ADMIN
                    </span>
                    <span
                      v-else-if="user.roles && (Array.isArray(user.roles) ? user.roles.includes('moderator') : user.roles.some(r => (typeof r === 'string' ? r : r.name) === 'moderator'))"
                      class="bg-yellow-500 text-white px-2 py-0.5 rounded text-xs ml-1"
                    >
                      MOD
                    </span>
                  </div>
                  <div class="text-xs text-gray-600 dark:text-gray-400">@{{ user.username || 'no-username' }}</div>
                  <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    {{ user.gender === 'male' ? '♂' : '♀' }} {{ user.gender || 'Not specified' }}
                  </div>
                </div>
              </div>

              <div v-if="user.bio" class="text-sm text-gray-700 dark:text-gray-300 mb-4 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                {{ user.bio }}
              </div>

              <div class="flex gap-2 flex-wrap">
                <Button
                  v-if="!user.friendship_status || user.friendship_status === null"
                  @click="handleSendFriendRequest(user.id)"
                  size="sm"
                  class="flex-1"
                >
                  ➕ {{ $t('community.addFriend') }}
                </Button>
                <Button
                  v-else-if="user.friendship_status === 'request_sent'"
                  @click="handleCancelRequest(user.id)"
                  size="sm"
                  variant="secondary"
                  class="flex-1"
                >
                  ⏳ Request Sent
                </Button>
                <Button
                  v-else-if="user.friendship_status === 'request_received'"
                  @click="handleAcceptRequestByUserId(user.id)"
                  size="sm"
                  class="flex-1"
                >
                  ✓ Accept Request
                </Button>
                <Button
                  v-else-if="user.friendship_status === 'friends'"
                  @click="handleStartChat(user.id)"
                  size="sm"
                  class="flex-1 bg-green-500 hover:bg-green-600"
                >
                  💬 {{ $t('profile.chat') }}
                </Button>
                <Button
                  v-if="user.friendship_status === 'friends'"
                  @click="handleRemoveFriend(user.id)"
                  size="sm"
                  variant="secondary"
                >
                  {{ $t('community.remove') }}
                </Button>
              </div>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFriendsStore } from '@/stores/friends'
import { useUsersStore } from '@/stores/users'
import { useConversationsStore } from '@/stores/conversations'
import { useMessagesStore } from '@/stores/messages'
import { useAvatar } from '@/composables/useAvatar'
import { useEcho } from '@/composables/useEcho'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'

const router = useRouter()
const authStore = useAuthStore()
const friendsStore = useFriendsStore()
const usersStore = useUsersStore()
const conversationsStore = useConversationsStore()
const messagesStore = useMessagesStore()
const { getAvatarUrl } = useAvatar()

const activePage = ref('chat')
const showFriendRequests = ref(false)
const deleteMode = ref(false)
const selectedMessages = ref([])
const newMessage = ref('')
const imageFile = ref(null)
const imagePreview = ref(null)
const imageInput = ref(null)
const messagesContainer = ref(null)
const searchQuery = ref('')
const searchTimeout = ref(null)

const communityChatId = ref(null)
const communityMessages = ref([])
const echo = ref(null)
const communityChannel = ref(null)

const isAdmin = computed(() => {
  if (!authStore.user) return false
  const roles = authStore.user.roles
  if (Array.isArray(roles)) {
    return roles.includes('admin') || roles.some(r => (typeof r === 'string' ? r : r.name) === 'admin')
  }
  return authStore.user.has_admin_role === true
})

const pendingRequestsCount = computed(() => {
  return friendsStore.requests.received?.length || 0
})

const formatMessageTime = (timestamp) => {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 1) return 'Just now'
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  if (days < 7) return `${days}d ago`
  
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  })
}

const connectToCommunityChannel = async () => {
  if (!echo.value || !communityChatId.value) return

  try {
    // Leave previous channel if exists
    if (communityChannel.value) {
      echo.value.leave(`conversation.${communityChatId.value}`)
    }

    communityChannel.value = echo.value.private(`conversation.${communityChatId.value}`)
      .subscribed(() => {
        console.log('✅ Successfully subscribed to community chat channel')
      })
      .listen('.message.sent', (event) => {
        console.log('📨 Community message received:', event)
        if (event.message) {
          // Only add message if it's from another user (not the current user)
          // This prevents duplicates when the current user sends a message
          if (event.message.user_id !== authStore.user?.id) {
            messagesStore.addMessage(communityChatId.value, event.message)
          }
        }
      })
      .error((error) => {
        console.error('❌ Community chat channel error:', error)
      })

    console.log('✅ Community channel listeners attached')
  } catch (error) {
    console.error('❌ Failed to subscribe to community chat channel:', error)
  }
}

const loadCommunityChat = async () => {
  try {
    console.log('Loading community chat...')
    const response = await conversationsStore.fetchCommunityChat()
    const chat = response.data.data || response.data
    if (!chat || !chat.id) {
      console.error('Invalid community chat response:', chat)
      return
    }
    
    communityChatId.value = chat.id
    console.log('Community chat ID:', communityChatId.value)
    
    // Load messages using messagesStore
    console.log('Fetching messages for chat:', communityChatId.value)
    await messagesStore.fetchMessages(communityChatId.value)
    // The watch will automatically update communityMessages.value
    // But we can also set it directly here for immediate display
    const loadedMessages = messagesStore.getMessagesByConversation(communityChatId.value)
    console.log('Loaded messages:', loadedMessages?.length || 0)
    
    if (loadedMessages && loadedMessages.length > 0) {
      communityMessages.value = [...loadedMessages]
    } else {
      communityMessages.value = []
    }
    
    // Initialize Echo for real-time if not already done
    if (!echo.value && authStore.token) {
      const echoComposable = useEcho(authStore.token)
      echo.value = echoComposable.initializeEcho()
      // Wait a bit for connection
      await new Promise(resolve => setTimeout(resolve, 1000))
    }
    
    // Connect to real-time channel
    if (echo.value) {
      await connectToCommunityChannel()
    }
    
    // Scroll to bottom
    await nextTick()
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  } catch (error) {
    console.error('Failed to load community chat:', error)
    console.error('Error details:', error.response?.data)
    // Try to load messages anyway if chat ID is available
    if (communityChatId.value) {
      try {
        await messagesStore.fetchMessages(communityChatId.value)
        const loadedMessages = messagesStore.getMessagesByConversation(communityChatId.value)
        if (loadedMessages && loadedMessages.length > 0) {
          communityMessages.value = [...loadedMessages]
        }
      } catch (msgError) {
        console.error('Failed to load messages:', msgError)
      }
    }
  }
}

const handleSendMessage = async () => {
  if (!newMessage.value.trim() && !imageFile.value) return
  if (!communityChatId.value) return

  try {
    const formData = new FormData()
    if (newMessage.value.trim()) {
      formData.append('content', newMessage.value.trim())
    } else {
      formData.append('content', '')
    }
    
    if (imageFile.value) {
      formData.append('attachments[]', imageFile.value)
      formData.append('type', 'image')
    } else {
      formData.append('type', 'text')
    }

    const response = await messagesStore.sendMessage(communityChatId.value, formData)
    const message = response.data.data || response.data
    
    // Add message to store (will be synced to local array via watch)
    // Don't add to local array directly to avoid duplicates
    messagesStore.addMessage(communityChatId.value, message)
    
    newMessage.value = ''
    imageFile.value = null
    imagePreview.value = null
    if (imageInput.value) {
      imageInput.value.value = ''
    }
    
    // Scroll to bottom
    await nextTick()
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  } catch (error) {
    console.error('Failed to send message:', error)
  }
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 10 * 1024 * 1024) {
      alert('Image size must be less than 10MB')
      return
    }
    if (!file.type.startsWith('image/')) {
      alert('Please select an image file')
      return
    }
    imageFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const handleDeleteSelected = async () => {
  if (selectedMessages.value.length === 0) return
  if (!confirm(`Are you sure you want to delete ${selectedMessages.value.length} message(s)?`)) return

  try {
    for (const messageId of selectedMessages.value) {
      await messagesStore.deleteMessage(messageId)
    }
    communityMessages.value = communityMessages.value.filter(m => !selectedMessages.value.includes(m.id))
    selectedMessages.value = []
    deleteMode.value = false
  } catch (error) {
    console.error('Failed to delete messages:', error)
  }
}

const handleSearch = () => {
  clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    usersStore.fetchUsers({ search: searchQuery.value })
  }, 500)
}

const handleAcceptRequest = async (requestId) => {
  await friendsStore.acceptRequest(requestId)
  await friendsStore.fetchRequests()
  await usersStore.fetchUsers({ search: searchQuery.value })
}

const handleRejectRequest = async (requestId) => {
  await friendsStore.rejectRequest(requestId)
  await friendsStore.fetchRequests()
}

const handleCancelRequest = async (userId) => {
  await friendsStore.removeFriend(userId)
  await friendsStore.fetchRequests()
  await usersStore.fetchUsers({ search: searchQuery.value })
}

const handleSendFriendRequest = async (userId) => {
  await friendsStore.sendRequest({ friend_id: userId })
  await usersStore.fetchUsers({ search: searchQuery.value })
}

const handleAcceptRequestByUserId = async (userId) => {
  const request = friendsStore.requests.received?.find(r => r.user?.id === userId)
  if (request) {
    await handleAcceptRequest(request.id)
  }
}

const handleRemoveFriend = async (userId) => {
  if (!confirm('Are you sure you want to remove this friend?')) return
  await friendsStore.removeFriend(userId)
  await usersStore.fetchUsers({ search: searchQuery.value })
}

const handleStartChat = async (userId) => {
  try {
    const response = await conversationsStore.createConversation({
      name: null,
      type: 'private',
      description: null,
      user_ids: [userId]
    })
    const conversation = response.data.data || response.data
    router.push(`/chat/${conversation.id}`)
  } catch (error) {
    console.error('Failed to start chat:', error)
  }
}

const showUserProfile = (userId) => {
  router.push(`/users/${userId}`)
}

// Watch for new messages in the store and update local array
watch(
  () => {
    if (communityChatId.value) {
      return messagesStore.getMessagesByConversation(communityChatId.value) || []
    }
    return []
  },
  (newMessages) => {
    if (communityChatId.value && newMessages) {
      communityMessages.value = [...newMessages]
      nextTick(() => {
        if (messagesContainer.value) {
          messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
      })
    }
  },
  { deep: true, immediate: true }
)

watch(activePage, (newPage) => {
  if (newPage === 'chat') {
    loadCommunityChat()
  } else if (newPage === 'users') {
    usersStore.fetchUsers()
  }
})

onMounted(async () => {
  friendsStore.fetchRequests()
  if (activePage.value === 'chat') {
    await loadCommunityChat()
  } else {
    usersStore.fetchUsers()
  }
  
  // Initialize Echo for real-time if authenticated
  if (authStore.isAuthenticated && authStore.token && !echo.value) {
    const echoComposable = useEcho(authStore.token)
    echo.value = echoComposable.initializeEcho()
  }
})
</script>
