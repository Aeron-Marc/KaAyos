<template>
  <div :class="['bg-white rounded-[24px] p-6 shadow-sm hover:shadow-md transition-all border border-transparent hover:border-[#6b95b3]/30', className]">
    <slot />
  </div>
</template>

<script setup>
defineProps({
  className: String,
});
</script>
