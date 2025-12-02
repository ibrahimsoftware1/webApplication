<template>
  <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
    <nav class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center">
            <router-link
              to="/"
              class="flex items-center gap-2 text-xl font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg px-2 py-1"
            >
              <!-- Chat Bubble Logo -->
              <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Main chat bubble -->
                <path d="M21 6C21 4.9 20.1 4 19 4H5C3.9 4 3 4.9 3 6V18C3 19.1 3.9 20 5 20H7L10 23L13 20H19C20.1 20 21 19.1 21 18V6Z" 
                      class="fill-primary-600 dark:fill-primary-400" 
                      stroke="currentColor" 
                      stroke-width="1.5" 
                      stroke-linecap="round" 
                      stroke-linejoin="round"/>
                <!-- Chat dots (ellipsis) -->
                <circle cx="9" cy="11" r="1.5" class="fill-white dark:fill-gray-100"/>
                <circle cx="12" cy="11" r="1.5" class="fill-white dark:fill-gray-100"/>
                <circle cx="15" cy="11" r="1.5" class="fill-white dark:fill-gray-100"/>
              </svg>
              <span>KOR</span>
            </router-link>
        </div>
        
        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-4">
            <router-link
              v-if="authStore.isAuthenticated"
              to="/chat"
              class="text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
              active-class="text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900"
            >
              {{ $t('chat.title') }}
            </router-link>
            <router-link
              v-if="authStore.isAuthenticated"
              to="/friends"
              class="text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
              active-class="text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900"
            >
              {{ $t('friends.title') }}
            </router-link>
            <router-link
              v-if="authStore.isAuthenticated"
              to="/community"
              class="text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
              active-class="text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900"
            >
              {{ $t('community.title') }}
            </router-link>
        </div>
        
        <!-- Right Side Actions -->
        <div class="flex items-center space-x-2 md:space-x-4">
          <!-- Language Switcher -->
          <div class="relative">
            <button
              type="button"
              class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg px-3 py-2 transition-colors cursor-pointer text-sm font-medium"
              @click.stop="showLanguageMenu = !showLanguageMenu"
            >
              {{ currentLanguage.toUpperCase() }}
            </button>
            <div
              v-if="showLanguageMenu"
              v-click-outside="() => showLanguageMenu = false"
              class="absolute right-0 mt-2 w-32 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50"
            >
              <button
                v-for="lang in availableLanguages"
                :key="lang.code"
                @click="changeLanguage(lang.code)"
                :class="[
                  'w-full text-left px-4 py-2 text-sm transition-colors',
                  currentLanguage === lang.code
                    ? 'bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400 font-semibold'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
              >
                {{ lang.name }}
              </button>
            </div>
          </div>
          
          <!-- Theme Toggle -->
          <button
            type="button"
            class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg p-2 transition-colors cursor-pointer"
            :aria-label="preferencesStore.isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
            @click.stop="handleToggleTheme"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                v-if="preferencesStore.isDarkMode"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
              />
              <path
                v-else
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
              />
            </svg>
          </button>
          
          <!-- User Menu (Desktop) -->
          <div v-if="authStore.isAuthenticated" class="hidden md:flex items-center space-x-2">
            <router-link
              to="/subscribe"
              class="text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              ✨ Get Verified
            </router-link>
            <router-link
              to="/profile"
              class="text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              {{ $t('auth.profile') }}
            </router-link>
            <button
              type="button"
              class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-red-500"
              @click="handleLogout"
            >
              {{ $t('auth.logout') }}
            </button>
            <div class="relative">
              <button
                type="button"
                class="flex items-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg px-3 py-2"
                :aria-expanded="showMenu"
                :aria-haspopup="true"
                @click="showMenu = !showMenu"
              >
                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white flex items-center justify-center font-semibold text-sm overflow-hidden">
                  <img
                    v-if="authStore.user?.avatar"
                    :src="getAvatarUrl(authStore.user.avatar)"
                    :alt="authStore.userName"
                    class="w-full h-full object-cover"
                  />
                  <span v-else>
                    {{ authStore.userName?.charAt(0).toUpperCase() || 'U' }}
                  </span>
                </div>
              </button>
              
              <div
                v-if="showMenu"
                v-click-outside="() => showMenu = false"
                class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50"
                role="menu"
              >
                <router-link
                  to="/profile"
                  class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700"
                  role="menuitem"
                  @click="showMenu = false"
                >
                  {{ $t('auth.profile') }}
                </router-link>
                <button
                  type="button"
                  class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900 focus:outline-none focus:bg-red-50 dark:focus:bg-red-900 font-semibold"
                  role="menuitem"
                  @click="handleLogout"
                >
                  🚪 {{ $t('auth.logout') }}
                </button>
              </div>
            </div>
          </div>
          
          <!-- Mobile Menu Button -->
          <button
            v-if="authStore.isAuthenticated"
            type="button"
            class="md:hidden text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg p-2"
            @click="showMobileMenu = !showMobileMenu"
            aria-label="Toggle menu"
          >
            <svg v-if="!showMobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          
          <!-- Guest Links -->
          <div v-if="!authStore.isAuthenticated" class="flex items-center space-x-2">
            <router-link
              to="/login"
              class="text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              Login
            </router-link>
            <router-link
              to="/register"
              class="bg-primary-600 dark:bg-primary-500 text-white hover:bg-primary-700 dark:hover:bg-primary-600 px-4 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              Register
            </router-link>
          </div>
        </div>
      </div>
      
      <!-- Mobile Menu -->
      <div
        v-if="authStore.isAuthenticated && showMobileMenu"
        class="md:hidden border-t border-gray-200 dark:border-gray-700 py-4"
      >
        <div class="space-y-2">
          <router-link
            to="/chat"
            class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-sm font-medium"
            active-class="bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400"
            @click="showMobileMenu = false"
          >
            Chat
          </router-link>
          <router-link
            to="/friends"
            class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-sm font-medium"
            active-class="bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400"
            @click="showMobileMenu = false"
          >
            Friends
          </router-link>
          <router-link
            to="/community"
            class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-sm font-medium"
            active-class="bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400"
            @click="showMobileMenu = false"
          >
            Community
          </router-link>
          <router-link
            to="/profile"
            class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-sm font-medium"
            active-class="bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400"
            @click="showMobileMenu = false"
          >
            Profile
          </router-link>
          <button
            type="button"
            class="w-full text-left px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900 rounded-md text-sm font-medium font-semibold"
            @click="handleLogout"
          >
            🚪 Logout
          </button>
        </div>
      </div>
      <!-- Guest Links (Mobile) -->
      <div v-else-if="!authStore.isAuthenticated && showMobileMenu" class="md:hidden border-t border-gray-200 dark:border-gray-700 py-4">
        <div class="space-y-2">
          <router-link
            to="/login"
            class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-sm font-medium"
            active-class="bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400"
            @click="showMobileMenu = false"
          >
            Login
          </router-link>
          <router-link
            to="/register"
            class="block px-4 py-2 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900 hover:bg-primary-100 dark:hover:bg-primary-800 rounded-md text-sm font-medium"
            active-class="bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400"
            @click="showMobileMenu = false"
          >
            Register
          </router-link>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { usePreferencesStore } from '@/stores/preferences'
import { useAvatar } from '@/composables/useAvatar'

const router = useRouter()
const { locale, t } = useI18n()
const authStore = useAuthStore()
const preferencesStore = usePreferencesStore()
const { getAvatarUrl } = useAvatar()
const showMenu = ref(false)
const showMobileMenu = ref(false)
const showLanguageMenu = ref(false)

const availableLanguages = [
  { code: 'en', name: 'English' },
  { code: 'ar', name: 'العربية' },
  { code: 'ku', name: 'کوردی' }
]

const currentLanguage = computed(() => locale.value)

const changeLanguage = (langCode) => {
  locale.value = langCode
  localStorage.setItem('locale', langCode)
  showLanguageMenu.value = false
  // Update HTML dir attribute for RTL languages
  if (langCode === 'ar' || langCode === 'ku') {
    document.documentElement.setAttribute('dir', 'rtl')
    document.documentElement.setAttribute('lang', langCode)
  } else {
    document.documentElement.setAttribute('dir', 'ltr')
    document.documentElement.setAttribute('lang', 'en')
  }
}

const handleToggleTheme = () => {
  try {
    const currentTheme = preferencesStore.theme
    const newTheme = currentTheme === 'light' ? 'dark' : 'light'
    console.log('🔄 Toggling theme:', currentTheme, '->', newTheme)
    
    // Call toggleTheme
    preferencesStore.toggleTheme()
    
    // Wait for next tick and verify
    nextTick(() => {
      const updatedTheme = preferencesStore.theme
      const htmlHasDark = document.documentElement.classList.contains('dark')
      console.log('✅ Theme state updated to:', updatedTheme)
      console.log('✅ HTML element has dark class:', htmlHasDark)
      console.log('✅ Match?', (updatedTheme === 'dark') === htmlHasDark)
      
      // Force a repaint if needed
      if ((updatedTheme === 'dark') !== htmlHasDark) {
        console.warn('⚠️ Mismatch detected! Fixing...')
        if (updatedTheme === 'dark') {
          document.documentElement.classList.add('dark')
        } else {
          document.documentElement.classList.remove('dark')
        }
      }
    })
  } catch (error) {
    console.error('❌ Error toggling theme:', error)
  }
}

const handleLogout = async () => {
  showMenu.value = false
  showMobileMenu.value = false
  try {
    await authStore.logout()
    // Force redirect to login
    router.push('/login').then(() => {
      // Force page reload to clear any cached state
      window.location.reload()
    })
  } catch (error) {
    console.error('Logout error:', error)
    // Still redirect even if logout fails
    router.push('/login')
  }
}

// Click outside directive
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}

onMounted(() => {
  preferencesStore.setTheme(preferencesStore.theme)
  // Set initial language direction
  if (locale.value === 'ar' || locale.value === 'ku') {
    document.documentElement.setAttribute('dir', 'rtl')
    document.documentElement.setAttribute('lang', locale.value)
  } else {
    document.documentElement.setAttribute('dir', 'ltr')
    document.documentElement.setAttribute('lang', 'en')
  }
})
</script>
