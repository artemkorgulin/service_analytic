export default {
    filters: {
        repDotWithCom(value) {
            return String(value).replace(/\./, ',');
        },
        divByCat(value) {
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        },
    },
};
