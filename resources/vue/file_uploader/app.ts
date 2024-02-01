import '../bootstrap';

import { createApp, h, DefineComponent } from 'vue';

import App from './components/App.vue';

createApp({ render: () => h(App, {}) })
    .mount(document.getElementById('fileUploader'));
