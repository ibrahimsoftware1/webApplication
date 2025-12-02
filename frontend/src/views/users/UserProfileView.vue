<template>
  <BaseLayout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="max-w-4xl mx-auto">
        <div v-if="usersStore.loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        </div>
        
        <div v-else-if="usersStore.currentUser">
          <Card>
            <div class="text-center mb-8">
              <div class="h-24 w-24 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white flex items-center justify-center mx-auto mb-4 overflow-hidden">
                <img
                  v-if="usersStore.currentUser.avatar"
                  :src="getAvatarUrl(usersStore.currentUser.avatar)"
                  :alt="usersStore.currentUser.name"
                  class="w-full h-full object-cover"
                />
                <span v-else class="text-white font-semibold text-3xl">
                  {{ usersStore.currentUser.name?.charAt(0).toUpperCase() || 'U' }}
                </span>
              </div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2 flex items-center justify-center gap-2">
                {{ usersStore.currentUser.name }}
                <VerifiedBadge :show="!!usersStore.currentUser.is_verified" size="lg" />
              </h1>
              <p class="text-gray-600 dark:text-gray-400 mb-4">{{ usersStore.currentUser.username || usersStore.currentUser.email }}</p>
              <p v-if="usersStore.currentUser.bio" class="text-gray-700 dark:text-gray-300 mb-4">
                {{ usersStore.currentUser.bio }}
              </p>
              <div class="flex justify-center space-x-4">
                <Button
                  v-if="usersStore.currentUser.id !== authStore.user?.id"
                  variant="primary"
                  @click="handleSendFriendRequest"
                >
                  {{ $t('community.addFriend') }}
                </Button>
                <Button
                  v-if="usersStore.currentUser.id !== authStore.user?.id"
                  variant="secondary"
                  @click="handleStartConversation"
                >
                  {{ $t('friends.startChat') }}
                </Button>
              </div>
            </div>
          </Card>
        </div>
        
        <div v-else class="card text-center py-12">
          <p class="text-gray-600 dark:text-gray-400">User not found</p>
        </div>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUsersStore } from '@/stores/users'
import { useAuthStore } from '@/stores/auth'
import { useFriendsStore } from '@/stores/friends'
import { useConversationsStore } from '@/stores/conversations'
import { useAvatar } from '@/composables/useAvatar'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import VerifiedBadge from '@/components/ui/VerifiedBadge.vue'

const route = useRoute()
const router = useRouter()
const usersStore = useUsersStore()
const authStore = useAuthStore()
const friendsStore = useFriendsStore()
const conversationsStore = useConversationsStore()
const { getAvatarUrl } = useAvatar()

const handleSendFriendRequest = async () => {
  await friendsStore.sendRequest({ friend_id: route.params.id })
}

const handleStartConversation = async () => {
  try {
    // Create or find conversation with this user
    const response = await conversationsStore.createConversation({
      participant_ids: [route.params.id],
      type: 'private'
    })
    router.push(`/chat/${response.data.data?.id || response.data.id}`)
  } catch (error) {
    // If conversation already exists, find it
    await conversationsStore.fetchConversations()
    const conversation = conversationsStore.conversations.find(c => 
      c.users?.some(u => u.id === parseInt(route.params.id))
    )
    if (conversation) {
      router.push(`/chat/${conversation.id}`)
    }
  }
}

onMounted(async () => {
  await usersStore.fetchUser(route.params.id)
})
</script>
