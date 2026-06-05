<template>
  <div class="p-4 md:p-8 max-w-7xl mx-auto">
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold text-[#112331]">Your Bookings</h1>
        <p class="text-[#6b95b3] mt-2">Manage your service requests</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="booking in bookings" :key="booking.id" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="font-bold text-lg text-[#112331]">{{ booking.service }}</h3>
              <p class="text-sm text-[#6b95b3] mt-1">{{ booking.workerName }}</p>
            </div>
            <span :class="[
              'px-3 py-1 rounded-full text-xs font-medium',
              booking.status === 'Active' ? 'bg-green-100 text-green-700' :
              booking.status === 'Done' ? 'bg-blue-100 text-blue-700' :
              'bg-yellow-100 text-yellow-700'
            ]">
              {{ booking.status }}
            </span>
          </div>

          <div class="space-y-2 mb-4 pb-4 border-b border-gray-100">
            <p class="text-sm text-gray-600">
              <span class="font-medium text-[#112331]">Date & Time:</span> {{ booking.date }}
            </p>
            <p class="text-sm text-gray-600">
              <span class="font-medium text-[#112331]">Amount:</span> ₱{{ booking.price }}
            </p>
          </div>

          <button class="w-full py-2 bg-[#2b516f] text-white font-medium rounded-lg hover:bg-[#1b364d] transition-colors text-sm">
            {{ booking.status === 'Done' ? 'Leave Review' : booking.status === 'Active' ? 'View Details' : 'Confirm' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAppData } from '../../composables/useAppData';

const { bookings } = useAppData();
</script>
