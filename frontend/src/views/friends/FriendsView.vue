<template>
  <BaseLayout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $t('friends.title') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">{{ $t('friends.subtitle') }}</p>
          </div>
          <div class="flex gap-2 flex-wrap">
            <router-link
              to="/chat"
              class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
              {{ $t('friends.backToConversations') }}
            </router-link>
            <router-link
              to="/community"
              class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
              {{ $t('community.title') }}
            </router-link>
            <router-link
              to="/profile"
              class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
              {{ $t('profile.title') }}
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

        <div v-if="friendsStore.loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
          <p class="mt-4 text-gray-600 dark:text-gray-400">Loading friends...</p>
        </div>

        <div v-else-if="!friendsStore.hasFriends" class="text-center py-12">
          <p class="text-lg text-gray-600 dark:text-gray-400 mb-2">No friends yet</p>
          <p class="text-sm text-gray-500 dark:text-gray-500">Go to Community to add friends!</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <Card
            v-for="friend in friendsStore.friends"
            :key="friend.id"
            class="hover:shadow-lg transition-all cursor-pointer border-2 border-gray-200 dark:border-gray-700 hover:border-primary-500"
            @click="handleStartChat(friend.id)"
          >
            <div class="flex items-center gap-4 mb-4">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white flex items-center justify-center font-bold text-2xl flex-shrink-0 overflow-hidden">
                  <img
                    v-if="friend.avatar"
                    :src="getAvatarUrl(friend.avatar)"
                    :alt="friend.name"
                    class="w-full h-full object-cover"
                  />
                <span v-else>{{ friend.name?.charAt(0).toUpperCase() || 'U' }}</span>
              </div>
              <div class="flex-1">
                <div class="font-semibold text-base mb-1 dark:text-gray-100 flex items-center gap-2">
                  {{ friend.name }}
                  <VerifiedBadge :show="!!friend.is_verified" size="sm" />
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">@{{ friend.username || 'no-username' }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                  <span v-if="friend.is_online" class="text-green-500">● {{ $t('friends.online') }}</span>
                  <span v-else>○ {{ $t('friends.offline') }}</span>
                </div>
              </div>
            </div>

            <div v-if="friend.bio" class="text-sm text-gray-700 dark:text-gray-300 mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
              {{ friend.bio }}
            </div>

            <Button
              class="w-full bg-green-500 hover:bg-green-600"
              @click.stop="handleStartChat(friend.id)"
            >
              {{ $t('friends.startChat') }}
            </Button>
          </Card>
        </div>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFriendsStore } from '@/stores/friends'
import { useConversationsStore } from '@/stores/conversations'
import { useAvatar } from '@/composables/useAvatar'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import VerifiedBadge from '@/components/ui/VerifiedBadge.vue'

const router = useRouter()
const authStore = useAuthStore()
const friendsStore = useFriendsStore()
const conversationsStore = useConversationsStore()
const { getAvatarUrl } = useAvatar()

const isAdmin = computed(() => {
  if (!authStore.user) return false
  const roles = authStore.user.roles
  if (Array.isArray(roles)) {
    return roles.includes('admin') || roles.some(r => (typeof r === 'string' ? r : r.name) === 'admin')
  }
  return authStore.user.has_admin_role === true
})

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

onMounted(() => {
  friendsStore.fetchFriends()
})
</script>
