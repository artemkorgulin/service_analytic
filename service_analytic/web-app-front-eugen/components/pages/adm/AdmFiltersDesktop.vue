<template>
    <div :class="$style.AdmFiltersDesktop">
        <div v-if="!!$slots.prepend" :class="$style.filter">
            <slot name="prepend" />
        </div>

        <div v-if="options.campaign_statuses" :class="$style.filter">
            <LazyVFilter
                :value="values.campaign_statuses"
                :options="options.campaign_statuses"
                label-field="name"
                value-field="id"
                label="Статус кампании"
                @change="val => $emit('change', { field: 'campaign_statuses', item: val })"
            />
        </div>
        <div v-if="options.campaign_placements" :class="$style.filter">
            <LazyVFilter
                :value="values.campaign_placements"
                :options="options.campaign_placements"
                label-field="description"
                value-field="id"
                label="Тип рекламных кампаний"
                @change="val => $emit('change', { field: 'campaign_placements', item: val })"
            />
        </div>
        <div v-if="options.campaign_payment_types" :class="$style.filter">
            <LazyVFilter
                :value="values.campaign_payment_types"
                :options="options.campaign_payment_types"
                label-field="name"
                value-field="id"
                label="Тип оплаты"
                @change="val => $emit('change', { field: 'campaign_payment_types', item: val })"
            />
        </div>
        <div v-if="options.strategy_types" :class="$style.filter">
            <LazyVFilter
                :value="values.strategy_types"
                :options="options.strategy_types"
                label-field="name"
                value-field="id"
                label="Тип стратегии"
                @change="val => $emit('change', { field: 'strategy_types', item: val })"
            />
        </div>
        <div v-if="options.strategy_statuses" :class="$style.filter">
            <LazyVFilter
                :value="values.strategy_statuses"
                :options="options.strategy_statuses"
                label-field="name"
                value-field="id"
                label="Статус стратегии"
                @change="val => $emit('change', { field: 'strategy_statuses', item: val })"
            />
        </div>
        <slot name="append" />
    </div>
</template>

<script>
    export default {
        name: 'AdmFiltersDesktop',
        props: {
            values: {
                type: Object,
                default: () => ({}),
            },
            options: {
                type: Object,
                default: () => ({}),
            },
        },
    };
</script>

<style lang="scss" module>
    .AdmFiltersDesktop {
        @extend %sheet;

        box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);
        display: flex;
        // margin-top: $page-gap;
        margin-right: -4px;
        // margin-bottom: $page-gap;
        margin-left: -4px;
        padding: 16px;
        gap: 0.8rem;

        @include respond-to(lg) {
            flex-wrap: wrap;
            gap: unset;
        }

        @include respond-to(md) {
            display: none;
        }

        .filter {
            flex: 1 1 20%;
            padding: 4px;

            @include respond-to(lg) {
                flex-basis: 50%;
                max-width: 50%;
            }

            @include respond-to(sm) {
                flex-basis: 100%;
                max-width: 100%;
            }
        }

        @include respond-to(md) {
            flex-wrap: wrap;
        }
    }
</style>
