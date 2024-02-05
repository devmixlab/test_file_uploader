import '../bootstrap';

import { createApp, h, DefineComponent } from 'vue';

import App from './components/App.vue';

let globalMixin = {
    methods: {
        route(name) {
            return route(name);
        },
    }
}

createApp({ render: () => h(App, {}) })
    .mixin(globalMixin)
    .mount(document.getElementById('fileUploader'));
