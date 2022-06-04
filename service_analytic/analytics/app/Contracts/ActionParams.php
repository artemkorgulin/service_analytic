<?php

namespace App\Contracts;

class ActionParams
{
    const PLATFORM_ID_OZOM = 1;
    const PLATFORM_ID_WB = 3;

    const OPTIMIZATION_SHORT = 'Степень оптимизации';
    const OPTIMIZATION_SHORT_BEFORE = 'Степень оптимизации вчера';
    const OPTIMIZATION_LONG = 'Степень оптимизации карточки значительно снизилась. Рекомендуем оптимизировать карточку товара в разделе Мои товары';

    const POSITION_CATEGORY_SHORT = 'Позиция в категории';
    const POSITION_CATEGORY_SHORT_BEFORE = 'Позиция в категории вчера';
    const POSITION_CATEGORY_LONG = 'Видимость товара в категории значительно снижена. Укажите как можно больше информации во вкладке Контентная оптимизация';

    const POSITION_SEARCH_SHORT = 'Позиция в поиске';
    const POSITION_SEARCH_SHORT_BEFORE = 'Позиция в поиске вчера';
    const POSITION_SEARCH_LONG = 'Видимость товара в поиске значительно снижена. Используйте функцию Поисковая оптимизация, чтобы наполнить карточку актуальными ключевыми словами';

    const POSITION_RATING_SHORT = 'Позиция в рейтинге';
    const POSITION_RATING_SHORT_BEFORE = 'Позиция в рейтинге вчера';
    const POSITION_RATING_LONG = 'Рейтинг товара на маркетплейсе снизился. Стимулируйте покупателей оставлять больше положительных отзывов';

    const POSITION_SAlES_SHORT = 'Продажи';
    const POSITION_SALES_SHORT_BEFORE = 'Среднее продажи за недедю';
    const POSITION_SALES_LONG = 'Сумма продаж товара стала ниже чем за предидущие 7 дней: проверьте наполнение карточки. Рекомендуем вам стимулировать покупателей оставлять больше положительных отзывов';

    const IMAGES_COUNT_SHORT = 'Колличество изображений';
    const IMAGES_COUNT_SHORT_BEFORE = 'Колличество изображений вчера';
    const IMAGES_COUNT_LONG = 'Количество изображений в карточке уменьшилось и теперь меньше, чем у некоторых конкурентов. Добавьте изображения или инфографику в карточку, чтобы покупателям было легче выбрать ваш товар';

    const ESCROW_SHORT = 'Защита авторства';
    const ESCROW__SHORT_BEFORE = 'Защита авторства вчера';
    const ESCROW__LONG = 'В карточке обнаружены недепонированные изображения. Воспользуйтесь функцией Защита авторства, чтобы обезопасить себя от недобросовестной конкуренции.';

    const WEIGHT_SALES = 1;
    const WEIGHT_OPTIMIZATION = 2;
    const WEIGHT_RATING = 3;
    const WEIGHT_POSITION_SEARCH = 4;
    const WEIGHT_POSITION_CATEGORY = 5;
    const WEIGHT_COUNT_IMAGES = 6;
    const WEIGHT_ESCROW = 7;

    const OPTIMIZATION_TRIGGER_THRESHOLD_PERCENTAGE = 10;
    const POSITION_CATEGORY_TRIGGER_THRESHOLD_PERCENTAGE = 10;
    const POSITION_SEARCH_TRIGGER_THRESHOLD_PERCENTAGE = 10;
    const RATING_TRIGGER_THRESHOLD_PERCENTAGE = 3;
    const SAlES_TRIGGER_THRESHOLD_PERCENTAGE = 10;
    const IMAGE_TRIGGER_THRESHOLD = 2;

    const PERIOD = 7;
    const DEFAULT_COUNT = 25;
    const PERIOD_MONTH = 30;
}
