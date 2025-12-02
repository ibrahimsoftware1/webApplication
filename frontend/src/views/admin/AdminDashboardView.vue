<template>
  <BaseLayout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $t('admin.title') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">{{ $t('admin.manageUsers') }}</p>
          </div>
          <div class="flex gap-2">
            <router-link
              to="/chat"
              class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
              {{ $t('admin.backToConversations') }}
            </router-link>
          </div>
        </div>

        <!-- Stats Cards -->
        <div v-if="adminStore.stats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
          <Card class="bg-gradient-to-br from-primary-600 to-purple-600 text-white">
            <div class="text-4xl font-bold mb-2">{{ adminStore.stats['Total Users'] || 0 }}</div>
            <div class="text-sm opacity-90">{{ $t('admin.stats.totalUsers') }}</div>
          </Card>
          <Card class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white">
            <div class="text-4xl font-bold mb-2">{{ adminStore.stats['Total Conversations'] || 0 }}</div>
            <div class="text-sm opacity-90">{{ $t('admin.stats.totalConversations') }}</div>
          </Card>
          <Card class="bg-gradient-to-br from-red-500 to-red-600 text-white">
            <div class="text-4xl font-bold mb-2">{{ adminStore.stats['Total Mesages'] || adminStore.stats['Total Messages'] || 0 }}</div>
            <div class="text-sm opacity-90">{{ $t('admin.stats.totalMessages') }}</div>
          </Card>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search users by name or email..."
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
            @input="handleSearch"
          />
        </div>

        <!-- Users List -->
        <Card>
          <h2 class="text-xl font-semibold mb-4 dark:text-gray-100">{{ $t('admin.users.title') }}</h2>
          
          <div v-if="adminStore.loading" class="text-center py-10">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
          </div>

          <div v-else-if="adminStore.users.length === 0" class="text-center py-10 text-gray-600 dark:text-gray-400">
            No users found
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="user in adminStore.users"
              :key="user.id"
              class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600 transition-all hover:bg-gray-100 dark:hover:bg-gray-600 hover:border-primary-500 flex items-center gap-4"
            >
              <div
                @click="showUserProfile(user.id)"
                class="flex items-center gap-4 flex-1 cursor-pointer"
              >
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white flex items-center justify-center font-bold text-lg flex-shrink-0 overflow-hidden">
                  <img
                    v-if="user.avatar"
                    :src="getAvatarUrl(user.avatar)"
                    :alt="user.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else>{{ user.name?.charAt(0).toUpperCase() || 'U' }}</span>
                </div>
                <div class="flex-1 font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                  {{ user.name }}
                  <VerifiedBadge :show="!!user.is_verified" size="sm" />
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                  <span v-if="user.is_online" class="text-green-500">● Online</span>
                  <span v-else>○ {{ $t('chat.offline') }}</span>
                </div>
              </div>
              <div class="flex gap-2" @click.stop>
                <!-- Don't show delete button for current admin user -->
                <button
                  v-if="user.id !== authStore.user?.id"
                  @click="handleDeleteUser(user.id)"
                  class="px-3 py-1 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600 transition-colors"
                  :disabled="adminStore.loading"
                >
                  {{ $t('admin.users.delete') }}
                </button>
                <span
                  v-else
                  class="px-3 py-1 text-gray-500 dark:text-gray-400 text-sm"
                >
                  (You)
                </span>
              </div>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '@/stores/admin'
import { useAuthStore } from '@/stores/auth'
import { useAvatar } from '@/composables/useAvatar'
import { useToast } from '@/composables/useToast'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'
import VerifiedBadge from '@/components/ui/VerifiedBadge.vue'

const router = useRouter()
const adminStore = useAdminStore()
const authStore = useAuthStore()
const { getAvatarUrl } = useAvatar()
const searchQuery = ref('')
const searchTimeout = ref(null)

const handleSearch = () => {
  clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    adminStore.fetchUsers({ search: searchQuery.value })
  }, 500)
}

const showUserProfile = (userId) => {
  router.push(`/users/${userId}`)
}

const handleDeleteUser = async (userId) => {
  if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
    return
  }
  
  try {
    await adminStore.deleteUser(userId)
    // Reload users list
    await adminStore.fetchUsers({ search: searchQuery.value })
    // Reload dashboard stats
    await adminStore.fetchDashboard()
  } catch (error) {
    console.error('Failed to delete user:', error)
  }
}

onMounted(() => {
  adminStore.fetchDashboard()
  adminStore.fetchUsers()
})
</script>

