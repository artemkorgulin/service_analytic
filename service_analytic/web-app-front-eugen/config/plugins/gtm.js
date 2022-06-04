export default ({ app }, inject) => {
    inject('sendGtm', event => {
        const isObject = typeof event === 'object';
        window.dataLayer.push(isObject ? event : { event });
    });
};
