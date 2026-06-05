<template>
  <div class="p-4 md:p-8 max-w-7xl mx-auto">
    <div class="space-y-8">
      <div class="space-y-4">
        <h1 class="text-3xl font-bold text-[#112331]">Create Account</h1>
        <p class="text-[#6b95b3]">Join KaAyos today</p>
      </div>

      <div class="max-w-md">
        <div class="bg-white rounded-2xl p-8 shadow-sm space-y-6">
          <div class="space-y-3">
            <label class="block text-sm font-medium text-[#112331]">Full Name</label>
            <input 
              type="text" 
              placeholder="John Doe"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#6b95b3]"
            />
          </div>

          <div class="space-y-3">
            <label class="block text-sm font-medium text-[#112331]">Email</label>
            <input 
              type="email" 
              placeholder="you@example.com"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#6b95b3]"
            />
          </div>

          <div class="space-y-3">
            <label class="block text-sm font-medium text-[#112331]">Password</label>
            <input 
              type="password" 
              placeholder="••••••••"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#6b95b3]"
            />
          </div>

          <button class="w-full bg-[#2b516f] text-white font-medium py-3 rounded-lg hover:bg-[#1b364d] transition-colors">
            Create Account
          </button>

          <div class="text-center text-sm">
            <span class="text-gray-600">Already have an account? </span>
            <router-link to="/login" class="text-[#2b516f] font-medium hover:underline">Sign in</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
