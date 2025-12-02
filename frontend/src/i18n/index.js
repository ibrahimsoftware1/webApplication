import { createI18n } from 'vue-i18n'
import en from './locales/en.json'
import ar from './locales/ar.json'
import ku from './locales/ku.json'

const messages = {
  en,
  ar,
  ku
}

// Get saved language from localStorage or default to 'en'
const savedLocale = localStorage.getItem('locale') || 'en'

const i18n = createI18n({
  legacy: false, // Use Composition API mode
  locale: savedLocale,
  fallbackLocale: 'en',
  messages,
  globalInjection: true // Enable $t in templates
})

export default i18n

