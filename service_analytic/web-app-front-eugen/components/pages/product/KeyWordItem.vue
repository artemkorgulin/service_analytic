<template>
    <div
        class="kw-item"
        :class="{ active: keywordObject.isActive }"
        @mouseover="hover = true"
        @mouseleave="hover = false"
        @click="handleClick"
    >
        <div class="kw-item__icon d-flex align-center flex-column mr-2">
            <SvgIcon
                color="#7E8793"
                :name="keywordObject.isActive ? 'outlined/tick' : 'outlined/plus'"
            />
        </div>
        <div class="kw-item__text">{{ keywordObject.name }}</div>
        <div class="kw-item__pop mr-2">{{ keywordObject.popularity }}</div>
        <div class="kw-item__actions d-flex align-center flex-column">
            <v-btn ref="delBtn" icon :disable="keywordObject.isActive">
                <SvgIcon
                    color="#7e8793"
                    name="filled/trashDelete"
                    style="width: 16px; height: 16px"
                />
            </v-btn>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'KeyWordItem',
        props: {
            keywordObject: {
                type: Object,
                required: true,
            },
            index: {
                type: Number,
                required: true,
            },
        },
        data() {
            return {
                hover: false,
            };
        },
        computed: {
            ...mapGetters({
                getActiveField: 'product/getActiveField',
            }),
            keywordData() {
                return {
                    index: this.index,
                    keywordObject: this.keywordObject,
                };
            },
        },
        methods: {
            ...mapActions({
                handleKeywordDelete: 'product/handleKeywordDelete',
                setKeywordActiveness: 'product/setKeywordActiveness',
                setActiveFieldNewValue: 'product/setActiveFieldNewValue',
            }),
            handleClick(event) {
                const { delBtn } = this.$refs;
                if (delBtn.$el.contains(event.target)) {
                    this.$emit('del', this.keywordObject);
                    return;
                }
                this.$emit('click', this.keywordObject);
            },
        },
    };
</script>
<style lang="scss" scoped>
    .kw-item {
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 48px;
        padding: 0 24px;
        font-size: 14px;
        cursor: pointer;

        &:hover {
            background: $selected-item-color;
        }

        &__text {
            flex: 1 auto;
            padding-bottom: 2px;
        }

        &__icon {
            font-size: 14px;
        }

        &__pop {
            font-weight: bold;
        }

        // &.active &__text,
        // &.active &__pop,
        // &.active &__icon {
        //     opacity: 0.4;
        // }

        &.active &__icon * {
            fill: #7e8793 !important;
            stroke: #7e8793 !important;
        }
    }
</style>
