import Vue from 'vue';
import ReactiveProvide from 'vue-reactive-provide';

// not necessary when used as a mixin, see below for details
Vue.use(ReactiveProvide);

// overwrite the option's name:
Vue.use(ReactiveProvide, {
    name: 'reactiveProvide', // default value
});
