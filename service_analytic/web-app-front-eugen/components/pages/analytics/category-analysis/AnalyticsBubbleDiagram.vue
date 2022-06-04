<template>
    <div class="diagram" @mouseover="hover = true" @mouseleave="hover = false">
        <div v-if="cellValue" class="diagram-inner" :style="computedStyle"></div>
        <template v-if="hover">
            <div class="diagram-legend-pointer" :style="legendPointerPosition"></div>
            <div class="diagram-legend" :style="legendPosition">
                <div class="diagram-legend__text-small">{{ legendText.brand }}</div>
                <div class="diagram-legend__text-small">{{ legendText.category }}</div>
                <div class="diagram-legend__text-big">{{ legendText.value | splitThousands }}</div>
            </div>
        </template>
    </div>
</template>

<script>
    /* eslint-disable no-extra-parens, no-unused-expressions */
    import { mapGetters, mapState } from 'vuex';

    export default {
        name: 'AnalyticsBubbleDiagram',
        data: function () {
            return {
                hover: false,
                cellValue: null,
                bubbleSizeConstatnts: {
                    min: 8,
                    max: 112,
                },
                colorsBase: ['234, 94, 127', '164, 111, 232', '76, 203, 134', '241, 224, 64'],
            };
        },
        computed: {
            ...mapGetters('category-analysis', ['getSelectedTabName']),
            ...mapState('category-analysis', { valuesRange: 'valuesRange' }),
            computedStyle() {
                const color = this.colorsBase[this.columnIndex % this.colorsBase.length];
                return {
                    width: `${this.getBubbleSize}px`,
                    height: `${this.getBubbleSize}px`,
                    backgroundColor: `rgba(${color}, ${this.hover ? '0.5' : '0.08'})`,
                    borderColor: `rgba(${color}, 0.5)`,
                };
            },
            minMaxValues() {
                return {
                    min: this.valuesRange.min[this.getSelectedTabName] || 0,
                    max: this.valuesRange.max[this.getSelectedTabName] || 0,
                };
            },
            getBubbleSize() {
                if (this.cellValue) {
                    const currentValToMaxVal =
                        (this.cellValue - this.minMaxValues.min) /
                        (this.minMaxValues.max - this.minMaxValues.min);
                    return (
                        (this.bubbleSizeConstatnts.max - this.bubbleSizeConstatnts.min) *
                            currentValToMaxVal +
                        this.bubbleSizeConstatnts.min
                    );
                } else {
                    return 0;
                }
            },
            columnIndex() {
                const fieldsName = this.params.colDef.field;
                const fieldsNamesArr = this.params.columnApi.columnModel.columnDefs.reduce(
                    (acc, current) => {
                        if (current.field !== 'subject_name') {
                            acc.push(current.field);
                        }
                        return acc;
                    },
                    []
                );
                return fieldsNamesArr.indexOf(fieldsName);
            },
            legendText() {
                return {
                    brand: this.params.brand.brand,
                    category: this.params.data.subject_name,
                    value: this.cellValue || 'Нет',
                };
            },
            legendPosition() {
                const style = {
                    left: `calc(50% + ${this.getBubbleSize / 2 + 18}px)`,
                };

                if (this.params.node.childIndex === 0) {
                    style.top = '2px';
                } else if (this.params.node.childIndex === this.params.diagramTableDataLength - 1) {
                    style.bottom = '5px';
                } else {
                    (style.top = '50%'), (style.transform = 'translateY(-50%)');
                }

                return style;
            },
            legendPointerPosition() {
                return {
                    left: `calc(50% + ${this.getBubbleSize / 2 + 13}px)`,
                };
            },
        },
        beforeMount() {
            this.cellValue = this.getValueToDisplay(this.params);
        },
        methods: {
            getValueToDisplay(params) {
                return Number(params.valueFormatted || params.value);
            },
        },
    };
</script>

<style lang="scss" scoped>
    $light-text: #E2E8F0;

    .diagram {
        position: relative;
        width: 100%;
        height: 100%;

        &-inner {
            @include centerer;

            pointer-events: none;
            z-index: 1;
            border-radius: 50%;
            border-style: solid;
            border-width: 1px;
        }

        &-legend {
            position: absolute;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 12px;
            border-radius: 8px;
            background-color: $black;

            &-pointer {
                position: absolute;
                top: 50%;
                width: 10px;
                height: 10px;
                background-color: $black;
                transform: translateY(-50%) rotate(45deg);
            }

            &__text-small {
                font-weight: bold;
                font-size: 12px;
                line-height: 16px;
                color: $color-gray-light-100;
            }

            &__text-big {
                font-weight: bold;
                font-size: 16px;
                line-height: 22px;
                color: #fff;
            }
        }
    }
</style>
