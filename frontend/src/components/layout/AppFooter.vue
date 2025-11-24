<template>
  <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">KOR</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            A modern social networking and chatting platform built with Vue.js
          </p>
        </div>
        
        <div>
          <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Links</h4>
          <ul class="space-y-2">
            <li>
              <router-link
                to="/chat"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
              >
                Chat
              </router-link>
            </li>
            <li>
              <router-link
                to="/friends"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
              >
                Friends
              </router-link>
            </li>
            <li>
              <router-link
                to="/community"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
              >
                Community
              </router-link>
            </li>
            <li>
              <router-link
                to="/profile"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
              >
                Profile
              </router-link>
            </li>
          </ul>
        </div>
        
        <div>
          <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">About</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            A social networking and chatting platform built with Vue 3, Vite, Pinia, and Tailwind CSS
          </p>
        </div>
      </div>
      
      <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700 text-center text-sm text-gray-600 dark:text-gray-400">
        <p>&copy; {{ currentYear }} KOR. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>

