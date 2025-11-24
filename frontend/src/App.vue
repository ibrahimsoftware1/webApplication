<template>
  <div id="app" class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <router-view />
    <Toast />
  </div>
</template>

<script setup>
import Toast from '@/components/ui/Toast.vue'
</script>

