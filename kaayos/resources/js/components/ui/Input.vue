<template>
  <div :class="['relative flex-1']">
    <component
      :is="icon"
      v-if="icon"
      :size="20"
      :class="['absolute left-4 top-1/2 -translate-y-1/2', iconColor || 'text-gray-400']"
    />
    <input
      :class="[
        'w-full px-4 py-3.5 bg-white border border-transparent rounded-2xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#6b95b3] transition-all text-[#112331] placeholder:text-gray-400',
        icon && 'pl-11',
        className
      ]"
      v-bind="$attrs"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>

<script setup>
defineProps({
  modelValue: String,
  icon: Object,
  iconColor: String,
  className: String,
});

defineEmits(['update:modelValue']);
</script>
