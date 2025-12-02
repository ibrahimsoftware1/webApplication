<template>
  <BaseLayout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">
        <div>
          <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
            Create your account
          </h2>
        </div>
        
        <Card>
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <Input
              v-model="form.name"
              type="text"
              label="Full name"
              placeholder="Enter your full name"
              :error="errors.name"
              required
              autocomplete="name"
            />
            
            <Input
              v-model="form.email"
              type="email"
              label="Email address"
              placeholder="Enter your email"
              :error="errors.email"
              required
              autocomplete="email"
            />
            
            <Input
              v-model="form.password"
              type="password"
              label="Password"
              placeholder="Enter your password"
              :error="errors.password"
              required
              autocomplete="new-password"
              hint="Must be at least 6 characters"
            />
            
            <Input
              v-model="form.password_confirmation"
              type="password"
              label="Confirm password"
              placeholder="Confirm your password"
              :error="errors.password_confirmation"
              required
              autocomplete="new-password"
            />
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</label>
              <select
                v-model="form.gender"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                :class="{ 'border-red-500 dark:border-red-400': errors.gender }"
                required
              >
                <option value="">Select gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>
              <p v-if="errors.gender" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.gender }}</p>
            </div>
            
            <Button
              type="submit"
              :loading="loading"
              :full-width="true"
            >
              Create account
            </Button>
            
            <div class="text-center text-sm">
              <span class="text-gray-600 dark:text-gray-400">Already have an account? </span>
              <router-link
                to="/login"
                class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300"
              >
                Sign in
              </router-link>
            </div>
          </form>
        </Card>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import * as yup from 'yup'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const errors = reactive({})

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  gender: ''
})

const schema = yup.object().shape({
  name: yup.string().min(2, 'Name must be at least 2 characters').required('Name is required'),
  email: yup.string().email('Invalid email').required('Email is required'),
  password: yup.string().min(6, 'Password must be at least 6 characters').required('Password is required'),
  password_confirmation: yup.string()
    .oneOf([yup.ref('password')], 'Passwords must match')
    .required('Password confirmation is required'),
  gender: yup.string().oneOf(['male', 'female'], 'Please select a gender').required('Gender is required')
})

const handleSubmit = async () => {
  try {
    Object.keys(errors).forEach(key => delete errors[key])
    
    await schema.validate(form, { abortEarly: false })
    
    loading.value = true
    await authStore.register(form)
    
    // After registration, show email verification message
    // The authStore should handle redirecting to verification page
    // For now, just go to login with a message
    router.push('/login')
  } catch (error) {
    if (error.inner) {
      error.inner.forEach((err) => {
        errors[err.path] = err.message
      })
    } else {
      if (error.response?.data?.errors) {
        Object.assign(errors, error.response.data.errors)
      }
    }
  } finally {
    loading.value = false
  }
}
</script>

