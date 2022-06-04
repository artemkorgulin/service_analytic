export default {
    props: {
        selectAll: {
            type: Boolean,
            default: false,
        },
        items: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            //
        };
    },
    watch: {
        //
    },
    filters: {
        percentSale(item) {
            const result =
                Math.round(
                    (1 -
                        Number(item.price.replace(',', '')) /
                            Number(item.old_price.replace(',', ''))) *
                        10000
                ) / 100;
            return String(result).replace('.', ',');
        },
        saleValue(item) {
            const result =
                Number(item.price.replace(',', '')) - Number(item.old_price.replace(',', ''));
            return result.toLocaleString().replace(/,/g, ' ');
        },
        optimizePercent(val) {
            if (typeof val !== 'number') {
                return parseInt(val, 10);
            }
            return Math.ceil(Number(val));
        },
        optimizeClass(val) {
            const data = Math.ceil(Number(val));

            if (data > 84) {
                return 'progress--green';
            } else if (data > 34) {
                return 'progress--yellow';
            } else {
                return 'progress--pink';
            }
        },
    },
    computed: {
        //
    },
};
