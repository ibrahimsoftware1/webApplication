import { createApp, watch } from 'vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import App from './App.vue'
import router from './router'
import i18n from './i18n'
import { useAuthStore } from './stores/auth'
import { usePreferencesStore } from './stores/preferences'
import './assets/css/main.css'

const app = createApp(App)
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)

app.use(pinia)
app.use(router)
app.use(i18n)

// Initialize preferences (theme) on app start
const preferencesStore = usePreferencesStore()

// Function to apply theme to DOM
const applyTheme = (theme) => {
  const html = document.documentElement
  if (theme === 'dark') {
    html.classList.add('dark')
  } else {
    html.classList.remove('dark')
  }
  console.log('Applied theme to DOM:', theme, 'HTML has dark class:', html.classList.contains('dark'))
}

// Apply the saved theme immediately
applyTheme(preferencesStore.theme)

// Watch for theme changes (in case persistence restores it or state changes)
watch(
  () => preferencesStore.theme,
  (newTheme) => {
    console.log('Theme changed in store, applying to DOM:', newTheme)
    applyTheme(newTheme)
  },
  { immediate: false }
)

// Initialize auth on app start
const authStore = useAuthStore()
authStore.initializeAuth()

app.mount('#app')
