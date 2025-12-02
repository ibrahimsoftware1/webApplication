<template>
  <BaseLayout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-8">Get Verified Badge</h1>

        <Card class="mb-6">
          <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center mb-4">
              <VerifiedBadge :show="true" size="lg" />
            </div>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
              Verified Account Badge
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
              Get a verified badge that appears next to your name, just like on Facebook and Instagram!
            </p>
          </div>

          <div v-if="subscriptionsStore.verifiedStatus?.has_active_subscription" class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-2">
              <span class="text-green-600 dark:text-green-400">✓</span>
              <p class="text-green-800 dark:text-green-200 font-medium">
                You already have an active verified badge!
              </p>
            </div>
          </div>

          <div v-else>
            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Benefits:</h3>
              <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-2">
                  <span class="text-primary-600">✓</span>
                  <span>Shining verified badge next to your name</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-primary-600">✓</span>
                  <span>Increased credibility and trust</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-primary-600">✓</span>
                  <span>Stand out in the community</span>
                </li>
              </ul>
            </div>

            <form @submit.prevent="handlePurchase" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Amount (IQD)
                </label>
                <Input
                  v-model="amount"
                  type="number"
                  step="0.01"
                  min="1"
                  placeholder="Enter amount"
                  required
                  class="w-full"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Minimum: 1 IQD
                </p>
              </div>

              <Button
                type="submit"
                :disabled="subscriptionsStore.loading || !amount || parseFloat(amount) < 1"
                class="w-full bg-gradient-to-r from-primary-600 to-purple-600 text-white"
              >
                {{ subscriptionsStore.loading ? 'Processing...' : 'Purchase Verified Badge' }}
              </Button>
            </form>
          </div>
        </Card>

        <!-- Payment Modal -->
        <Modal v-if="paymentData" @close="paymentData = null">
          <div class="p-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Complete Payment</h3>
            
            <div class="space-y-4">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Amount</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ amount }} IQD
                </p>
              </div>

              <div v-if="paymentData.payment_url">
                <Button
                  @click="window.open(paymentData.payment_url, '_blank')"
                  class="w-full bg-gradient-to-r from-primary-600 to-purple-600 text-white mb-4"
                >
                  Open Payment Page
                </Button>
              </div>

              <div v-if="paymentData.qr_code" class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Or scan QR code</p>
                <img
                  :src="`data:image/png;base64,${paymentData.qr_code}`"
                  alt="Payment QR Code"
                  class="mx-auto max-w-xs"
                />
              </div>

              <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-4">
                After completing the payment, your verified badge will be activated automatically.
              </p>
            </div>
          </div>
        </Modal>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useSubscriptionsStore } from '@/stores/subscriptions'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Modal from '@/components/ui/Modal.vue'
import VerifiedBadge from '@/components/ui/VerifiedBadge.vue'

const subscriptionsStore = useSubscriptionsStore()

const amount = ref('')
const paymentData = ref(null)

const handlePurchase = async () => {
  try {
    const response = await subscriptionsStore.purchaseVerified(parseFloat(amount.value))
    const data = response.data.data || response.data
    paymentData.value = data
    
    // Refresh verified status
    await subscriptionsStore.fetchVerifiedStatus()
  } catch (error) {
    console.error('Failed to purchase verified badge:', error)
  }
}

onMounted(async () => {
  await subscriptionsStore.fetchVerifiedStatus()
})
</script>

