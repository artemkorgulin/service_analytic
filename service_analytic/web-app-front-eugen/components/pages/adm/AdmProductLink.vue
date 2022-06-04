<template>
    <a class="adm-product-link" target="_blank" :href="hrefLink">
        {{ sku }}
        <SvgIcon class="adm-product-link_icon" name="outlined/link" />
    </a>
</template>

<script>
    export default {
        name: 'AdmProductLink',
        props: {
            sku: {
                type: [String, Number],
                default: '',
            },
            marketplace: {
                type: String,
                default: 'ozon',
            },
        },
        computed: {
            hrefLink() {
                return this.marketplace === 'wb'
                    ? `https://www.wildberries.ru/catalog/${this.sku}/detail.aspx`
                    : `https://www.ozon.ru/context/detail/id/${this.sku}`;
            },
        },
    };
</script>
