<template>
    <div class="top-by-positions-item-header">
        <div class="top-by-positions-item-header-position">Позиция</div>
        <div class="top-by-positions-item-header-img"></div>
        <div class="top-by-positions-item-header-article">
            Артикул
            <SvgIcon
                name="outlined/search"
                style="width: 13px; margin-right: 10px; color: c8cfd9"
            />
        </div>
        <div class="top-by-positions-item-header-optimization">
            <div class="top-by-positions-item-header-optimization-text">Оптимизация</div>
            <SvgIcon
                name="outlined/search"
                style="width: 13px; margin-right: 10px; color: c8cfd9"
            />
        </div>
        <div class="top-by-positions-item-header-name">
            <div class="top-by-positions-item-header-name-text">Название</div>
            <SvgIcon
                name="outlined/search"
                style="width: 13px; margin-right: 10px; color: c8cfd9"
            />
        </div>
        <div class="top-by-positions-item-header-category">
            <div class="top-by-positions-item-header-name-category">Категория</div>
            <SvgIcon
                name="outlined/search"
                style="width: 13px; margin-right: 10px; color: c8cfd9"
            />
        </div>
    </div>
</template>

<script>
    export default {
        name: 'TopByPositionsItemHeader',
    };
</script>

<style lang="scss" scoped>
    .top-by-positions-item-header {
        display: flex;
        align-items: center;
        height: 40px;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
        background-color: #e9edf2;
        font-size: 12px;
        font-weight: 500;
        color: #505965;

        &-position {
            box-sizing: border-box;
            overflow: hidden;
            min-width: 50px;
            max-width: 50px;
            padding: 12px 0 12px 16px;
            text-overflow: ellipsis;
        }

        &-img {
            box-sizing: border-box;
            overflow: hidden;
            min-width: 78px;
            max-width: 78px;
            height: 100%;
        }

        &-article {
            box-sizing: border-box;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 120px;
            max-width: 120px;
            height: 100%;
            padding: 12px 0 12px 16px;
        }

        &-optimization {
            box-sizing: border-box;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 104px;
            max-width: 104px;
            height: 100%;
            padding: 12px 0 12px 16px;

            &-text {
                overflow: hidden;
                width: 50px;
                text-overflow: ellipsis;
            }
        }

        &-name {
            box-sizing: border-box;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 310px;
            max-width: 310px;
            padding: 12px 0 12px 16px;
        }

        &-category {
            box-sizing: border-box;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 280px;
            max-width: 280px;
            padding: 12px 0 12px 16px;
        }
    }
</style>
