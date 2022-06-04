<template>
    <div id="datePicker" :class="$style.InputDateWithActivator">
        <VMenu
            ref="menu"
            :close-on-content-click="false"
            content-class="date-picker-content"
            offset-y
            min-width="auto"
            max-width="300px"
            transition="scale-transition"
            @input="menuChange"
        >
            <template #activator="{ on, attrs }">
                <div
                    v-if="persistentLabel && label && label.length && label.trim().length"
                    class="responsiveLabel"
                    :class="errors && errors.length && 'error--text'"
                >
                    {{ label }}
                </div>
                <VTextField
                    id="datePickerInput"
                    ref="datePickerInput"
                    outlined
                    dense
                    :hide-details="hideDetails"
                    :value="displayedDate"
                    :label="!persistentLabel ? label : ''"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                >
                    <template #append>
                        <SvgIcon
                            v-if="isDirty"
                            :class="$style.clear"
                            name="outlined/close"
                            @click="handleClear"
                        />
                        <SvgIcon
                            :class="$style.focusBtn"
                            :color="isMenu ? 'accent' : ''"
                            name="outlined/calendar"
                            @click="handleFocus"
                        />
                    </template>
                </VTextField>
            </template>
            <VDatePicker
                v-model="lazyValue"
                no-title
                range
                scrollable
                full-width
                show-adjacent-months
                :min="facet.min"
                :max="facet.max"
                @change="handleChange"
            />
        </VMenu>
    </div>
</template>

<script>
    export default {
        name: 'InputDateWithActivator',
        props: {
            value: {
                type: Array,
                default: () => [],
            },
            label: {
                type: String,
                default: '',
            },
            facet: {
                type: [Object, Array],
                default: () => ({}),
            },
            persistentLabel: {
                type: Boolean,
                default: true,
            },
            hideDetails: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                date: [],
                isMenu: false,
                lazyValue: this.value,
                errors: [],
            };
        },
        computed: {
            displayedDate() {
                return this.value.reduce((acc, val, i, arr) => {
                    const formattedDate = this.$options.filters.formatDateTime(val, '$d.$m.$y');
                    if (arr?.length === 2) {
                        if (i === 0) {
                            acc += 'от ' + formattedDate;
                        } else if (i === 1) {
                            acc += ' до ' + formattedDate;
                        }
                    } else {
                        acc += formattedDate;
                    }
                    return acc;
                }, '');
            },
            isDirty() {
                return this.value?.toString().length > 0;
            },
        },
        watch: {
            value(val) {
                this.lazyValue = val;
            },
        },
        methods: {
            handleFocus() {
                if (!this.$refs.menu.isActive) {
                    this.$refs.menu.isActive = true;
                }
            },
            menuChange(val) {
                this.isMenu = val;
            },
            handleChange(val) {
                this.$emit('change', val.sort());
            },
            handleClear() {
                this.$emit('change', []);
                this.$refs.datePickerInput.blur();
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .InputDateWithActivator {
        position: relative;

        --btn-font-weight: normal;
    }

    .clear,
    .focusBtn {
        cursor: pointer;

        &:hover {
            color: $accent;
        }
    }

    .focusBtn {
        margin-left: 12px;
        user-select: none;
    }
</style>
