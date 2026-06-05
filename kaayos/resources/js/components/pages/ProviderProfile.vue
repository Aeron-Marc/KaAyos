<template>
  <div class="p-4 md:p-8 max-w-7xl mx-auto">
    <router-link to="/provider" class="text-sm text-[#6b95b3] hover:text-[#2b516f] font-medium mb-6 inline-block">← Back to Dashboard</router-link>
    
    <div class="max-w-2xl">
      <h1 class="text-3xl font-bold text-[#112331] mb-8">My Profile</h1>

      <div class="bg-white rounded-2xl p-8 shadow-sm space-y-8">
        <!-- Profile Header -->
        <div class="flex flex-col md:flex-row gap-6 items-start md:items-center pb-8 border-b border-gray-200">
          <img 
            src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=300&q=80"
            alt="Profile"
            class="w-24 h-24 rounded-2xl object-cover"
          />
          <div class="flex-1">
            <h2 class="text-2xl font-bold text-[#112331]">Juan Dela Cruz</h2>
            <p class="text-[#6b95b3] mt-1">Professional Plumber • Verified</p>
            <div class="flex gap-4 mt-4">
              <button class="bg-[#2b516f] text-white px-6 py-2 rounded-lg hover:bg-[#1b364d] transition-colors font-medium">
                Edit Profile
              </button>
              <button class="border border-[#6b95b3] text-[#2b516f] px-6 py-2 rounded-lg hover:bg-[#e8f0f5] transition-colors font-medium">
                Upload Photo
              </button>
            </div>
          </div>
        </div>

        <!-- About Section -->
        <div class="space-y-4">
          <h3 class="text-lg font-bold text-[#112331]">About</h3>
          <textarea 
            class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#6b95b3]"
            rows="4"
            placeholder="Tell customers about yourself..."
          >Experienced plumber with over 10 years of service. Fast, reliable, and guarantees high-quality work.</textarea>
        </div>

        <!-- Services Section -->
        <div class="space-y-4">
          <h3 class="text-lg font-bold text-[#112331]">Services Offered</h3>
          <div class="space-y-3">
            <div v-for="service in services" :key="service" class="flex items-center gap-3">
              <input type="checkbox" :id="service" checked class="w-4 h-4 rounded" />
              <label :for="service" class="text-[#112331]">{{ service }}</label>
            </div>
          </div>
        </div>

        <!-- Pricing Section -->
        <div class="space-y-4">
          <h3 class="text-lg font-bold text-[#112331]">Hourly Rate</h3>
          <div class="flex items-center gap-3">
            <span class="text-[#112331] font-medium">₱</span>
            <input 
              type="number" 
              value="450"
              class="flex-1 px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#6b95b3]"
            />
            <span class="text-[#6b95b3] text-sm">/hour</span>
          </div>
        </div>

        <!-- Save Button -->
        <button class="w-full bg-[#2b516f] text-white font-medium py-3 rounded-lg hover:bg-[#1b364d] transition-colors">
          Save Changes
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const services = [
  'Pipe Fixing',
  'Water Heater Installation',
  'Drain Unblocking',
  'Leak Detection',
];
</script>
