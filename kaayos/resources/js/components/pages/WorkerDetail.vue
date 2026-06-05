<template>
  <div class="p-4 md:p-8 max-w-7xl mx-auto">
    <router-link to="/" class="text-sm text-[#6b95b3] hover:text-[#2b516f] font-medium mb-6 inline-block">← Back</router-link>
    
    <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm space-y-8" v-if="worker">
      <!-- Header -->
      <div class="flex flex-col md:flex-row gap-6 md:gap-8">
        <div class="relative">
          <img :src="worker.avatar" :alt="worker.name" class="w-32 h-32 rounded-2xl object-cover" />
          <div v-if="worker.verified" class="absolute -bottom-2 -right-2 bg-white rounded-full p-1">
            <BadgeCheck :size="24" class="text-[#2b516f] fill-[#6b95b3]/20" />
          </div>
        </div>

        <div class="flex-1">
          <div class="mb-4">
            <h1 class="text-3xl font-bold text-[#112331]">{{ worker.name }}</h1>
            <p class="text-lg text-[#6b95b3] font-medium">{{ worker.category }}</p>
          </div>

          <div class="flex flex-wrap gap-4 mb-6">
            <div class="flex items-center gap-2 bg-[#e8f0f5] px-4 py-2 rounded-lg">
              <Star :size="16" class="fill-amber-400 text-amber-400" />
              <span class="font-semibold text-[#1b364d]">{{ worker.rating }} ({{ worker.reviews }} reviews)</span>
            </div>
            <div class="flex items-center gap-2 bg-[#e8f0f5] px-4 py-2 rounded-lg">
              <MapPin :size="16" class="text-[#6b95b3]" />
              <span class="text-[#1b364d]">{{ worker.distance }}</span>
            </div>
            <div class="bg-[#e8f0f5] px-4 py-2 rounded-lg text-[#2b516f] font-semibold">
              ₱{{ worker.price }}/hour
            </div>
          </div>

          <button class="w-full md:w-auto bg-[#2b516f] text-white font-medium py-3 px-8 rounded-lg hover:bg-[#1b364d] transition-colors">
            Book Service
          </button>
        </div>
      </div>

      <!-- About Section -->
      <div class="border-t border-gray-200 pt-8 space-y-4">
        <h2 class="text-xl font-bold text-[#112331]">About</h2>
        <p class="text-[#6b95b3] leading-relaxed">{{ worker.about }}</p>
      </div>

      <!-- Skills Section -->
      <div class="space-y-4">
        <h2 class="text-xl font-bold text-[#112331]">Skills & Services</h2>
        <div class="flex flex-wrap gap-3">
          <Badge v-for="skill in worker.skills" :key="skill">{{ skill }}</Badge>
        </div>
      </div>

      <!-- Reviews Section (Static) -->
      <div class="border-t border-gray-200 pt-8 space-y-4">
        <h2 class="text-xl font-bold text-[#112331]">Recent Reviews</h2>
        <div class="space-y-4">
          <div v-for="i in 3" :key="i" class="border border-gray-100 rounded-lg p-4">
            <div class="flex items-start justify-between mb-2">
              <div class="font-semibold text-[#112331]">Customer {{ i }}</div>
              <div class="flex gap-1">
                <Star v-for="j in 5" :key="j" :size="14" class="fill-amber-400 text-amber-400" />
              </div>
            </div>
            <p class="text-gray-600">Great service and very professional. Highly recommended!</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { Star, MapPin, BadgeCheck } from 'lucide-vue';
import { useAppData } from '../../composables/useAppData';
import Badge from '../ui/Badge.vue';

const route = useRoute();
const { workers } = useAppData();

const worker = computed(() => {
  return workers.find(w => w.id === route.params.id);
});
</script>
