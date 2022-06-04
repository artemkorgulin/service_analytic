<?php

return [
    'root_queries' => 'Загрузка корневых запросов',
    'search_queries' => 'Загрузка поисковых запросов из парсинга',
    'negative_keywords' => 'Загрузка минус-слов',

    'csv_file' => 'CSV-файл',
    'csv_file_link' => 'Ссылка на CSV-файл',
    'contains_titles' => 'Содержит заголовки?',

    'load' => 'Загрузить',

    'ozon_category_all' => 'все',

    'has_errors' => 'При выполнении возникли ошибки, всего: :count',
    'error_opening_file' => 'Ошибка открытия файла',
    'category_not_found' => 'Категория ":category" для запроса ":query" не найдена',
    'root_query_not_found' => 'Корневой запрос ":query" в категории ":category" не найден',
    'root_query_save_error' => 'Ошибка при сохранении корневого запроса ":query"',
    'alias_save_error' => 'Ошибка при сохранении синонима ":alias" к запросу ":query"',
    'search_query_save_error' => 'Ошибка при сохранении поискового запроса ":query"',
    'search_query_update_error' => 'Ошибка при обновлении рейтингов поисковых запросов :queries',
    'root_query_search_query_update_error' => 'Ошибка при обновлении рейтингов связей поисковых запросов :queries',
    'search_query_history_save_error' => 'Ошибка при сохранении истории поискового запроса ":query"',
    'characteristic_save_error' => 'Ошибка при сохранении характеристики ":characteristic"',

    'file_has_been_processed' => ':datetime: Файл был успешно обработан: загружено :count записей за :hours часов :min минут :sec секунд',
    'file_processed_with_errors' => ':datetime: Файл был загружен с ошибками',
    'params_has_been_calculated' => ':datetime: Показатели поисковых запросов были расчитаны за :min минут :sec секунд',
];
