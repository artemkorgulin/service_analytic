import Vue from 'vue';
import Notifications from '~notify/Notify.vue';

/* eslint-disable no-unused-expressions,no-use-before-define,brace-style */

class Notify {
    constructor(options) {
        this.options = options;
        this.__vm = new Vue(Notifications);
        this.create = function(opts) {
            return this.__vm.add(opts, this.options);
        };
        this.create.bind(this)
        this.registerType = function(typeName, typeOpts) {
            this.options.types[typeName] = typeOpts;
        };
        this.closeAll = function() {
            return this.__vm.closeAll();
        };
    }
}

export default function({app}, inject) {
    // eslint-disable-next-line
    /* tslint:disable-next-line */
    const options = <%= serialize(options) %>;
    const notifyPlugin = new Notify(options);
    app.$notify = notifyPlugin;
    inject('notify', notifyPlugin);
    if (!app.mixins) {
        app.mixins = [];
    }
    app.mixins.push({
        mounted() {
            const node = document.createElement('div');
            document.body.appendChild(node);
            notifyPlugin.__vm.$mount(node);
        },
    });
}
