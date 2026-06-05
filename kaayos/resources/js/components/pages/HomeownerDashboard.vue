<template>
  <div class="p-4 md:p-8 max-w-7xl mx-auto space-y-8">
    <!-- Header Section -->
    <section class="space-y-4">
      <div class="flex justify-between items-end">
        <div>
          <p class="text-[#6b95b3] font-medium text-sm md:text-base">Quezon City, Metro Manila</p>
          <h1 class="text-2xl md:text-4xl font-bold text-[#112331] mt-1">Good morning, Maria 👋</h1>
        </div>
      </div>

      <!-- Search Bar -->
      <div class="relative flex gap-3">
        <div class="relative flex-1">
          <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" :size="20" />
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="What service do you need?" 
            class="w-full pl-11 pr-4 py-3.5 bg-white border border-transparent rounded-2xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#6b95b3] transition-all text-[#112331] placeholder:text-gray-400"
          />
        </div>
        <button class="bg-white p-3.5 rounded-2xl shadow-sm border border-transparent hover:border-[#6b95b3] transition-all text-[#2b516f]">
          <Filter :size="20" />
        </button>
      </div>
    </section>

    <!-- Categories -->
    <section>
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg md:text-xl font-bold text-[#1b364d]">Categories</h2>
        <button class="text-sm text-[#2b516f] font-medium hover:underline">See all</button>
      </div>
      <div class="flex overflow-x-auto pb-4 -mx-4 px-4 md:mx-0 md:px-0 gap-4 hide-scrollbar">
        <button
          v-for="(cat, idx) in categories"
          :key="cat.id"
          @click="activeCategory = activeCategory === cat.id ? null : cat.id"
          :class="[
            'flex flex-col items-center justify-center min-w-[90px] h-[100px] md:min-w-[110px] md:h-[120px] rounded-3xl transition-all duration-200',
            activeCategory === cat.id
              ? 'bg-[#2b516f] text-white shadow-md'
              : 'bg-white text-[#1b364d] shadow-sm hover:shadow-md border border-transparent hover:border-[#6b95b3]/30'
          ]"
        >
          <div :class="['p-3 rounded-2xl mb-2', activeCategory === cat.id ? 'bg-white/20' : 'bg-[#e8f0f5]']">
            <component :is="iconMap[cat.icon]" :size="24" :class="[activeCategory === cat.id ? 'text-white' : 'text-[#2b516f]']" />
          </div>
          <span class="text-xs md:text-sm font-medium">{{ cat.name }}</span>
        </button>
      </div>
    </section>

    <!-- Recommended Workers -->
    <section>
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg md:text-xl font-bold text-[#1b364d]">Recommended for you</h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <router-link
          v-for="worker in filteredWorkers"
          :key="worker.id"
          :to="`/worker/${worker.id}`"
          class="bg-white rounded-[24px] p-5 shadow-sm hover:shadow-md transition-all cursor-pointer border border-transparent hover:border-[#6b95b3]/30 flex flex-col gap-4 no-underline"
        >
          <div class="flex gap-4 items-start">
            <div class="relative">
              <img :src="worker.avatar" :alt="worker.name" class="w-16 h-16 rounded-2xl object-cover" />
              <div v-if="worker.verified" class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5">
                <BadgeCheck :size="18" class="text-[#2b516f] fill-[#6b95b3]/20" />
              </div>
            </div>
            <div class="flex-1">
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="font-bold text-[#112331] text-lg">{{ worker.name }}</h3>
                  <p class="text-sm text-[#6b95b3] font-medium">{{ worker.category }}</p>
                </div>
                <div class="flex items-center gap-1 bg-[#e8f0f5] px-2 py-1 rounded-lg">
                  <Star :size="14" class="fill-amber-400 text-amber-400" />
                  <span class="text-sm font-bold text-[#1b364d]">{{ worker.rating }}</span>
                </div>
              </div>
              
              <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
                <div class="flex items-center gap-1">
                  <MapPin :size="14" class="text-[#6b95b3]" />
                  <span>{{ worker.distance }}</span>
                </div>
                <div class="font-semibold text-[#2b516f]">
                  ₱{{ worker.price }}/hr
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex gap-2 flex-wrap mt-2">
            <Badge v-for="skill in worker.skills.slice(0, 3)" :key="skill">{{ skill }}</Badge>
          </div>
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Search, Filter, Star, MapPin, BadgeCheck } from 'lucide-vue';
import { Wrench, Zap, Sparkles, Fan, Hammer, Paintbrush } from 'lucide-vue';
import { useAppData } from '../../composables/useAppData';
import Badge from '../ui/Badge.vue';

const { categories, workers } = useAppData();
const searchQuery = ref('');
const activeCategory = ref(null);

const iconMap = {
  Wrench, Zap, Sparkles, Fan, Hammer, Paintbrush
};

const filteredWorkers = computed(() => {
  return workers.filter(w => !activeCategory.value || w.category.toLowerCase() === activeCategory.value);
});
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.no-underline {
  text-decoration: none;
}
</style>
