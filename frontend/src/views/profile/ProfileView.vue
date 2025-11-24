<template>
  <BaseLayout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">👤 My Profile</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">Edit your personal information</p>
          </div>
          <div class="flex gap-2">
            <router-link
              to="/chat"
              class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors"
            >
              ← Back to Conversations
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

        <Card>
          <div v-if="loading" class="text-center py-10">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
          </div>

          <!-- View Mode (Read-only) -->
          <div v-else-if="!editing && authStore.user">
            <!-- Avatar Section -->
            <div class="text-center mb-8">
              <div class="w-32 h-32 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white inline-flex items-center justify-center font-bold text-5xl border-4 border-white shadow-lg overflow-hidden mx-auto">
                <img
                  v-if="authStore.user.avatar"
                  :src="getAvatarUrl(authStore.user.avatar)"
                  :alt="authStore.user.name"
                  class="w-full h-full object-cover"
                />
                <span v-else>{{ authStore.user.name?.charAt(0).toUpperCase() || 'U' }}</span>
              </div>
              <div class="mt-4">
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">{{ authStore.user.name }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">@{{ authStore.user.username || 'no-username' }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  <span v-if="authStore.user.is_online" class="text-green-500">● Online</span>
                  <span v-else>○ Offline</span>
                </div>
              </div>
            </div>

            <!-- Profile Details -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5 mb-6">
              <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ $t('profile.profileInformation') }}</h3>
              
              <div class="mb-4">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Email</div>
                <div class="text-sm text-gray-900 dark:text-gray-100">{{ authStore.user.email }}</div>
              </div>

              <div v-if="authStore.user.gender" class="mb-4">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Gender</div>
                <div class="text-sm text-gray-900 dark:text-gray-100">
                  {{ authStore.user.gender === 'male' ? '♂ Male' : '♀ Female' }}
                </div>
              </div>

              <div v-if="authStore.user.bio">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Bio</div>
                <div class="text-sm text-gray-900 dark:text-gray-100 p-3 bg-white dark:bg-gray-700 rounded-lg leading-relaxed">
                  {{ authStore.user.bio }}
                </div>
              </div>
            </div>

            <!-- Edit Button -->
            <div class="text-center">
              <Button @click="startEditing" size="lg">
                ✏️ {{ $t('profile.editProfile') }}
              </Button>
            </div>
          </div>

          <!-- Edit Mode -->
          <div v-else>
            <!-- Avatar Section -->
            <div class="text-center mb-8">
              <div class="relative inline-block">
                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white inline-flex items-center justify-center font-bold text-5xl border-4 border-white shadow-lg overflow-hidden">
                  <img
                    v-if="avatarPreview || authStore.user?.avatar"
                    :src="avatarPreview || getAvatarUrl(authStore.user?.avatar)"
                    :alt="form.name || authStore.user?.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else>{{ (form.name || authStore.user?.name || 'U').charAt(0).toUpperCase() }}</span>
                </div>
                <label class="absolute bottom-0 right-0 bg-green-500 text-white rounded-full w-9 h-9 flex items-center justify-center cursor-pointer shadow-lg hover:bg-green-600 transition-colors">
                  <input
                    ref="avatarInput"
                    type="file"
                    @change="handleAvatarChange"
                    accept="image/*"
                    class="hidden"
                  />
                  ✎
                </label>
              </div>
              <div class="mt-2">
                <Button
                  variant="secondary"
                  size="sm"
                  @click="handleRemoveAvatar"
                >
                  Remove Avatar
                </Button>
              </div>
            </div>

            <!-- Profile Form -->
            <form @submit.prevent="handleUpdateProfile" class="space-y-5">
              <div>
                <label class="block font-semibold mb-2 text-gray-900 dark:text-gray-100">Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                  placeholder="Your name"
                  required
                />
              </div>

              <div>
                <label class="block font-semibold mb-2 text-gray-900 dark:text-gray-100">Username</label>
                <input
                  v-model="form.username"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                  placeholder="Your username"
                />
              </div>

              <div>
                <label class="block font-semibold mb-2 text-gray-900 dark:text-gray-100">Bio</label>
                <textarea
                  v-model="form.bio"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-y bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                  placeholder="Tell us about yourself..."
                  rows="4"
                ></textarea>
              </div>

              <div>
                <label class="block font-semibold mb-2 text-gray-900 dark:text-gray-100">Gender</label>
                <select
                  v-model="form.gender"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                >
                  <option value="">Select gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
              </div>

              <div class="flex gap-3">
                <Button
                  type="submit"
                  :loading="updating"
                  class="flex-1"
                >
                  💾 Save Changes
                </Button>
                <Button
                  type="button"
                  variant="secondary"
                  @click="cancelEditing"
                >
                  Cancel
                </Button>
              </div>
            </form>
          </div>
        </Card>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { authService } from '@/services/auth'
import { useToast } from '@/composables/useToast'
import { useAvatar } from '@/composables/useAvatar'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'

const authStore = useAuthStore()
const { getAvatarUrl } = useAvatar()
const loading = ref(false)
const editing = ref(false)
const updating = ref(false)
const avatarInput = ref(null)
const avatarPreview = ref(null)
const avatarFile = ref(null)

const form = reactive({
  name: '',
  username: '',
  bio: '',
  gender: ''
})

const isAdmin = computed(() => {
  if (!authStore.user) return false
  const roles = authStore.user.roles
  if (Array.isArray(roles)) {
    return roles.includes('admin') || roles.some(r => (typeof r === 'string' ? r : r.name) === 'admin')
  }
  return authStore.user.has_admin_role === true
})

const startEditing = () => {
  if (authStore.user) {
    form.name = authStore.user.name || ''
    form.username = authStore.user.username || ''
    form.bio = authStore.user.bio || ''
    form.gender = authStore.user.gender || ''
    avatarPreview.value = authStore.user.avatar || null
    avatarFile.value = null
    editing.value = true
  }
}

const cancelEditing = () => {
  editing.value = false
  avatarPreview.value = null
  avatarFile.value = null
  if (avatarInput.value) {
    avatarInput.value.value = ''
  }
}

const handleAvatarChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 2048 * 1024) {
      useToast().error('Avatar size must be less than 2MB')
      return
    }
    avatarFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const handleRemoveAvatar = async () => {
  try {
    await authService.removeAvatar()
    avatarPreview.value = null
    avatarFile.value = null
    if (authStore.user) {
      authStore.user.avatar = null
    }
    useToast().success('Avatar removed successfully')
  } catch (error) {
    useToast().error('Failed to remove avatar')
  }
}

const handleUpdateProfile = async () => {
  try {
    updating.value = true

    // Update profile info
    await authService.updateProfile({
      name: form.name,
      username: form.username,
      bio: form.bio,
      gender: form.gender
    })

    // Update avatar if changed
    if (avatarFile.value) {
      const formData = new FormData()
      formData.append('avatar', avatarFile.value)
      const avatarResponse = await authService.updateAvatar(formData)
      const avatarUrl = avatarResponse.data.data?.avatar_url || avatarResponse.data.data?.avatar
      if (avatarUrl && authStore.user) {
        authStore.user.avatar = avatarUrl
        avatarPreview.value = avatarUrl
      }
    }

    // Reload profile to get latest data
    await authStore.fetchProfile()

    editing.value = false
    avatarFile.value = null
    if (avatarInput.value) {
      avatarInput.value.value = ''
    }

    useToast().success('Profile updated successfully!')
  } catch (error) {
    useToast().error(error.response?.data?.message || 'Failed to update profile')
  } finally {
    updating.value = false
  }
}

onMounted(() => {
  if (!authStore.user) {
    loading.value = true
    authStore.fetchProfile().finally(() => {
      loading.value = false
    })
  }
})
</script>
