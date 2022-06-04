<template>
    <tr class="bc-table-item" @click="$emit('click', item)">
        <td class="bc-table-item__img-block" width="84px">
            <v-img
                :width="33"
                :src="img"
                lazy-src="https://www-penoplex.ru/assets/images/no_image.jpg"
                aspect-ratio="1"
                contain
            >
                <template #placeholder>
                    <v-row class="fill-height ma-0" align="center" justify="center">
                        <v-progress-circular
                            indeterminate
                            color="primary"
                            :size="20"
                        ></v-progress-circular>
                    </v-row>
                </template>
            </v-img>
        </td>
        <td class="bc-table-item__optimization" width="90px">
            <div class="progress-min d-flex">
                <div
                    class="progress-min__circle"
                    :class="`progress-min__circle_${optimiztion.class}`"
                ></div>
                <div class="progress-min__title">{{ optimiztion.value }} %</div>
            </div>
        </td>
        <td v-if="item.sku" class="bc-table-item__vendor" width="120px">
            <span class="bc-table-item__inner">{{ item.sku }}</span>
        </td>
        <td class="bc-table-item__brand" width="120px">
            <span class="bc-table-item__inner">{{ item.brand }}</span>
        </td>
        <td class="bc-table-item__name">
            <span class="bc-table-item__inner">{{ name }}</span>
        </td>
        <td class="bc-table-item__category" width="300px">
            <span class="bc-table-item__inner">{{ categoryName }}</span>
        </td>
    </tr>
</template>

<script>
    import { mapGetters } from 'vuex';
    export default {
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
        },
        data() {
            return {
                noImg: 'https://www-penoplex.ru/assets/images/no_image.jpg',
            };
        },
        computed: {
            ...mapGetters(['isSelectedMp']),
            indexMp() {
                return this.isSelectedMp.id - 1;
            },
            img() {
                /* eslint-disable */
                const field = ['photo_url', 'image'][this.indexMp];
                return this.item[field] && this.item[field] != 0 ? this.item[field] : this.noImg;
            },
            optimiztion() {
                try {
                    const fields = ['content_optimization', 'optimization'][this.indexMp];
                    const opt = Math.ceil(this.item[fields]);
                    const terms = [!(opt > 84) || 'grenn', !(opt > 34) || 'yellow', 'pink'];

                    return { value: opt, class: terms.find(item => typeof item !== 'boolean') };
                } catch {
                    return { value: 0, class: '' };
                }
            },
            name() {
                const field = ['name', 'title'][this.indexMp];
                return this.item[field];
            },
            category() {
                const field = ['category', 'object'][this.indexMp];
                return this.item[field];
            },
            categoryName() {
                return this.isSelectedMp.id === 1 ? this.category.name : this.category;
            },
            categoryValue() {
                return this.isSelectedMp.id === 1 ? this.category.id : this.category;
            },
        },
    };
</script>

<style lang="scss" scoped>
    .bc-table-item {
        font-size: 12px;
    }
</style>
