<template>
    <div class="cell-link">
        <a :href="link" target="_blank" rel="noopener">
            {{ name }}
            <br>
            Арт.: {{ sku }}
        </a>
    </div>
</template>

<script>
    export default {
        name: 'AgCustomCellLink',
        data() {
            return {
                link: '#',
                name: '',
                sku: '',
            };
        },
        created() {
            this.link = this.params.link || '#';
            this.name = this.params.name || '';
            this.sku = this.params.sku || '';
        }
    };
</script>

<style lang="scss" scoped>
    .cell-link {
        width: 100%;
        height: 100%;
        padding: 8px 16px;
        //text-overflow: initial;
        //white-space: normal;
        font-weight: 500;
        font-size: 14px;
        line-height: 19px;
        color: $color-purple-primary-700;

        & a {
            //overflow: hidden;
            display: inline-block;
            max-width: 100%;
            //text-overflow: ellipsis;
            //white-space: nowrap;
            white-space: normal;
        }
    }
</style>
