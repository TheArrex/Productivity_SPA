import './styles/app.css'
import './bootstrap'
import Vue from 'vue'
import Vuetify from 'vuetify'
import 'vuetify/dist/vuetify.min.css'
import moment from 'moment'
import router from './router'

import App from './App'

Vue.use(Vuetify)
Vue.prototype.$moment = moment;

new Vue({
    vuetify : new Vuetify(),
    el: '#app',
    router: router,
    render: h => h(App)
});

