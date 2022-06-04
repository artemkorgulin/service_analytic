<template>
    <div :class="$style.InputPromocode">
        <div :class="$style.InputWrapper">
            <VTextField
                v-model="input"
                :class="$style.Input"
                label="Промокод"
                dense
                outlined
                hide-details
                clearable
            />
            <VBtn
                :class="$style.buttonApply"
                outlined
                :disabled="!input"
                @click="setPromocode(input)"
            >
                Применить
            </VBtn>
        </div>
        <div :class="$style.InputMessage">
            <SvgIcon :class="$style.InputMessageIcon" name="outlined/tick" />
            <span>Промокод активирован</span>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'InputPromocode',
        data() {
            return {
                input: null,
            };
        },
        computed: {
            ...mapGetters({
                getPromocodeEntered: 'tariffs/getPromocodeEntered',
            }),
            // input: {
            //     get() {
            //         return this.getPromocodeEntered;
            //     },
            //     set(val) {
            //         this.setPromocode(val);
            //     },
            // },
        },
        methods: {
            ...mapActions({
                setPromocode: 'tariffs/setPromocode',
            }),
        },
    };
</script>

<style lang="scss" module>
    .InputPromocode {
        display: flex;
        flex-direction: column;
        gap: size(6);
    }

    .InputWrapper {
        display: flex;
        gap: size(10);
    }

    .buttonApply {
        color: $color-gray-light-100;
    }

    .InputMessage {
        display: flex;
        align-items: center;
        gap: size(5);
        font-size: size(12);
        font-weight: bold;
        line-height: 1.4;
        color: $success;
    }

    .InputMessageIcon {
        max-width: size(20);
        max-height: size(20);
    }
</style>
