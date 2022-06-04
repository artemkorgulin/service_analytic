<template>
    <div class="d-flex align-center" style="gap: 16px">
        <div v-if="Number(params.value.art)">
            <a :href="params.value.link" target="_blank">{{ params.value.art }}</a>
            <VChip
                label
                :color="tagInfo.bgColor"
                :text-color="tagInfo.color"
                small
                style="font-weight: 700"
            >
                {{ params.colDef.field.toUpperCase() }}
                <SvgIcon :name="`filled/${tagInfo.icon}`" style="margin-left: 8px" />
            </VChip>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                tagInfo: {},
            };
        },
        created() {
            this.tagInfo = this.getTagInfo();
        },
        methods: {
            getTagInfo() {
                const { quantity } = this.params.value;
                return {
                    color: quantity ? '#20c274' : '#fc6e90',
                    bgColor: quantity ? 'rgba(32, 194, 116, 0.08)' : 'rgba(255, 11, 153, 0.08)',
                    icon: quantity ? 'check' : 'close',
                };
            },
        },
    };
</script>
