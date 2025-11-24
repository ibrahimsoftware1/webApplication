<template>
  <BaseLayout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4">
          {{ $t('home.welcome') }}
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
          {{ $t('home.subtitle') }}
        </p>
        
        <div v-if="!authStore.isAuthenticated" class="flex justify-center space-x-4">
          <router-link
            to="/register"
            class="btn btn-primary"
          >
            {{ $t('home.getStarted') }}
          </router-link>
          <router-link
            to="/login"
            class="btn btn-secondary"
          >
            {{ $t('auth.login.submit') }}
          </router-link>
        </div>
        
        <div v-else class="flex justify-center space-x-4">
          <router-link
            to="/chat"
            class="btn btn-primary"
          >
            {{ $t('home.openChat') }}
          </router-link>
          <router-link
            to="/friends"
            class="btn btn-secondary"
          >
            {{ $t('home.viewFriends') }}
          </router-link>
        </div>
      </div>
      
      <div class="mt-16 grid grid-cols-1 md:grid-cols-2 gap-8">
        <Card :title="$t('home.features.chat.title')">
          <p class="text-gray-600 dark:text-gray-400">
            {{ $t('home.features.chat.description') }}
          </p>
        </Card>
        
        <Card :title="$t('home.features.friends.title')">
          <p class="text-gray-600 dark:text-gray-400">
            {{ $t('home.features.friends.description') }}
          </p>
        </Card>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Card from '@/components/ui/Card.vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
</script>
