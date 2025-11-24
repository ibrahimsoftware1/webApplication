<template>
  <div :class="cardClasses">
    <div v-if="$slots.header || title" class="card-header">
      <slot name="header">
        <h3 v-if="title" class="text-lg font-semibold text-gray-900">
          {{ title }}
        </h3>
      </slot>
    </div>
    
    <div class="card-body">
      <slot />
    </div>
    
    <div v-if="$slots.footer" class="card-footer">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'bordered', 'shadow'].includes(value)
  },
  padding: {
    type: String,
    default: 'normal',
    validator: (value) => ['none', 'sm', 'normal', 'lg'].includes(value)
  }
})

const cardClasses = computed(() => {
  const base = 'bg-white dark:bg-gray-800 rounded-lg'
  
  const variants = {
    default: 'shadow-sm border border-gray-200 dark:border-gray-700',
    bordered: 'border-2 border-gray-300 dark:border-gray-600',
    shadow: 'shadow-lg'
  }
  
  const paddings = {
    none: '',
    sm: 'p-4',
    normal: 'p-6',
    lg: 'p-8'
  }
  
  return `${base} ${variants[props.variant]} ${paddings[props.padding]}`
})
</script>

<style scoped>
.card-header {
  @apply mb-4 pb-4 border-b border-gray-200 dark:border-gray-700;
}

.card-header h3 {
  @apply text-gray-900 dark:text-gray-100;
}

.card-body {
  @apply text-gray-700 dark:text-gray-300;
}

.card-footer {
  @apply mt-4 pt-4 border-t border-gray-200 dark:border-gray-700;
}
</style>

