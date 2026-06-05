<template>
  <div class="p-4 md:p-8 max-w-7xl mx-auto">
    <div class="space-y-8">
      <div>
        <h1 class="text-3xl font-bold text-[#112331]">Provider Dashboard</h1>
        <p class="text-[#6b95b3] mt-2">Manage your service offerings</p>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm">
          <p class="text-[#6b95b3] text-sm font-medium mb-2">Total Earnings</p>
          <h2 class="text-3xl font-bold text-[#112331]">₱{{ providerStats.earnings.toLocaleString() }}</h2>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm">
          <p class="text-[#6b95b3] text-sm font-medium mb-2">Jobs Completed</p>
          <h2 class="text-3xl font-bold text-[#112331]">{{ providerStats.jobsCompleted }}</h2>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm">
          <p class="text-[#6b95b3] text-sm font-medium mb-2">Rating</p>
          <h2 class="text-3xl font-bold text-[#112331]">{{ providerStats.rating }} ⭐</h2>
        </div>
      </div>

      <!-- Weekly Earnings Chart -->
      <div class="bg-white rounded-2xl p-6 shadow-sm">
        <h3 class="text-xl font-bold text-[#112331] mb-6">Weekly Earnings</h3>
        <div class="flex items-end justify-between gap-2 h-64">
          <div v-for="day in providerStats.weeklyData" :key="day.name" class="flex-1 flex flex-col items-center gap-2">
            <div 
              :style="{ height: (day.earnings / 3000) * 100 + '%' }"
              class="w-full bg-[#2b516f] rounded-t-lg transition-all hover:bg-[#1b364d]"
            />
            <span class="text-xs font-medium text-[#6b95b3]">{{ day.name }}</span>
            <span class="text-xs font-semibold text-[#112331]">₱{{ day.earnings }}</span>
          </div>
        </div>
      </div>

      <!-- Active Jobs -->
      <div class="space-y-4">
        <h3 class="text-xl font-bold text-[#112331]">Upcoming Jobs</h3>
        <div class="bg-white rounded-2xl p-6 shadow-sm space-y-4">
          <div v-for="i in 3" :key="i" class="border border-gray-100 rounded-lg p-4 flex justify-between items-start">
            <div>
              <h4 class="font-semibold text-[#112331]">Job Request #{{ i }}</h4>
              <p class="text-sm text-[#6b95b3] mt-1">Plumbing - Leak Fix</p>
              <p class="text-xs text-gray-500 mt-2">Tomorrow at 2:00 PM</p>
            </div>
            <button class="bg-[#2b516f] text-white px-4 py-2 rounded-lg hover:bg-[#1b364d] transition-colors text-sm font-medium">
              Accept
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAppData } from '../../composables/useAppData';

const { providerStats } = useAppData();
</script>
