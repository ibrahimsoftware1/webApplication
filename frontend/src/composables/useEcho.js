import { ref, onUnmounted } from 'vue'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

let echoInstance = null

export function useEcho(token) {
  const isConnected = ref(false)
  const socketId = ref(null)

  const initializeEcho = () => {
    if (echoInstance) {
      return echoInstance
    }

    window.Pusher = Pusher

    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

    echoInstance = new Echo({
      broadcaster: 'pusher',
      key: 'w3ega2dc8ka76bwzaxvh',
      wsHost: '127.0.0.1',
      wsPort: 8080,
      wssPort: 8080,
      forceTLS: false,
      encrypted: false,
      disableStats: true,
      enabledTransports: ['ws', 'wss'],
      cluster: 'mt1',
      auth: {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json'
        }
      },
      authEndpoint: `${apiBaseUrl}/broadcasting/auth`
    })

    // Bind connection events
    if (echoInstance.connector?.pusher?.connection) {
      const connection = echoInstance.connector.pusher.connection

      connection.bind('connected', () => {
        isConnected.value = true
        socketId.value = connection.socket_id
        console.log('🟢 WebSocket connected, Socket ID:', socketId.value)
      })

      connection.bind('disconnected', () => {
        isConnected.value = false
        socketId.value = null
        console.log('🔴 WebSocket disconnected')
      })

      connection.bind('error', (error) => {
        console.error('❌ WebSocket error:', error)
        isConnected.value = false
      })

      // Check initial state
      if (connection.state === 'connected') {
        isConnected.value = true
        socketId.value = connection.socket_id
      }
    }

    return echoInstance
  }

  const disconnect = () => {
    if (echoInstance) {
      echoInstance.disconnect()
      echoInstance = null
      isConnected.value = false
      socketId.value = null
    }
  }

  onUnmounted(() => {
    disconnect()
  })

  return {
    echo: echoInstance,
    initializeEcho,
    disconnect,
    isConnected,
    socketId
  }
}

