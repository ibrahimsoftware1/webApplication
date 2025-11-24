import { describe, it, expect, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { usePreferencesStore } from '@/stores/preferences'

describe('Preferences Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('should initialize with default values', () => {
    const store = usePreferencesStore()
    expect(store.theme).toBe('light')
    expect(store.viewMode).toBe('grid')
  })

  it('should toggle theme', () => {
    const store = usePreferencesStore()
    store.toggleTheme()
    expect(store.theme).toBe('dark')
    store.toggleTheme()
    expect(store.theme).toBe('light')
  })

  it('should set view mode', () => {
    const store = usePreferencesStore()
    store.setViewMode('list')
    expect(store.viewMode).toBe('list')
    expect(store.isGridView).toBe(false)
  })
})

