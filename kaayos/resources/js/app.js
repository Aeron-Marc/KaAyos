import { createApp } from 'vue';
import { router } from './router';
import App from './App.vue';
import '../css/app.css';
import './echo';

const app = createApp(App);

app.use(router);
app.mount('#app');
