<template>
  <div class="flex h-screen w-full bg-[#e8f0f5] font-sans text-[#112331]">
    <!-- Desktop Sidebar -->
    <aside class="hidden md:flex w-64 flex-col bg-[#112331] text-white transition-all duration-300">
      <div class="p-6 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-[#6b95b3] flex items-center justify-center font-mono font-bold text-xl">K</div>
          <span class="font-mono font-bold text-xl tracking-tight">KaAyos</span>
        </div>
      </div>

      <div class="px-6 py-4">
        <button 
          @click="toggleRole"
          class="w-full flex items-center justify-between px-4 py-2 bg-[#1b364d] rounded-xl text-sm font-medium hover:bg-[#2b516f] transition-colors"
        >
          <div class="flex items-center gap-2">
            <component :is="role === 'homeowner' ? Home : Briefcase" :size="16" />
            <span>{{ role === 'homeowner' ? 'Homeowner View' : 'Worker View' }}</span>
          </div>
        </button>
      </div>

      <nav class="flex-1 px-4 py-4 space-y-2">
        <router-link
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200"
          :class="{ 'bg-[#2b516f] text-white font-medium shadow-md': isActive(link.to), 'text-gray-400 hover:bg-[#1b364d] hover:text-white': !isActive(link.to) }"
        >
          <component :is="link.icon" :size="20" />
          <span>{{ link.label }}</span>
        </router-link>
      </nav>

      <div class="p-4 border-t border-[#1b364d]">
        <div class="flex items-center gap-3 px-4 py-2">
          <img 
            src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&q=80" 
            alt="User" 
            class="w-10 h-10 rounded-full border-2 border-[#2b516f] object-cover"
          />
          <div class="flex-1">
            <p class="text-sm font-medium">Maria Reyes</p>
            <router-link to="/login" class="text-xs text-gray-400 hover:text-white flex items-center gap-1 mt-1 transition-colors">
              <LogOut :size="12" /> Sign out
            </router-link>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
      <!-- Mobile Header -->
      <header class="md:hidden flex items-center justify-between p-4 bg-white shadow-sm z-10">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-[#2b516f] flex items-center justify-center font-mono text-white font-bold text-lg">K</div>
          <span class="font-mono font-bold text-lg tracking-tight text-[#112331]">KaAyos</span>
        </div>
        
        <div class="flex items-center gap-3">
          <button @click="toggleRole" class="text-xs font-medium px-2 py-1 bg-[#e8f0f5] rounded-md text-[#2b516f]">
            {{ role === 'homeowner' ? 'Homeowner' : 'Worker' }}
          </button>
          <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="p-2 -mr-2 text-[#112331]">
            <component :is="isMobileMenuOpen ? X : Menu" :size="24" />
          </button>
        </div>
      </header>

      <!-- Mobile Menu Overlay -->
      <div v-if="isMobileMenuOpen" class="md:hidden absolute top-[72px] left-0 right-0 bg-white shadow-lg z-20 border-b border-gray-100">
        <nav class="p-4 flex flex-col gap-2">
          <div class="flex items-center gap-3 p-3 mb-4 bg-[#e8f0f5] rounded-xl">
            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&q=80" alt="User" class="w-12 h-12 rounded-full object-cover" />
            <div>
              <p class="font-semibold text-[#112331]">Maria Reyes</p>
              <router-link to="/login" @click="isMobileMenuOpen = false" class="text-sm text-[#6b95b3] flex items-center gap-1 mt-0.5">
                <LogOut :size="14" /> Sign out
              </router-link>
            </div>
          </div>
          <router-link
            v-for="link in links"
            :key="link.to"
            :to="link.to"
            @click="isMobileMenuOpen = false"
            class="flex items-center gap-3 p-4 rounded-xl"
            :class="{ 'bg-[#2b516f] text-white font-medium': isActive(link.to), 'text-[#1b364d] hover:bg-[#e8f0f5]': !isActive(link.to) }"
          >
            <component :is="link.icon" :size="20" />
            <span>{{ link.label }}</span>
          </router-link>
        </nav>
      </div>

      <!-- Scrollable Content -->
      <div class="flex-1 overflow-y-auto pb-20 md:pb-0">
        <router-view />
      </div>

      <!-- Mobile Bottom Navigation -->
      <nav v-if="role === 'homeowner'" class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-6 py-3 flex justify-between items-center z-10">
        <router-link
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="flex flex-col items-center gap-1 transition-colors"
          :class="{ 'text-[#2b516f]': isActive(link.to), 'text-gray-400': !isActive(link.to) }"
        >
          <component :is="link.icon" :size="24" :class="{ 'fill-[#2b516f] stroke-[#2b516f]': isActive(link.to) }" />
          <span class="text-[10px] font-medium">{{ link.label }}</span>
        </router-link>
      </nav>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { Home, Search, MessageSquare, Map as MapIcon, Calendar, User, Settings, LogOut, Menu, X, Briefcase } from 'lucide-vue';

const route = useRoute();
const isMobileMenuOpen = ref(false);
const role = ref('homeowner');

const homeownerLinks = [
  { to: '/', icon: Search, label: 'Browse' },
  { to: '/chat', icon: MessageSquare, label: 'AI Chat' },
  { to: '/map', icon: MapIcon, label: 'Nearby Map' },
  { to: '/bookings', icon: Calendar, label: 'Bookings' },
];

const providerLinks = [
  { to: '/provider', icon: Home, label: 'Dashboard' },
  { to: '/provider/bookings', icon: Calendar, label: 'Job Requests' },
  { to: '/provider/profile', icon: User, label: 'My Profile' },
];

const links = computed(() => role.value === 'homeowner' ? homeownerLinks : providerLinks);

const toggleRole = () => {
  role.value = role.value === 'homeowner' ? 'provider' : 'homeowner';
};

const isActive = (path) => {
  if (path === '/') {
    return route.path === '/' || route.path === '/provider';
  }
  return route.path.startsWith(path);
};
</script>
