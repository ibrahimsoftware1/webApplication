<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="handleBackdropClick"
      >
        <div class="flex min-h-screen items-center justify-center p-4">
          <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            aria-hidden="true"
          ></div>
          
          <div
            :class="modalClasses"
            role="dialog"
            :aria-modal="true"
            :aria-labelledby="titleId"
            @keydown.esc="handleEscape"
          >
            <div class="flex items-center justify-between mb-4">
              <h2
                v-if="title"
                :id="titleId"
                class="text-xl font-semibold text-gray-900"
              >
                {{ title }}
              </h2>
              <button
                v-if="closable"
                type="button"
                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg p-1"
                :aria-label="closeLabel"
                @click="close"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <div class="modal-content">
              <slot />
            </div>
            
            <div v-if="$slots.footer" class="mt-6 flex justify-end gap-3">
              <slot name="footer" />
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  },
  closable: {
    type: Boolean,
    default: true
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  },
  closeLabel: {
    type: String,
    default: 'Close modal'
  }
})

const emit = defineEmits(['update:modelValue', 'close'])

const titleId = computed(() => `modal-title-${Math.random().toString(36).substr(2, 9)}`)

const modalClasses = computed(() => {
  const base = 'relative bg-white rounded-lg shadow-xl p-6 w-full max-w-lg transform transition-all'
  const sizes = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl'
  }
  return `${base} ${sizes[props.size]}`
})

const close = () => {
  emit('update:modelValue', false)
  emit('close')
}

const handleBackdropClick = () => {
  if (props.closeOnBackdrop) {
    close()
  }
}

const handleEscape = (event) => {
  if (event.key === 'Escape' && props.closable) {
    close()
  }
}

// Lock body scroll when modal is open
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .modal-content,
.modal-leave-active .modal-content {
  transition: transform 0.3s ease;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.95);
}
</style>

