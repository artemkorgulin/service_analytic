import vue from 'vue';
import Echo from 'laravel-echo';

window.io = require('socket.io-client');

export default ({ $config }) => {
    vue.prototype.$Echo = new Echo({
        broadcaster: 'socket.io',
        host: $config.WS_EVENT_URL || '',
        transports: ['websocket'],
    });
};
