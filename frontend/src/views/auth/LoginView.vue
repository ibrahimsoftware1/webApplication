<template>
  <BaseLayout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">
        <div>
          <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
            {{ $t('auth.login.title') }}
          </h2>
        </div>
        
        <Card>
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <Input
              v-model="form.email"
              type="email"
              :label="$t('auth.login.email')"
              :placeholder="$t('auth.login.email')"
              :error="errors.email"
              required
              autocomplete="email"
            />
            
            <Input
              v-model="form.password"
              type="password"
              :label="$t('auth.login.password')"
              :placeholder="$t('auth.login.password')"
              :error="errors.password"
              required
              autocomplete="current-password"
            />
            
            <Button
              type="submit"
              :loading="loading"
              :full-width="true"
            >
              {{ $t('auth.login.submit') }}
            </Button>
            
            <div class="text-center text-sm">
              <span class="text-gray-600 dark:text-gray-400">{{ $t('auth.login.noAccount') }} </span>
              <router-link
                to="/register"
                class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300"
              >
                {{ $t('auth.login.signUp') }}
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
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import * as yup from 'yup'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const loading = ref(false)
const errors = reactive({})

const form = reactive({
  email: '',
  password: ''
})

const schema = yup.object().shape({
  email: yup.string().email('Invalid email').required('Email is required'),
  password: yup.string().min(6, 'Password must be at least 6 characters').required('Password is required')
})

const handleSubmit = async () => {
  try {
    errors.email = ''
    errors.password = ''
    
    await schema.validate(form, { abortEarly: false })
    
    loading.value = true
    await authStore.login({
      email: form.email,
      password: form.password
    })
    
    // Check if user is admin and redirect accordingly
    let redirect = route.query.redirect
    if (!redirect) {
      // Check if user is admin
      const user = authStore.user
      const isAdmin = user?.roles && (
        Array.isArray(user.roles) 
          ? user.roles.includes('admin') || user.roles.some(r => (typeof r === 'string' ? r : r.name) === 'admin')
          : user.has_admin_role === true
      )
      redirect = isAdmin ? '/admin' : '/chat'
    }
    router.push(redirect)
  } catch (error) {
    if (error.inner) {
      error.inner.forEach((err) => {
        errors[err.path] = err.message
      })
    } else {
      // Check if email verification is required
      if (error.emailVerificationRequired) {
        router.push({
          name: 'email-verification',
          query: { email: error.email }
        })
        return
      }
      // API error
      if (error.response?.data?.errors) {
        Object.assign(errors, error.response.data.errors)
      } else if (error.response?.data?.message) {
        errors.email = error.response.data.message
      }
    }
  } finally {
    loading.value = false
  }
}
</script>

