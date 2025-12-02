import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/HomeView.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { requiresAuth: false, guestOnly: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/auth/RegisterView.vue'),
      meta: { requiresAuth: false, guestOnly: true }
    },
    {
      path: '/email/verify/:userId/:hash',
      name: 'email-verify',
      component: () => import('@/views/auth/EmailVerificationView.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/email/verify',
      name: 'email-verification',
      component: () => import('@/views/auth/EmailVerificationView.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/chat',
      name: 'chat',
      component: () => import('@/views/chat/ChatView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/chat/:id',
      name: 'conversation',
      component: () => import('@/views/chat/ConversationView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/friends',
      name: 'friends',
      component: () => import('@/views/friends/FriendsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/community',
      name: 'community',
      component: () => import('@/views/community/CommunityView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/users/:id',
      name: 'user-profile',
      component: () => import('@/views/users/UserProfileView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/views/profile/ProfileView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/payments',
      name: 'payments',
      component: () => import('@/views/payments/PaymentView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/subscribe',
      name: 'subscribe',
      component: () => import('@/views/subscriptions/SubscribeView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('@/views/admin/AdminDashboardView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/NotFoundView.vue')
    }
  ]
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
  } else if (to.meta.guestOnly && authStore.isAuthenticated) {
    next({ name: 'home' })
  } else if (to.meta.requiresAdmin) {
    // Check if user is admin
    const isAdmin = authStore.user?.roles && (
      Array.isArray(authStore.user.roles) 
        ? authStore.user.roles.includes('admin') 
        : authStore.user.has_admin_role === true
    )
    if (!isAdmin) {
      next({ name: 'chat' })
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router
