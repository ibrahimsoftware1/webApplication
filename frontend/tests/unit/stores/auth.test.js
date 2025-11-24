import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { authService } from '@/services/auth'

vi.mock('@/services/auth')
vi.mock('@/composables/useToast', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn()
  })
}))

describe('Auth Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
  })

  it('should initialize with empty state', () => {
    const store = useAuthStore()
    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })

  it('should login successfully', async () => {
    const store = useAuthStore()
    const mockResponse = {
      data: {
        token: 'test-token',
        user: { id: 1, name: 'Test User', email: 'test@example.com' }
      }
    }
    
    authService.login.mockResolvedValue(mockResponse)
    
    await store.login({ email: 'test@example.com', password: 'password' })
    
    expect(store.token).toBe('test-token')
    expect(store.user).toEqual(mockResponse.data.user)
    expect(store.isAuthenticated).toBe(true)
  })

  it('should handle login failure', async () => {
    const store = useAuthStore()
    const error = { response: { data: { message: 'Invalid credentials' } } }
    
    authService.login.mockRejectedValue(error)
    
    await expect(store.login({ email: 'test@example.com', password: 'wrong' })).rejects.toEqual(error)
    expect(store.isAuthenticated).toBe(false)
  })

  it('should logout successfully', async () => {
    const store = useAuthStore()
    store.token = 'test-token'
    store.user = { id: 1, name: 'Test User' }
    store.isAuthenticated = true
    
    authService.logout.mockResolvedValue({})
    
    await store.logout()
    
    expect(store.token).toBeNull()
    expect(store.user).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })

  it('should get user name', () => {
    const store = useAuthStore()
    store.user = { name: 'Test User' }
    expect(store.userName).toBe('Test User')
  })
})

