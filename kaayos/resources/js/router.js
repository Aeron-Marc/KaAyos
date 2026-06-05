import { createRouter, createWebHistory } from 'vue-router';
import Layout from './components/Layout.vue';
import HomeownerDashboard from './components/pages/HomeownerDashboard.vue';
import Login from './components/pages/Login.vue';
import Register from './components/pages/Register.vue';
import WorkerDetail from './components/pages/WorkerDetail.vue';
import AIChat from './components/pages/AIChat.vue';
import MapScreen from './components/pages/MapScreen.vue';
import BookingsScreen from './components/pages/BookingsScreen.vue';
import ProviderDashboard from './components/pages/ProviderDashboard.vue';
import ProviderProfile from './components/pages/ProviderProfile.vue';

const routes = [
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  {
    path: '/',
    component: Layout,
    children: [
      { path: '', component: HomeownerDashboard },
      { path: 'worker/:id', component: WorkerDetail },
      { path: 'chat', component: AIChat },
      { path: 'map', component: MapScreen },
      { path: 'bookings', component: BookingsScreen },
      { path: 'provider', component: ProviderDashboard },
      { path: 'provider/bookings', component: BookingsScreen },
      { path: 'provider/profile', component: ProviderProfile },
    ],
  },
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
});
