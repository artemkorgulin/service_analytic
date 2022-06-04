import Vue from 'vue';
import mitt from 'mitt';
export default ({ app }, inject) => {
    const emitter = mitt();

    const contextMenu = {
        data: Vue.observable({
            show: false,
            options: { x: 0, y: 0, attach: null },
            heading: '',
            item: {},
            items: [],
        }),
        emitter,
        open(component, data) {
            this.emitter.emit('openContextMenu', component, data);
        },
        close() {
            this.emitter.emit('closeContextMenu');
        },
        change(prop, value) {
            Vue.set(this.data, prop, value);
        },
    };
    app.$contextMenu = contextMenu;
    inject('contextMenu', contextMenu);
    const modal = {
        emitter,
        open(component, data) {
            this.emitter.emit('openModal', component, data);
        },
        close() {
            this.emitter.emit('closeModal');
        },
    };
    app.$modal = modal;
    inject('modal', modal);

    app.$emitter = emitter;
    inject('emitter', emitter);
};
