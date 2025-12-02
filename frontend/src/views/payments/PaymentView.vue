<template>
  <BaseLayout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-8">Payments</h1>

        <!-- Create Payment Form -->
        <Card class="mb-8">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Create New Payment</h2>
          <form @submit.prevent="handleCreatePayment" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Amount (IQD)
              </label>
              <Input
                v-model="paymentForm.amount"
                type="number"
                step="0.01"
                min="0.01"
                placeholder="Enter amount"
                required
                class="w-full"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Description
              </label>
              <Input
                v-model="paymentForm.description"
                type="text"
                placeholder="Payment description"
                class="w-full"
              />
            </div>
            <Button
              type="submit"
              :disabled="paymentsStore.loading || !paymentForm.amount"
              class="w-full bg-gradient-to-r from-primary-600 to-purple-600 text-white"
            >
              {{ paymentsStore.loading ? 'Processing...' : 'Create Payment' }}
            </Button>
          </form>
        </Card>

        <!-- Payment Modal -->
        <Modal v-if="currentPayment" @close="currentPayment = null">
          <div class="p-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Payment Details</h3>
            
            <div class="space-y-4">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Amount</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ currentPayment.amount }} {{ currentPayment.currency }}
                </p>
              </div>
              
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                <span
                  :class="[
                    'inline-block px-3 py-1 rounded-full text-sm font-medium',
                    getStatusClass(currentPayment.status)
                  ]"
                >
                  {{ currentPayment.status }}
                </span>
              </div>

              <div v-if="currentPayment.payment_url">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Payment URL</p>
                <a
                  :href="currentPayment.payment_url"
                  target="_blank"
                  class="text-primary-600 hover:underline break-all"
                >
                  {{ currentPayment.payment_url }}
                </a>
                <Button
                  @click="window.open(currentPayment.payment_url, '_blank')"
                  class="mt-2 w-full bg-gradient-to-r from-primary-600 to-purple-600 text-white"
                >
                  Open Payment Page
                </Button>
              </div>

              <div v-if="currentPayment.qr_code" class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">QR Code</p>
                <img
                  :src="`data:image/png;base64,${currentPayment.qr_code}`"
                  alt="Payment QR Code"
                  class="mx-auto max-w-xs"
                />
              </div>

              <div class="flex gap-2 mt-4">
                <Button
                  @click="handleCheckStatus(currentPayment.id)"
                  :disabled="paymentsStore.loading"
                  class="flex-1"
                >
                  Check Status
                </Button>
                <Button
                  v-if="currentPayment.status === 'processing' || currentPayment.status === 'pending'"
                  @click="handleCancelPayment(currentPayment.id)"
                  :disabled="paymentsStore.loading"
                  class="flex-1 bg-red-500 hover:bg-red-600"
                >
                  Cancel
                </Button>
              </div>
            </div>
          </div>
        </Modal>

        <!-- Payment History -->
        <div>
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Payment History</h2>
          
          <div v-if="paymentsStore.loading && paymentsStore.payments.length === 0" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
          </div>

          <div v-else-if="paymentsStore.payments.length === 0" class="card text-center py-12">
            <p class="text-gray-600 dark:text-gray-400">No payments yet</p>
          </div>

          <div v-else class="space-y-4">
            <Card
              v-for="payment in paymentsStore.payments"
              :key="payment.id"
              class="hover:shadow-md transition-shadow cursor-pointer"
              @click="currentPayment = payment"
            >
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                      {{ payment.amount }} {{ payment.currency }}
                    </span>
                    <span
                      :class="[
                        'px-2 py-1 rounded-full text-xs font-medium',
                        getStatusClass(payment.status)
                      ]"
                    >
                      {{ payment.status }}
                    </span>
                  </div>
                  <p v-if="payment.description" class="text-sm text-gray-600 dark:text-gray-400">
                    {{ payment.description }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                    {{ formatDate(payment.created_at) }}
                  </p>
                </div>
                <div class="flex gap-2">
                  <Button
                    v-if="payment.payment_url"
                    @click.stop="window.open(payment.payment_url, '_blank')"
                    class="text-sm px-3 py-1"
                  >
                    Pay
                  </Button>
                </div>
              </div>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePaymentsStore } from '@/stores/payments'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Modal from '@/components/ui/Modal.vue'

const paymentsStore = usePaymentsStore()

const paymentForm = ref({
  amount: '',
  description: ''
})

const currentPayment = ref(null)

const handleCreatePayment = async () => {
  try {
    const response = await paymentsStore.createPayment({
      amount: parseFloat(paymentForm.value.amount),
      currency: 'IQD',
      description: paymentForm.value.description || 'Payment'
    })
    
    const payment = response.data.data || response.data
    currentPayment.value = payment
    paymentForm.value = { amount: '', description: '' }
  } catch (error) {
    console.error('Failed to create payment:', error)
  }
}

const handleCheckStatus = async (id) => {
  try {
    await paymentsStore.checkPaymentStatus(id)
    const payment = paymentsStore.getPaymentById(id)
    if (payment) {
      currentPayment.value = payment
    }
  } catch (error) {
    console.error('Failed to check status:', error)
  }
}

const handleCancelPayment = async (id) => {
  if (!confirm('Are you sure you want to cancel this payment?')) return
  
  try {
    await paymentsStore.cancelPayment(id)
    const payment = paymentsStore.getPaymentById(id)
    if (payment) {
      currentPayment.value = payment
    }
  } catch (error) {
    console.error('Failed to cancel payment:', error)
  }
}

const getStatusClass = (status) => {
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

onMounted(() => {
  paymentsStore.fetchPayments()
})
</script>

