import Vue from "vue";
import App from "./App.vue";
import router from "./router";
import store from "./store";
import axios from "axios";
import Notifications from 'vue-notification';
import 'bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import './assets/css/style.css';

Vue.use(Notifications);

const axiosConfig = {
    baseURL: 'http://127.0.0.1:8000/api/',
    timeout: 30000
};

Vue.prototype.$axios = axios.create(axiosConfig);

Vue.config.productionTip = false;

new Vue({
    router,
    store,
    render: h => h(App)
}).$mount("#app");