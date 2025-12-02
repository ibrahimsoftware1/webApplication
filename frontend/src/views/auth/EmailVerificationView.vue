<template>
  <BaseLayout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">
        <div>
          <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
            📧 Verify Your Email
          </h2>
        </div>
        
        <Card>
          <div class="space-y-6">
            <p class="text-gray-600 dark:text-gray-400 text-center">
              We've sent a verification link to <strong class="text-gray-900 dark:text-gray-100">{{ email }}</strong>
            </p>
            <p class="text-gray-600 dark:text-gray-400 text-sm text-center">
              Please check your email (including spam folder) and click the verification link to activate your account.
            </p>

            <div v-if="verificationMessage" class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md p-4">
              <p class="text-green-800 dark:text-green-200 text-sm">{{ verificationMessage }}</p>
            </div>

            <div v-if="verificationError" class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-md p-4">
              <p class="text-red-800 dark:text-red-200 text-sm">{{ verificationError }}</p>
            </div>

            <div class="space-y-4">
              <p class="text-gray-600 text-sm text-center">
                Didn't receive the email?
              </p>
              <Button
                @click="handleResend"
                :loading="isResending"
                :full-width="true"
              >
                Resend Verification Email
              </Button>
            </div>

            <div class="text-center text-sm">
              <span class="text-gray-600 dark:text-gray-400">Already verified? </span>
              <router-link
                to="/login"
                class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300"
              >
                Go to Login
              </router-link>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const email = ref(route.query.email || '')
const verificationMessage = ref(null)
const verificationError = ref(null)
const isResending = ref(false)

const handleResend = async () => {
  if (!email.value) {
    verificationError.value = 'Email address is required'
    return
  }

  try {
    isResending.value = true
    verificationError.value = null
    verificationMessage.value = null
    await authStore.resendVerificationEmail(email.value)
    verificationMessage.value = 'Verification email sent successfully! Please check your inbox.'
  } catch (error) {
    verificationError.value = error.response?.data?.message || 'Failed to resend verification email'
  } finally {
    isResending.value = false
  }
}

onMounted(() => {
  // Check if verification link parameters are in URL
  const userId = route.params.userId || route.query.id
  const hash = route.params.hash || route.query.hash

  if (userId && hash) {
    // Auto-verify if parameters are present
    authStore.verifyEmail(userId, hash)
      .then(() => {
        verificationMessage.value = 'Email verified successfully! You can now log in.'
        setTimeout(() => {
          router.push('/login')
        }, 2000)
      })
      .catch((error) => {
        verificationError.value = error.response?.data?.message || 'Email verification failed'
      })
  }
})
</script>

