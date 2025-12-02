/**
 * Composable for handling avatar URLs
 * Ensures avatar URLs are properly formatted with base URL if needed
 */
export function useAvatar() {
  const getAvatarUrl = (avatar) => {
    if (!avatar) return null
    
    // If it's already a full URL, return it
    if (avatar.startsWith('http://') || avatar.startsWith('https://')) {
      return avatar
    }
    
    // If it starts with /storage, prepend base URL
    if (avatar.startsWith('/storage')) {
      const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
      return baseUrl.replace('/api', '') + avatar
    }
    
    // If it's a relative path, prepend storage path
    const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
    return baseUrl.replace('/api', '') + '/storage/' + avatar
  }
  
  return {
    getAvatarUrl
  }
}

