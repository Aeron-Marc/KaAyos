import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const key = import.meta.env.VITE_REVERB_APP_KEY;
if (key) {
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        host: import.meta.env.VITE_REVERB_HOST + ':' + import.meta.env.VITE_REVERB_PORT,
        scheme: import.meta.env.VITE_REVERB_SCHEME ?? 'http',
        useTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    });
}
