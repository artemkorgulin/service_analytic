import store from './store';
import moment from 'moment';
import '@fortawesome/fontawesome-free/css/all.css';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';


import router from './router';
import Vue from "vue";
import VueRouter from "vue-router";

import Toasted from 'vue-toasted';
import Multiselect from 'vue-multiselect'
import Toast from "vue-toastification";
// Import the CSS or use your own!
import "vue-toastification/dist/index.css";
const options = {
    // You can set your default options here
};






require('./bootstrap');
window.Vue = require('vue').default;
require('./components_list');


moment.locale('ru');
Vue.prototype.moment = moment;
Vue.use(Loading);
Vue.use(VueRouter);
Vue.use(Toasted);
Vue.use(require('vue-resource'));
Vue.component('pagination', require('laravel-vue-pagination'));
Vue.use(Toast, options);



window.vueApp = new Vue({
    el: '#admin-app',
    store,
    router

});

