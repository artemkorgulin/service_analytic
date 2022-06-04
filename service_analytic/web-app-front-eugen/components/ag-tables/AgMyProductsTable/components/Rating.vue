<template>
    <VMenu offset-y open-on-hover top :nudge-bottom="-10">
        <template #activator="{ on, attrs }">
            <div class="product-table__rating border-hover" v-bind="attrs" v-on="on">
                <v-rating
                    :class="$style.ratingStars"
                    :color="colors.primaryPurple"
                    :length="displayOptionBig ? 5 : 1"
                    :value="rating"
                    half-increments
                    dense
                    readonly
                    size="15"
                />
                <span class="number">{{ rating }}</span>
            </div>
        </template>
        <VCard class="custom-popover">
            <div class="custom-popover-rating">
                <v-rating
                    :class="$style.ratingStars"
                    :color="colors.primaryPurple"
                    length="5"
                    :value="rating"
                    half-increments
                    dense
                    readonly
                    size="15"
                />
                <span class="number">{{ rating }}</span>
            </div>
        </VCard>
    </VMenu>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        name: 'Rating',
        data() {
            return {
                colors: {
                    primaryPurple: '#710bff',
                    black: '#2f3640',
                },
            };
        },
        computed: {
            ...mapGetters({
                displayOptionBig: 'products/GET_DISPLAY_OPTION',
            }),
            rating() {
                return this.params.value;
            }
        },
    };
</script>

<style lang="scss" module>
    .ratingStars {
        display: flex;

        &:global(.v-rating) {
            display: flex;
            align-items: center;

            &:global(.v-rating--dense) {
                & :global(.v-icon) {
                    padding: 0;
                }
            }
        }
    }
</style>

<style lang="scss">
    /* stylelint-disable declaration-no-important */
    .custom-popover-rating {
        display: flex;
        align-items: center;

        &:not(:first-child) {
            margin-top: 4px;
        }

        .el-rate {
            display: flex;
            align-items: center;
            pointer-events: none;

            &__item {
                display: flex;
                font-size: 16px;
            }
        }

        .number {
            margin-left: 8px;
            font-size: 14px;
            color: $color-main-font !important;
            font-weight: 500;
        }
    }
</style>
