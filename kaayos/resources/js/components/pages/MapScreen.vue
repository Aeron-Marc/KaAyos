<template>
  <div class="p-4 md:p-8 max-w-7xl mx-auto">
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold text-[#112331]">Service Providers Map</h1>
        <p class="text-[#6b95b3] mt-2">View available service providers near you</p>
      </div>

      <div class="bg-white rounded-2xl p-6 shadow-sm">
        <div class="w-full h-96 bg-gray-100 rounded-xl flex items-center justify-center">
          <div class="text-center">
            <MapIcon :size="48" class="mx-auto text-[#6b95b3] mb-4" />
            <p class="text-[#6b95b3] font-medium">Map integration coming soon</p>
            <p class="text-sm text-gray-500 mt-2">Interactive map will show nearby service providers</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Map as MapIcon } from '@lucide/vue';
</script>
