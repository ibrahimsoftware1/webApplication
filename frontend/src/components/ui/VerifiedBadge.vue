<template>
  <span
    v-if="show"
    class="verified-badge inline-flex items-center"
    :class="sizeClass"
    :title="tooltip || 'Verified Account'"
  >
    <svg
      class="verified-icon"
      :class="iconSizeClass"
      viewBox="0 0 24 24"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"
        fill="currentColor"
        stroke="currentColor"
        stroke-width="1.5"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  tooltip: {
    type: String,
    default: null
  }
})

const sizeClass = computed(() => {
  const classes = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base'
  }
  return classes[props.size] || classes.md
})

const iconSizeClass = computed(() => {
  const classes = {
    sm: 'w-3 h-3',
    md: 'w-4 h-4',
    lg: 'w-5 h-5'
  }
  return classes[props.size] || classes.md
})
</script>

<style scoped>
.verified-badge {
  color: #1DA1F2; /* Twitter blue, change to your brand color */
  margin-left: 4px;
  vertical-align: middle;
  display: inline-flex;
  align-items: center;
}

.verified-icon {
  animation: shine 2s ease-in-out infinite;
  filter: drop-shadow(0 0 2px currentColor);
}

@keyframes shine {
  0%, 100% {
    opacity: 1;
    filter: drop-shadow(0 0 2px currentColor) brightness(1);
  }
  50% {
    opacity: 0.9;
    filter: drop-shadow(0 0 4px currentColor) brightness(1.2);
  }
}

.verified-icon:hover {
  animation: pulse 0.6s ease-in-out;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

/* Enhanced shine effect */
.verified-icon {
  position: relative;
}

.verified-icon::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.4),
    transparent
  );
  animation: shimmer 3s infinite;
}

@keyframes shimmer {
  0% {
    left: -100%;
  }
  100% {
    left: 100%;
  }
}
</style>

