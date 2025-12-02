import { defineStore } from 'pinia'

export const usePreferencesStore = defineStore('preferences', {
  state: () => ({
    theme: 'light',
    viewMode: 'grid', // grid or list
    itemsPerPage: 10
  }),

  getters: {
    isDarkMode: (state) => state.theme === 'dark',
    isGridView: (state) => state.viewMode === 'grid'
  },

  actions: {
    setTheme(theme) {
      console.log('setTheme called with:', theme, 'current theme:', this.theme)
      
      // CRITICAL: Apply to DOM FIRST, before updating state
      // This ensures the class is applied synchronously
      if (typeof document !== 'undefined') {
        const html = document.documentElement
        
        if (theme === 'dark') {
          html.classList.add('dark')
          console.log('✅ Added dark class to <html>')
        } else {
          html.classList.remove('dark')
          console.log('✅ Removed dark class from <html>')
        }
        
        // Verify immediately
        const hasDark = html.classList.contains('dark')
        console.log('Verification - html has dark class?', hasDark, 'Expected:', theme === 'dark')
        console.log('All html classes:', html.className)
      }
      
      // THEN update the state (this might trigger persistence)
      this.theme = theme
    },
    
    toggleTheme() {
      const currentTheme = this.theme
      const newTheme = currentTheme === 'light' ? 'dark' : 'light'
      console.log('toggleTheme called - current:', currentTheme, 'new:', newTheme)
      this.setTheme(newTheme)
    },
    
    setViewMode(mode) {
      this.viewMode = mode
    },
    
    setItemsPerPage(count) {
      this.itemsPerPage = count
    }
  },

  persist: {
    key: 'preferences',
    paths: ['theme', 'viewMode', 'itemsPerPage']
  }
})

