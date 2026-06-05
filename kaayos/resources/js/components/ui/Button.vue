<template>
  <button
    :class="[
      'inline-flex items-center justify-center px-4 py-2 font-medium rounded-lg transition-all duration-200',
      variantStyles[variant],
      sizeStyles[size],
      disabled && 'opacity-50 cursor-not-allowed',
      className
    ]"
    :disabled="disabled"
    v-bind="$attrs"
  >
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'primary', 'secondary', 'destructive', 'outline', 'ghost'].includes(v)
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  disabled: Boolean,
  className: String,
});

const variantStyles = {
  default: 'bg-white text-[#112331] border border-transparent shadow-sm hover:shadow-md hover:border-[#6b95b3]/30',
  primary: 'bg-[#2b516f] text-white shadow-sm hover:shadow-md hover:bg-[#1b364d]',
  secondary: 'bg-[#6b95b3] text-white shadow-sm hover:shadow-md hover:bg-[#2b516f]',
  destructive: 'bg-red-500 text-white hover:bg-red-600',
  outline: 'border border-[#6b95b3] text-[#2b516f] hover:bg-[#e8f0f5]',
  ghost: 'text-[#2b516f] hover:bg-[#e8f0f5]',
};

const sizeStyles = {
  sm: 'text-sm px-3 py-1.5',
  md: 'text-base px-4 py-2',
  lg: 'text-lg px-6 py-3',
};
</script>
