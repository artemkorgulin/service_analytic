<template>
    <draggable v-model="keyWords" :class="$style.KeyWordsDraggable">
        <KeyWordItem
            v-for="keyword in keyWords"
            :key="keyword.id"
            :keyword-object="keyword"
            :index="index"
        />
    </draggable>
</template>

<script>
    import draggable from 'vuedraggable';

    export default {
        name: 'KeyWordsDraggable',
        components: {
            draggable,
        },
        props: {
            items: {
                type: Array,
                required: true,
            },
            index: {
                type: Number,
                required: true,
            },
        },
        computed: {
            keyWords: {
                get() {
                    return this.items;
                },
                set(val) {
                    this.$emit('drag', {
                        index: this.index,
                        payload: val,
                    });
                },
            },
        },
    };
</script>

<style lang="scss" module>
    .KeyWordsDraggable {
        display: flex;
        flex-direction: column;
        gap: size(8);
    }
</style>
