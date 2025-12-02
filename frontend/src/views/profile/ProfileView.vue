<template>
  <BaseLayout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">👤 My Profile</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">Edit your personal information</p>
          </div>
          <div class="flex gap-2">
            <router-link
              to="/chat"
              class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors"
            >
              ← Back to Conversations
            </router-link>
            <router-link
              v-if="isAdmin"
              to="/admin"
              class="px-3 py-1 rounded-lg text-xs font-semibold bg-red-500 text-white border-2 border-red-600"
            >
              ⚙️ {{ $t('admin.admin') }}
            </router-link>
          </div>
        </div>

        <Card>
          <div v-if="loading" class="text-center py-10">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
          </div>

          <!-- View Mode (Read-only) -->
          <div v-else-if="!editing && authStore.user">
            <!-- Avatar Section -->
            <div class="text-center mb-8">
              <div class="w-32 h-32 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white inline-flex items-center justify-center font-bold text-5xl border-4 border-white shadow-lg overflow-hidden mx-auto">
                <img
                  v-if="authStore.user.avatar"
                  :src="getAvatarUrl(authStore.user.avatar)"
                  :alt="authStore.user.name"
                  class="w-full h-full object-cover"
                />
                <span v-else>{{ authStore.user.name?.charAt(0).toUpperCase() || 'U' }}</span>
              </div>
              <div class="mt-4">
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1 flex items-center justify-center gap-2">
                  {{ authStore.user.name }}
                  <VerifiedBadge :show="!!authStore.user.is_verified" size="lg" />
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">@{{ authStore.user.username || 'no-username' }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  <span v-if="authStore.user.is_online" class="text-green-500">● Online</span>
                  <span v-else>○ Offline</span>
                </div>
              </div>
            </div>

            <!-- Profile Details -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5 mb-6">
              <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ $t('profile.profileInformation') }}</h3>
              
              <div class="mb-4">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Email</div>
                <div class="text-sm text-gray-900 dark:text-gray-100">{{ authStore.user.email }}</div>
              </div>

              <div v-if="authStore.user.gender" class="mb-4">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Gender</div>
                <div class="text-sm text-gray-900 dark:text-gray-100">
                  {{ authStore.user.gender === 'male' ? '♂ Male' : '♀ Female' }}
                </div>
              </div>

              <div v-if="authStore.user.bio">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Bio</div>
                <div class="text-sm text-gray-900 dark:text-gray-100 p-3 bg-white dark:bg-gray-700 rounded-lg leading-relaxed">
                  {{ authStore.user.bio }}
                </div>
              </div>
            </div>

            <!-- Subscription Section -->
            <div class="bg-gradient-to-r from-primary-50 to-purple-50 dark:from-primary-900 dark:to-purple-900 rounded-xl p-5 mb-6 border border-primary-200 dark:border-primary-700">
              <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100 flex items-center gap-2">
                ✨ Verified Badge
                <VerifiedBadge :show="!!authStore.user.is_verified" size="sm" />
              </h3>
              
              <div v-if="authStore.user.is_verified" class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-4 mb-4">
                <div class="flex items-center gap-2">
                  <span class="text-green-600 dark:text-green-400 text-xl">✓</span>
                  <p class="text-green-800 dark:text-green-200 font-medium">
                    You have an active verified badge!
                  </p>
                </div>
              </div>

              <div v-else>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                  Get a verified badge that appears next to your name, just like on Facebook and Instagram!
                </p>
                <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-3 mb-4">
                  <p class="text-xs text-blue-800 dark:text-blue-200">
                    💡 <strong>Note:</strong> Purchasing a verified badge will automatically create a payment. You'll be redirected to complete the payment.
                  </p>
                </div>
                <div class="space-y-3">
                  <form @submit.prevent="handlePurchaseVerified" class="space-y-3">
                    <Button
                      type="submit"
                      :disabled="subscriptionsStore.loading"
                      class="w-full bg-gradient-to-r from-primary-600 to-purple-600 text-white"
                    >
                      {{ subscriptionsStore.loading ? 'Processing...' : 'Purchase Verified Badge' }}
                    </Button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Payment Balance & History Section -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5 mb-6">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">💳 Payments</h3>
                <router-link
                  to="/payments"
                  class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
                >
                  View All →
                </router-link>
              </div>

              <!-- Payment Summary -->
              <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-white dark:bg-gray-700 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                  <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Total Payments</div>
                  <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ paymentSummary.total || 0 }}
                  </div>
                </div>
                <div class="bg-white dark:bg-gray-700 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                  <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Total Spent</div>
                  <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ paymentSummary.totalAmount || 0 }} IQD
                  </div>
                </div>
              </div>

              <!-- Quick Create Payment -->
              <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Create Payment</h4>
                <form @submit.prevent="handleCreatePayment" class="space-y-2">
                  <div class="flex gap-2">
                    <input
                      v-model="paymentForm.amount"
                      type="number"
                      step="0.01"
                      min="0.01"
                      placeholder="Amount (IQD)"
                      required
                      class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                    />
                    <input
                      v-model="paymentForm.description"
                      type="text"
                      placeholder="Description"
                      class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                    />
                  </div>
                  <Button
                    type="submit"
                    :disabled="paymentsStore.loading || !paymentForm.amount"
                    class="w-full"
                  >
                    {{ paymentsStore.loading ? 'Creating...' : 'Create Payment' }}
                  </Button>
                </form>
              </div>

              <!-- Recent Payments -->
              <div v-if="recentPayments.length > 0">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Recent Payments</h4>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                  <div
                    v-for="payment in recentPayments"
                    :key="payment.id"
                    class="bg-white dark:bg-gray-700 rounded-lg p-3 border border-gray-200 dark:border-gray-600"
                  >
                    <div class="flex items-center justify-between mb-2">
                      <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          {{ payment.amount }} {{ payment.currency }}
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                          {{ payment.description || 'Payment' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                          {{ formatDate(payment.created_at) }}
                        </div>
                      </div>
                      <div class="flex items-center gap-2">
                        <span
                          :class="[
                            'px-2 py-1 rounded-full text-xs font-medium',
                            getPaymentStatusClass(payment.status)
                          ]"
                        >
                          {{ payment.status }}
                        </span>
                        <Button
                          v-if="payment.status === 'pending' && payment.payment_url"
                          @click.stop="window.open(payment.payment_url, '_blank')"
                          :disabled="!payment.payment_url"
                          class="text-xs px-2 py-1"
                          size="sm"
                        >
                          Pay
                        </Button>
                        <Button
                          v-if="(payment.status === 'pending' || payment.status === 'processing') && payment.payment_id"
                          :disabled="paymentsStore.loading"
                          @click.stop="handleCheckPaymentStatus(payment.id)"
                          class="text-xs px-2 py-1"
                          size="sm"
                        >
                          {{ payment.payment_url || payment.qr_code ? 'Open Payment' : 'Check' }}
                        </Button>
                        <Button
                          v-if="payment.status === 'pending' && !payment.payment_id"
                          @click.stop="handleRetryPayment(payment.id)"
                          class="text-xs px-2 py-1"
                          size="sm"
                          :disabled="paymentsStore.loading"
                          variant="outline"
                        >
                          Retry
                        </Button>
                        <span
                          v-if="payment.status === 'pending' && !payment.payment_id"
                          class="text-xs text-yellow-600 dark:text-yellow-400 ml-1"
                          title="FIB payment gateway needs to be configured"
                        >
                          ⚠️ FIB Pending
                        </span>
                      </div>
                    </div>
                    
                    <!-- SHOW QR CODE IF AVAILABLE -->
                    <div v-if="payment.qr_code && !payment.qr_code.startsWith('http')" class="mt-2">
                      <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">QR Code:</p>
                      <img 
                        :src="getQRCodeSrc(payment.qr_code)" 
                        alt="Payment QR Code"
                        class="w-32 h-32 border border-gray-200 dark:border-gray-700 rounded"
                        @error="handleQRCodeError"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <div v-else-if="!paymentsStore.loading" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
                No payments yet
              </div>

              <!-- Warning for pending payments without payment_url -->
              <div v-if="recentPayments.some(p => p.status === 'pending' && !p.payment_url)" class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-3 mt-4">
                <p class="text-xs text-yellow-800 dark:text-yellow-200">
                  ⚠️ <strong>Note:</strong> Some payments are pending due to FIB API integration issue. The payment was created in your account, but FIB payment gateway needs to be configured. Contact support for assistance.
                </p>
              </div>

              <div v-if="paymentsStore.loading" class="text-center py-4">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
              </div>
            </div>

            <!-- Edit Button -->
            <div class="text-center">
              <Button @click="startEditing" size="lg">
                ✏️ {{ $t('profile.editProfile') }}
              </Button>
            </div>
          </div>

          <!-- Edit Mode -->
          <div v-else>
            <!-- Avatar Section -->
            <div class="text-center mb-8">
              <div class="relative inline-block">
                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-primary-600 to-purple-600 text-white inline-flex items-center justify-center font-bold text-5xl border-4 border-white shadow-lg overflow-hidden">
                  <img
                    v-if="avatarPreview || authStore.user?.avatar"
                    :src="avatarPreview || getAvatarUrl(authStore.user?.avatar)"
                    :alt="form.name || authStore.user?.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else>{{ (form.name || authStore.user?.name || 'U').charAt(0).toUpperCase() }}</span>
                </div>
                <label class="absolute bottom-0 right-0 bg-green-500 text-white rounded-full w-9 h-9 flex items-center justify-center cursor-pointer shadow-lg hover:bg-green-600 transition-colors">
                  <input
                    ref="avatarInput"
                    type="file"
                    @change="handleAvatarChange"
                    accept="image/*"
                    class="hidden"
                  />
                  ✎
                </label>
              </div>
              <div class="mt-2">
                <Button
                  variant="secondary"
                  size="sm"
                  @click="handleRemoveAvatar"
                >
                  Remove Avatar
                </Button>
              </div>
            </div>

            <!-- Profile Form -->
            <form @submit.prevent="handleUpdateProfile" class="space-y-5">
              <div>
                <label class="block font-semibold mb-2 text-gray-900 dark:text-gray-100">Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                  placeholder="Your name"
                  required
                />
              </div>

              <div>
                <label class="block font-semibold mb-2 text-gray-900 dark:text-gray-100">Username</label>
                <input
                  v-model="form.username"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                  placeholder="Your username"
                />
              </div>

              <div>
                <label class="block font-semibold mb-2 text-gray-900 dark:text-gray-100">Bio</label>
                <textarea
                  v-model="form.bio"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-y bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                  placeholder="Tell us about yourself..."
                  rows="4"
                ></textarea>
              </div>

              <div>
                <label class="block font-semibold mb-2 text-gray-900 dark:text-gray-100">Gender</label>
                <select
                  v-model="form.gender"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                >
                  <option value="">Select gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
              </div>

              <div class="flex gap-3">
                <Button
                  type="submit"
                  :loading="updating"
                  class="flex-1"
                >
                  💾 Save Changes
                </Button>
                <Button
                  type="button"
                  variant="secondary"
                  @click="cancelEditing"
                >
                  Cancel
                </Button>
              </div>
            </form>
          </div>
        </Card>

        <!-- Payment Modal -->
        <Modal v-if="currentPayment" @close="currentPayment = null">
          <div class="p-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Complete Payment</h3>
            
            <div class="space-y-4">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Amount</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ currentPayment.amount }} {{ currentPayment.currency || 'IQD' }}
                </p>
              </div>

              <div v-if="currentPayment" class="text-center">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                  📱 Complete Payment
                </p>
                
                <!-- Show QR code only -->
                <div v-if="currentPayment.qr_code && !currentPayment.qr_code.startsWith('http')" class="space-y-3">
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Scan the QR code below to complete payment:
                  </p>
                  <div class="bg-white p-4 rounded-lg inline-block">
                    <img
                      :src="getQRCodeSrc(currentPayment.qr_code)"
                      alt="Payment QR Code"
                      class="mx-auto w-64 h-64"
                      @error="handleQRCodeError"
                    />
                  </div>
                  <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-3 mt-3">
                    <p class="text-xs text-blue-800 dark:text-blue-200 font-medium mb-1">Payment Credentials:</p>
                    <p class="text-xs text-blue-700 dark:text-blue-300">Phone: <strong>7301111215</strong></p>
                    <p class="text-xs text-blue-700 dark:text-blue-300">Password: <strong>Personal@123</strong></p>
                    <p class="text-xs text-blue-700 dark:text-blue-300">OTP: <strong>123-456</strong></p>
                  </div>
                </div>
                
                <!-- If no QR code yet -->
                <div v-else class="space-y-3">
                  <p class="text-sm text-yellow-600 dark:text-yellow-400">
                    ⏳ QR code is being generated. Please wait...
                  </p>
                </div>
              </div>

              <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-4">
                After completing the payment, your subscription will be activated automatically.
              </p>
            </div>
          </div>
        </Modal>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { usePaymentsStore } from '@/stores/payments'
import { useSubscriptionsStore } from '@/stores/subscriptions'
import { authService } from '@/services/auth'
import { paymentsService } from '@/services/payments'
import { useToast } from '@/composables/useToast'
import { useAvatar } from '@/composables/useAvatar'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import VerifiedBadge from '@/components/ui/VerifiedBadge.vue'
import Modal from '@/components/ui/Modal.vue'

const authStore = useAuthStore()
const paymentsStore = usePaymentsStore()
const subscriptionsStore = useSubscriptionsStore()
const { getAvatarUrl } = useAvatar()
const loading = ref(false)
const editing = ref(false)
const updating = ref(false)
const avatarInput = ref(null)
const avatarPreview = ref(null)
const avatarFile = ref(null)

// Fixed amount for verified badge: 1000 IQD (no longer needed as input)
const paymentForm = reactive({
  amount: '',
  description: ''
})

const currentPayment = ref(null)

const form = reactive({
  name: '',
  username: '',
  bio: '',
  gender: ''
})

const isAdmin = computed(() => {
  if (!authStore.user) return false
  const roles = authStore.user.roles
  if (Array.isArray(roles)) {
    return roles.includes('admin') || roles.some(r => (typeof r === 'string' ? r : r.name) === 'admin')
  }
  return authStore.user.has_admin_role === true
})

const startEditing = () => {
  if (authStore.user) {
    form.name = authStore.user.name || ''
    form.username = authStore.user.username || ''
    form.bio = authStore.user.bio || ''
    form.gender = authStore.user.gender || ''
    avatarPreview.value = authStore.user.avatar || null
    avatarFile.value = null
    editing.value = true
  }
}

const cancelEditing = () => {
  editing.value = false
  avatarPreview.value = null
  avatarFile.value = null
  if (avatarInput.value) {
    avatarInput.value.value = ''
  }
}

const handleAvatarChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 2048 * 1024) {
      useToast().error('Avatar size must be less than 2MB')
      return
    }
    avatarFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const handleRemoveAvatar = async () => {
  try {
    await authService.removeAvatar()
    avatarPreview.value = null
    avatarFile.value = null
    if (authStore.user) {
      authStore.user.avatar = null
    }
    useToast().success('Avatar removed successfully')
  } catch (error) {
    useToast().error('Failed to remove avatar')
  }
}

const handleUpdateProfile = async () => {
  try {
    updating.value = true

    // Update profile info
    await authService.updateProfile({
      name: form.name,
      username: form.username,
      bio: form.bio,
      gender: form.gender
    })

    // Update avatar if changed
    if (avatarFile.value) {
      const formData = new FormData()
      formData.append('avatar', avatarFile.value)
      const avatarResponse = await authService.updateAvatar(formData)
      const avatarUrl = avatarResponse.data.data?.avatar_url || avatarResponse.data.data?.avatar
      if (avatarUrl && authStore.user) {
        authStore.user.avatar = avatarUrl
        avatarPreview.value = avatarUrl
      }
    }

    // Reload profile to get latest data
    await authStore.fetchProfile()

    editing.value = false
    avatarFile.value = null
    if (avatarInput.value) {
      avatarInput.value.value = ''
    }

    useToast().success('Profile updated successfully!')
  } catch (error) {
    useToast().error(error.response?.data?.message || 'Failed to update profile')
  } finally {
    updating.value = false
  }
}

// Payment and Subscription Methods
const handlePurchaseVerified = async () => {
  try {
    // Fixed amount: 1000 IQD (handled in backend)
    const response = await subscriptionsStore.purchaseVerified()
    const data = response.data.data || response.data
    
    // Refresh payments list to get the full payment object
    await paymentsStore.fetchPayments()
    
    // Find the payment in the list (data.payment_id is the local payment ID)
    const payment = paymentsStore.payments.find(p => p.id === data.payment_id) || data
    
    // Set up payment modal data with QR code/URL
    // Note: QR code IS the URL from FIB API
    currentPayment.value = {
      id: payment.id || data.payment_id, // Local payment ID
      payment_id: payment.payment_id, // FIB payment ID (may be null if FIB failed)
      payment_url: payment.payment_url || data.payment_url,
      qr_code: payment.qr_code || data.qr_code || payment.payment_url || data.payment_url, // QR code is the URL
      amount: payment.amount || data.amount || 0,
      currency: payment.currency || data.currency || 'IQD',
      description: payment.description || data.description || 'Verified Badge Subscription',
      status: payment.status || data.status || 'pending'
    }
    
    // If QR code/URL exists, show modal automatically
    if (currentPayment.value.qr_code || currentPayment.value.payment_url) {
      useToast().success('Payment created! Open the URL to complete payment.')
      
      // Start polling for payment status if payment has FIB payment_id
      if (currentPayment.value.payment_id && currentPayment.value.id) {
        startPaymentStatusPolling(currentPayment.value.id)
      }
    } else {
      // FIB API failed - show error message
      useToast().error('FIB payment gateway error. Please try again or contact support.')
      console.error('FIB payment failed - no URL/QR code received', {
        payment: currentPayment.value,
        data: data
      })
    }
    
    // Refresh user data
    await authStore.fetchProfile()
    await subscriptionsStore.fetchVerifiedStatus()
  } catch (error) {
    console.error('Failed to purchase verified badge:', error)
  }
}

const handleCreatePayment = async () => {
  try {
    const response = await paymentsStore.createPayment({
      amount: parseFloat(paymentForm.amount),
      currency: 'IQD',
      description: paymentForm.description || 'Payment'
    })
    
    const payment = response.data.data || response.data
    currentPayment.value = payment
    paymentForm.amount = ''
    paymentForm.description = ''
    
    // Refresh payments list to update summary
    await paymentsStore.fetchPayments()
    
    useToast().success('Payment created successfully!')
  } catch (error) {
    console.error('Failed to create payment:', error)
  }
}

const handleCheckPaymentStatus = async (paymentId) => {
  try {
    // First, find the payment in the current list to get URL/QR code
    let payment = paymentsStore.payments.find(p => p.id === paymentId)
    
    // If payment not found or doesn't have URL, refresh payments list
    if (!payment || (!payment.payment_url && !payment.qr_code)) {
      await paymentsStore.fetchPayments()
      payment = paymentsStore.payments.find(p => p.id === paymentId)
    }
    
    // Check payment status - THIS RETRIEVES URL FROM DATABASE
    const response = await paymentsStore.checkPaymentStatus(paymentId)
    await paymentsStore.fetchPayments()
    
    // Get payment data from API response (has URL from database)
    const paymentData = response?.data?.data || {}
    
    // Get updated payment from store
    const updatedPayment = paymentsStore.payments.find(p => p.id === paymentId) || paymentData
    
    // Extract URL - backend returns it in payment_url field from database
    const paymentUrl = paymentData.payment_url || updatedPayment.payment_url
    
    // Extract QR code - backend returns it in qr_code field from database
    const qrCode = paymentData.qr_code || updatedPayment.qr_code
    
    console.log('=== URL FROM DATABASE ===')
    console.log('Payment ID:', paymentId)
    console.log('URL:', paymentUrl)
    console.log('QR Code:', qrCode ? 'EXISTS' : 'NULL')
    
    // ALWAYS open modal - URL is in database
    currentPayment.value = {
      id: updatedPayment.id || paymentData.id,
      payment_id: updatedPayment.payment_id || paymentData.payment_id,
      payment_url: paymentUrl || null, // URL FROM DATABASE
      qr_code: qrCode || null, // QR CODE FROM DATABASE
      amount: updatedPayment.amount || paymentData.amount,
      currency: updatedPayment.currency || paymentData.currency || 'IQD',
      description: updatedPayment.description || paymentData.description,
      status: updatedPayment.status || paymentData.status
    }
    
    if (paymentUrl) {
      useToast().success(`URL retrieved: ${paymentUrl.substring(0, 50)}...`)
    } else {
      useToast().warning('URL not found in database')
    }
  } catch (error) {
    console.error('Failed to check payment status:', error)
    useToast().error('Failed to check payment status')
  }
}

const handleRetryPayment = async (paymentId) => {
  try {
    await paymentsStore.retryPayment(paymentId)
    // Refresh payments list to update UI
    await paymentsStore.fetchPayments()
  } catch (error) {
    console.error('Failed to retry payment:', error)
  }
}

const paymentSummary = computed(() => {
  const payments = paymentsStore.payments || []
  const total = payments.length
  const totalAmount = payments.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0)
  return { total, totalAmount: totalAmount.toFixed(2) }
})

const recentPayments = computed(() => {
  return (paymentsStore.payments || []).slice(0, 5)
})

const getPaymentStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    failed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleString()
}

// Helper function to get QR code image source
const getQRCodeSrc = (qrCode) => {
  if (!qrCode) return ''
  
  // If it already has data:image prefix, return as is
  if (qrCode.startsWith('data:image')) {
    return qrCode
  }
  
  // If it's a URL, return as is
  if (qrCode.startsWith('http')) {
    return qrCode
  }
  
  // Otherwise, assume it's base64 and add the prefix
  return `data:image/png;base64,${qrCode}`
}

const handleQRCodeError = (event) => {
  console.error('QR Code image failed to load:', event.target.src)
  // Try alternative source if available
  if (currentPayment.value?.qr_code_url) {
    event.target.src = currentPayment.value.qr_code_url
  }
}

// Payment status polling
let paymentPollingInterval = null

const startPaymentStatusPolling = (paymentId) => {
  // Clear any existing polling
  if (paymentPollingInterval) {
    clearInterval(paymentPollingInterval)
    paymentPollingInterval = null
  }
  
  let pollCount = 0
  const maxPolls = 120 // Stop after 10 minutes (120 * 5 seconds)
  
  // Poll every 5 seconds for pending/processing payments
  paymentPollingInterval = setInterval(async () => {
    try {
      pollCount++
      
      // Stop polling after max attempts
      if (pollCount > maxPolls) {
        clearInterval(paymentPollingInterval)
        paymentPollingInterval = null
        console.log('Payment polling stopped: Max attempts reached')
        return
      }
      
      // Refresh payments list first
      await paymentsStore.fetchPayments()
      const payment = paymentsStore.payments.find(p => p.id === paymentId)
      
      // Stop polling if payment not found or is completed, failed, or cancelled
      if (!payment) {
        clearInterval(paymentPollingInterval)
        paymentPollingInterval = null
        console.log('Payment polling stopped: Payment not found')
        return
      }
      
      if (['completed', 'failed', 'cancelled'].includes(payment.status)) {
        clearInterval(paymentPollingInterval)
        paymentPollingInterval = null
        console.log('Payment polling stopped: Payment status is', payment.status)
        
        // If completed, refresh user data to show verified badge
        if (payment.status === 'completed') {
          // Refresh user profile to get updated is_verified status
          await authStore.fetchProfile()
          await subscriptionsStore.fetchVerifiedStatus()
          
          useToast().success('Payment completed! You are now verified! ✨')
          
          // Close modal if open
          if (currentPayment.value?.id === paymentId) {
            currentPayment.value = null
          }
          
          // Refresh conversations to update badge everywhere
          // The badge will show on next page navigation or refresh
        }
        return
      }
      
      // Stop polling if QR code is available (no need to keep checking)
      if (payment.qr_code && !payment.qr_code.startsWith('http')) {
        clearInterval(paymentPollingInterval)
        paymentPollingInterval = null
        return
      }
      
      // Only poll if payment has FIB payment_id and status is still processing/pending
      if (payment.payment_id && ['pending', 'processing'].includes(payment.status)) {
        // Check status silently (no toast messages during polling)
        try {
          const response = await paymentsService.checkStatus(paymentId)
          const paymentData = response.data.data || response.data
          
          // Update payment in store silently
          const index = paymentsStore.payments.findIndex(p => p.id === paymentId)
          if (index !== -1) {
            paymentsStore.payments[index] = { ...paymentsStore.payments[index], ...paymentData }
          }
          
          // Update modal if it's the current payment
          if (currentPayment.value?.id === paymentId) {
            const updatedPayment = paymentsStore.payments.find(p => p.id === paymentId)
            if (updatedPayment) {
              currentPayment.value = { ...currentPayment.value, ...updatedPayment }
            }
          }
        } catch (error) {
          // Silent error during polling
          console.error('Silent polling error:', error)
        }
      } else {
        // No payment_id or status changed, stop polling
        clearInterval(paymentPollingInterval)
        paymentPollingInterval = null
      }
    } catch (error) {
      console.error('Payment polling error:', error)
      // Stop polling on error after a few attempts
      if (pollCount > 10) {
        clearInterval(paymentPollingInterval)
        paymentPollingInterval = null
        console.log('Payment polling stopped: Too many errors')
      }
    }
  }, 5000) // Poll every 5 seconds
}

// Clean up polling on unmount
onUnmounted(() => {
  if (paymentPollingInterval) {
    clearInterval(paymentPollingInterval)
  }
})

onMounted(async () => {
  if (!authStore.user) {
    loading.value = true
    await authStore.fetchProfile()
    loading.value = false
  }
  
  // Load payments and subscription status
  try {
    await paymentsStore.fetchPayments()
    await subscriptionsStore.fetchVerifiedStatus()
  } catch (error) {
    console.error('Failed to fetch payments/subscriptions:', error)
  }
})
</script>
