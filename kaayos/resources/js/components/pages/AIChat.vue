<template>
  <div class="p-4 md:p-8 max-w-7xl mx-auto">
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold text-[#112331]">AI Chat Assistant</h1>
        <p class="text-[#6b95b3] mt-2">Get help finding the right service</p>
      </div>

      <div class="bg-white rounded-2xl p-6 shadow-sm h-96 flex flex-col">
        <div class="flex-1 overflow-y-auto mb-4 space-y-4">
          <div class="flex justify-start">
            <div class="bg-[#e8f0f5] rounded-xl rounded-tl-none p-4 max-w-xs">
              <p class="text-[#112331]">Hi! I'm here to help you find the perfect service provider. What do you need help with?</p>
            </div>
          </div>
          <div class="flex justify-end">
            <div class="bg-[#2b516f] text-white rounded-xl rounded-tr-none p-4 max-w-xs">
              <p>I'm looking for a plumber in Quezon City</p>
            </div>
          </div>
          <div class="flex justify-start">
            <div class="bg-[#e8f0f5] rounded-xl rounded-tl-none p-4 max-w-xs">
              <p class="text-[#112331]">Great! I found several plumbers near you. Would you like to see their profiles?</p>
            </div>
          </div>
        </div>

        <div class="flex gap-2">
          <input 
            type="text"
            placeholder="Type your message..."
            class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#6b95b3]"
          />
          <button class="bg-[#2b516f] text-white p-2 rounded-lg hover:bg-[#1b364d] transition-colors">
            Send
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
