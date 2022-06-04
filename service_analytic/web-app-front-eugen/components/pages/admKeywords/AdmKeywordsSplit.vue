<template>
    <span :class="$style.AdmKeywordsSplit">
        <template v-if="nameSplitted.length">
            <span
                v-for="chunk in nameSplitted"
                :key="value + chunk.text"
                :class="[$style.chunk, !chunk.isActive && $style.notActive]"
                @click="$emit('addStopword', chunk.text)"
                v-text="chunk.text"
            />
        </template>
        <template v-else>
            {{ value }}
        </template>
    </span>
</template>
<script>
    import { defineComponent } from '@nuxtjs/composition-api';

    export default defineComponent({
        name: 'AdmKeywordsSplit',
        // functional: true,
        props: {
            value: {
                type: String,
                default: '',
            },
            list: {
                type: Array,
                default: () => [],
            },
        },
        computed: {
            nameSplitted() {
                if (!this.value) {
                    return [];
                }
                const splitRes = this.value.split(' ');
                if (splitRes.length === 1) {
                    return [];
                }
                if (!this?.list?.length) {
                    return splitRes.map(item => ({ text: item, isActive: true }));
                }
                return splitRes.map(item => ({ text: item, isActive: !this.list.includes(item) }));
            },
            // nameResult(){
            //     if(!this?.list?.length){
            //         return this.nameSplitted
            //     }
            //     return this.nameSplitted.map()
            // }
        },
    });
</script>

<style lang="scss" module>
    .AdmKeywordsSplit {
        //
    }

    .chunk {
        transition: $primary-transition;

        &:hover {
            background-color: $base-400;
            text-decoration: line-through;
            cursor: pointer;
        }

        &.notActive {
            background-color: $base-400;
            text-decoration: line-through;
            cursor: unset;
            pointer-events: none;
        }

        &:after {
            content: ' ';
        }
    }
</style>
