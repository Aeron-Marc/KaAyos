<template>
  <span :class="['px-3 py-1 bg-[#f3f7f9] text-[#1b364d] text-xs font-medium rounded-full', className]">
    <slot />
  </span>
</template>

<script setup>
defineProps({
  className: String,
});
</script>
