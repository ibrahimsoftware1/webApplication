<template>
  <BaseLayout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ $t('notFound.title') }}</h1>
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ $t('notFound.subtitle') }}</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-8">
          {{ $t('notFound.message') }}
        </p>
        <router-link
          to="/"
          class="btn btn-primary"
        >
          {{ $t('notFound.goHome') }}
        </router-link>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup>
import BaseLayout from '@/components/layout/BaseLayout.vue'
</script>

