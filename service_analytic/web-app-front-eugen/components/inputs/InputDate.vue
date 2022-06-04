<template>
    <div id="datePicker" :class="$style.SpecDate">
        <div
            v-if="persistentLabel && label && label.length && label.trim().length"
            :class="errors && errors.length && 'error--text'"
        >
            {{ label }}
        </div>
        <VTextField
            id="datePickerInput"
            ref="datePickerInput"
            class="light-outline"
            outlined
            dense
            :hide-details="hideDetails"
            :value="displayedDate"
            :label="!persistentLabel ? label : ''"
            background-color="#fff"
            readonly
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
        <VMenu
            ref="menu"
            :close-on-content-click="false"
            transition="scale-transition"
            max-width="100%"
            min-width="auto"
            content-class="date-picker-content"
            activator="#datePickerInput"
            attach="#datePicker"
            @input="menuChange"
        >
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
        name: 'InputDate',
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
                default: false,
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
                if (typeof this.value === 'string') {
                    return this.value;
                }
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
                this.$emit('input', val.sort());
            },
            handleClear() {
                this.$emit('input', []);
                this.$refs.datePickerInput.blur();
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .SpecDate {
        position: relative;

        --btn-font-weight: normal;

        :global(.date-picker-content) {
            top: unset !important;
            bottom: 0;
            min-width: 300px !important;
            max-width: unset !important;
            transform: translateY(100%);

            :global(.theme--light.v-btn.v-btn--disabled) {
                color: rgba(0, 0, 0, 0.44) !important;
            }

            :global(.theme--light.v-btn.v-btn--disabled.accent) {
                background-color: rgba($accent, 0.18) !important;
            }
        }
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
