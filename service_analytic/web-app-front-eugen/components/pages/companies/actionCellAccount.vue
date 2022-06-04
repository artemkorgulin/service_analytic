<template>
    <div class="actions">
        <div class="actions-market__btn" @click="params.clicked(params)">Перенести</div>
    </div>
</template>
<style lang="scss">
    .actions-market {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        width: 100%;
        height: 100%;

        &__btn {
            padding: 4px 8px;
            line-height: 16px;
            cursor: pointer;

            &:hover {
                background: rgba(212, 212, 212, 0.575);
            }
        }
    }

    .actions__list.v-list {
        padding: 0;

        .v-list-item {
            min-height: 40px;
            padding: 0 16px;
            cursor: pointer;

            &:hover {
                background: #e9e9e9;
            }
        }
    }
</style>
