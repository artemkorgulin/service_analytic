<template>
    <div
        :class="classes"
        :style="{ backgroundColor: componentData.color, color: componentData.iconColor }"
    >
        <SvgIcon
            :class="$style.statusIcon"
            :name="componentData.icon"
            :style="{ color: componentData.iconColor }"
        />
        <div v-if="text">{{ text }}</div>
    </div>
</template>

<script>
    export default {
        name: 'VStatusIcon',
        props: {
            status: {
                type: [String, Number],
                default: 1,
            },
            text: {
                type: String,
                default: '',
            },
            expanded: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            classes() {
                return [this.$style.VStatusIcon, { [this.$style.expanded]: this.expanded }];
            },
            componentData() {
                const status = String(this.status);
                let component = {
                    icon: 'filled/text',
                    color: 'rgba(200, 207, 217, 0.08)',
                    iconColor: '#7E8793',
                };
                switch (status) {
                    case '1':
                        component = {
                            icon: 'filled/check',
                            color: 'rgba(32, 194, 116, .08)',
                            iconColor: '#20c274',
                        };
                        break;
                    case '2': // неактивна
                        component = {
                            icon: 'filled/pause',
                            color: 'rgba(200, 207, 217, 0.08)',
                            iconColor: '#7E8793',
                        };
                        break;
                    case '3':
                        component = {
                            icon: 'filled/check',
                            color: 'rgba(32, 194, 116, .08)',
                            iconColor: '#20c274',
                        };
                        break;
                    case '4':
                        component = {
                            icon: 'filled/check',
                            color: 'rgba(32, 194, 116, .08)',
                            iconColor: '#20c274',
                        };
                        break;
                    case '5': // черновик
                        component = {
                            icon: 'filled/pencilalt',
                            color: 'rgba(200, 207, 217, 0.08)',
                            iconColor: '#7E8793',
                        };
                        break;
                    case '6': // архив
                        component = {
                            icon: 'filled/save',
                            color: 'rgba(200, 207, 217, 0.08)',
                            iconColor: '#7E8793',
                        };
                        break;

                    default:
                        // component = {
                        //     icon: 'filled/text',
                        //     color: 'rgba(200, 207, 217, 0.08)',
                        //     iconColor: '#7E8793',
                        // };
                        break;
                }
                return component;
            },
        },
    };
</script>

<style lang="scss" module>
    .VStatusIcon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3.2rem;
        min-width: 3.2rem;
        height: 3.2rem;
        // margin-right: 8px;
        border-radius: 0.8rem;
        // background: rgba(32, 194, 116, .08);

        .statusIcon {
            color: currentColor;
            // color: $success;
        }

        &.expanded {
            justify-content: unset;
            width: unset;
            min-width: unset;
            padding-right: 1.5rem;
            padding-left: 1.1rem;
            border: thin solid currentColor;
            gap: 0.56rem;
        }
    }
</style>
