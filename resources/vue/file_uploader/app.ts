import '../bootstrap';

import { createApp, h, DefineComponent } from 'vue';

import App from './components/App.vue';

createApp({ render: () => h(App, {
        foo: 'foo_variable',
    }) })
    .mount(document.getElementById('fileUploader'));
