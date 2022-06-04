## Архитектура проекта

* `app/Console/Commands/` - консольные команды

    * `ImportRootQueries.php` - содержит команду `load:root-queries {path} {--no-headers}`, позволяющую загрузить корневые запросы из файла, где
        * `path` - путь к файлу
        * `no-headers` - признак отсутствия строки заголовков

    * `LoadSearchQueriesCommand.php` - содержит команду `load:search_queries {--date=today} {--no-headers} {--no-totals}`, позволяющую загрузить поисковые запросы из файла, где
        * `date` - дата файла от парсинга
        * `no-headers` - признак отсутствия строки заголовков
        * `no-totals` - указание не рассчитывать показатели поисковых запросов (т.к. эта процедура весьма продолжительная по времени)

    * `CalcSearchQueriesParametersCommand.php` - содержит команду `calc:search_queries_parameters`, позволяющую расчитать показатели поисковых запросов отдельно
    
    * `CheckProductsAdvertisingCompaign.php` - содержит команду `products:check_advertisings {chunk_size=100}`, позволяющую обновить статус участия товаров в рекламной кампании
        * `chunk_size` - размер чанка данных
    
    * `ImportNegativeKeywords.php` - содержит команду `load:negative-keywords {path} {--no-headers}`, позволяющую загрузить список минус слов из файла, где
        * `path` - путь к файлу
        * `no-headers` - признак отсутствия строки заголовков
  
    * `UpdateProductStatus.php` - содержит команду `ozon:products:update-status`, позволяющую обновить статусы для товаров на модерации

    * `ExportSearchQueriesWeeklyCommand.php` - содержит команду `export:search_queries_weekly`, позволяющую сформировать еженедельный файл для парсинга с поисковыми запросами, требующими дополнительной обработки

    * `LoadSearchQueriesWeeklyCommand.php` - содержит команду `load:weekly_search_queries {--date=yesterday} {--no-headers} {--no-totals}`, позволяющую загрузить результаты еженедельной обработки поисковых запросов из файла, где
        * `date` - дата файла от парсинга
        * `no-headers` - признак отсутствия строки заголовков
        * `no-totals` - указание не рассчитывать показатели поисковых запросов (т.к. эта процедура весьма продолжительная по времени)
    
    * `V2LoadOzonCategories.php` - содержит команду `ozon:load_categories`, позволяющую выгрузить из озона список всех категорий

    * `V2LoadOzonCategoryFeatures.php` - содержит команду `ozon:load_features {category_id?}`, позволяющую выгрузить из озона список всех характеристик в каждой категории
        * `category_id` - id категории (необязательный параметр)

    * `V2LoadOzonOptions.php` - содержит команду `ozon:load_options`, позволяющую выгрузить из озона список значений всех характеристик-справочников
    
    * `V2LoadProducts.php` - содержит команду `ozon:load_products`, позволяющую выгрузить из озона информацию о всех товарах, которые находятся на отслеживании в системе

    * `V2LoadProductsFeatures.php` - содержит команду `ozon:load_products_features`, позволяющую выгрузить из озона значения характеристик для всех товаров, которые находятся на отслеживании в системе

    * `V2LoadProductsStats.php` - содержит команду `ozon:load_products_stats`, позволяющую выгрузить из сервиса MPStats информацию о текущей позиции для всех товаров, которые находятся на отслеживании в системе

    * `V2SendListFilesToFtp.php` - содержит команду `ftp:send_list_files`, позволяющую отправить на ftp информацию для последующего парсинга о всех товарах и их категориях, которые находятся на отслеживании в системе

    * `V2ParseSkuFtp.php` - содержит команду `ftp:parse_sku`, позволяющую выгрузить с ftp результаты парсинга карточек всех товаров, которые находятся на отслеживании в системе

    * `V2ParseTopFtp.php` - содержит команду `ftp:parse_top`, позволяющую выгрузить с ftp результаты парсинга top36 категорий товаров, которые находятся на отслеживании в системе

* `app/Constants/` - Константы с кодами ошибок и данными Озона

* `app/Exports/` - файлы отчетов о сущностях в Excel. должно было войти во 2 релиз, но потом задача была снята, поэтому могут содержать неактуальный код.

* `app/Http/Controllers/` - контроллеры

    * `FirstStepController.php` - контроллер для первого шага.
        * action `index` - Информация для 1 шага: список категорий Озон
        * action `getRootQueries` - Информация для 1 шага: Получение списка корневых запросов к введенному слову
        * action `getOzonCategories` - Получение списка категорий Озон к введенному корневому запросу
    
    * `SecondStepController.php` - контроллер для второго шага.
        * action `index` - Информация для 2 шага: Получение списка поисковых запросов

    * `ThirdStepController.php` - контроллер для третьего шага.     
        * action `index` - Информация для 3 шага: Формирование идеального заголовка товара
        * action `formNewPerfectTitle` - Информация для 3 шага: Формирование нового идеального заголовка товара

    * `ExportController` - контроллер отчетов в Excel

    * `ImportController` - контроллер для загрузки файлов
        * action `rootQueriesForm` - форма загрузки корневых запросов
        * action `negativeKeywordsForm` - форма загрузки минус-слов
        * action `searchQueriesForm` - форма загрузки посиковых запросов
        * action `process` - запуск загрузки
    
    * `Api\ApiController` - контроллер API методов для сторонних сервисов

        * action `getKeywordsPopularity` - Получить популярность ключевых слов
        * action `findKeywordsWithStatistics` - Получить статистику популярности ключевого слова в категории
    
    * `Api\Import\ProductsImportController` - контроллер для импорта карточек товара. _Не используется_

    * `Api\v2\ProductsController` - контроллер API методов для товарами пользователя
        * action `addProduct` - Добавление товара пользователя для отслеживания в системе
        * action `addProducts` - Массовое добавление товаров пользователя для отслеживания в системе
        * action `modifyProduct` - Изменение товара и отправка этих изменений в озон
        * action `getProductsList` - Получение списка товаров пользователя, которые находятся на отслеживании в системе
        * action `getProductDetail` - Получение детальной информации о товаре
        * action `searchFeatureValues` - Поиск значений характеристик-справочников для указанного товара
        * action `removeProducts` - Удаление товара из списка отслеживаемых
        * action `updateProducts` - Актуализация данных о товаре (товарах) в системе (запрос на получение данных в озон и последующее обновление в системе)
        * action `downloadPdfRecomendation` - Генерация и скачивание pdf-файла с рекомендациями по указанному товару
        * action `clearAllTriggers` - Сброс всех триггеров по товару 
        * action `clearPhotoTriggers` - Сброс триггеров по изменению минимального количества фото в ТОП-36 по товару
        * action `clearReviewTriggers` - Сброс триггеров по изменению минимального количества отзывов в ТОП-36 по товару
        * action `clearPositionTriggers` - Сброс триггеров по падению позиции карточки по товару
        * action `clearFeatureTriggers` - Сброс триггеров по изменению количества характеристик по товару
        * action `clearRemoveFromSaleTriggers` - Сброс триггеров по снятию товара с продажи
        * action `resetUpdatedFlags` - Сброс флага обновления карточки
        * action `resetShowSuccessAlertFlag` - Сброс флага алерта
        * action `getTestProductsCount` - Получение списка тестовых продуктов пользователя
        * action `getSampleProductDetail` - Шаблон детализации товара
        * action `setVerifiedStatus` - Установить успешный статус для товара после ознакомления с ошибкой


* `app/Repositories/` - репозитории для работы с сущностями проекта

    * `CharacteristicRepository.php` - характеристики. В данный момент не используется, так как характеристики стали
      считать на ходу, вместо выделения их ежедневно при обработке результатов парсинга
    * `OzonCategoryRepository.php` - категории Озона
    * `RootQueryRepository.php` - корневые запросы
    * `RootQuerySearchQueryRepository.php` - связь Корневой запрос - Поисковый запрос
    * `SearchQueryRepository.php` - поисковые запросы

* `app/Services/` - вспомогательные сервисы

    * `ParsingFilesService.php` - помощник по работе с файлами парсинга, располагающимися по FTP

    * `QueryService.php` - помощник по работе с запросами из файлов и парсинга. Базовый класс для сервисов-лоадеров:
        * `RootQueryLoader.php` - помощник при загрузке корневых слов из файла
        * `NegativeKeywordLoader.php` - помощник при загрузке минус-слов из файла
        * `SearchQueryLoader.php` - помощник при загрузке поисковых запросов из парсинга
        * `SearchQueryCalcService.php` - помощник по выгрузке поисковых запросов для еженедельного парсинга: расчитывает
          Коэффициенты отсеивания поисковых запросов

    * `ProductsLoader.php` - помощник при загрузке товаров (не используется)

    * `V2` - Сервисы для релиза №2
        * `CategoryFeatureUpdater.php` - помощник, позволяющий выгрузить все характеристики и значения характиристик для указанной категории 
        * `FeatureUpdater.php` - помощник, для актуализации информации о характеристиках-справочниках, используется в крон скриптах
        * `FtpService.php` - помощник при работе с ftp 
        * `MPStatsApi.php` - помощник при работе с API сервиса MPStats
        * `OzonApi.php` - помощник при работе с API Озона
        * `ProductServiceImporter.php` - помощник при работе с импортом товаров в Озон
        * `ProductServiceUpdater.php` - помощник, позволяющий актуализировать информацию о товаре, его значениях характеристик, и позиции в категории
        * `ProductsFeatureServiceUpdater.php` - помощник, позволяющий актуализировать информацию о характеристиках коллекции товаров
        * `ProductTrackingService.php` - помощник, позволяющий добавлять товары в отслеживаемые
        * `WebCategoryServiceUpdater.php` - помощник, позволяющий выгружать результаты парсинга с ftp

* `app/Jobs/` - очереди

    * `CheckingProductChanges.php` - очередь, которая используется для обновления статуса товара

* `config/` - конфиги

    * `sources.php` - конфиг источников данных (хост, папки файлов парсинга)
    * `env.php` - параметры окружения

* `database/seeds/` - сидеры базы данных

    * `OzonCategorySeeder.php` - категории Озон для ВП (из публичной части)
    * `NegativeKeywordsSeeder.php` - минус-слова (для тестов)
    * `RootQuerySeeder.php` - корневые запросы (для тестов, _возможно неактуальный код_)
    * `ProductStatusSeeder.php` - заполняет возможные статусы товара
    * `ProductSetStatusSeeder.php` - проставляет статус по умолчанию для товаров, у которых нет статуса (статус добавился в процессе и является обязательным полем, поэтому было необходимо заполнить статусы всем товарам, чтобы прошла миграция)
    * `AddOzonStatusCreated.php` - создание конкретного статуса для создания товаров в Ozon
* 

* `resources/views/import/` - шаблоны административных страниц (должны были во 2 релизе переехать на фронт)

    * `negative_keywords.blade.php` - минус-слова
    * `root_queries.blade.php` - корневые запросы
    * `seacrh_queries.blade.php` - поисковые запросы (работает через крон, а также консоль)

* `resources/sampleProducts` - товары заглушки

* `routes/`

    * `api.php` - роуты для API-методов взаимодействия с фронтом
    * `web.php` - роуты для административных страниц

## Модели данных

* **Alias** - Синоним к корневому запросу.
* **Characteristic** - Характеристика поискового запроса. *В данный момент не используется, так как характеристики выделяются на ходу при работе с пользователем.*
* **NegativeKeyword** - Минус-слово.
* **OptimizationRequest** - Запрос на оптимизацию. *Должно было быть во 2 релизе. В данный момент не используется.*
* **OzonCategory** - Категория Озон (из публичной части).
* **OzonSellerCategory** - Категория Озон (из Ozon Seller'а).
* **Product** - Товар. _Должен был быть во 2 релизе. Не используется._
* **ProductCharacteristic** - Характеристика товара. _Должна была быть во 2 релизе. Не используется._
* **ProductCharacteristicName** - Название характеристики товара. _Должно было быть во 2 релизе. Не используется._
* **ProductCharacteristicValue** - Значение характеристики товара. _Должно было быть во 2 релизе. Не используется._
* **ProductSet** - Подборка товара. _Должно было быть во 2 релизе. Не используется._
* **RootQuery** - Корневой запрос.
* **RootQuerySearchQuery** - Связь "Корневой запрос - Поисковый запрос".
* **SearchQuery** - Поисковый запрос.
* **SearchQueryHistory** - История по поисковому запросу.
* **SearchQueryRank** - Место в выдаче по поисковому запросу. (_Не используется_).

#### V2
* **Category** - Категории Озон
* **CategoryToFeature** - Связь категорий и характеристик
* **Feature** - Характеристики категорий Озон
* **Option** - Значения Озон
* **Product** - Товары, которые находятся на отслеживании в системе
* **ProductFeature** - Значения характеристик товара
* **ProductChangeHistory** - История редактирования товара
* **ProductFeatureHistory** - История изменения характеристик товара
* **ProductFeatureErrorHistory** - История ошибок редактирования характеристик товара
* **ProductPositionHistory** - История изменения позиции товара по самой глубокой категории
* **ProductPositionHistoryGraph** - История изменения позиции товара по всем подкатегориям
* **ProductPriceChangeHistory** - История изменения цены продукта
* **WbProductStatus** - Статусы товаров Wildberries
* **TemporaryProduct** - Товары пустышки
* **WebCategory** - Web категории
* **WebCategoryHistory** - История Web категорий
* **TriggerChangeFeature** - Триггеры изменения характеристик
* **TriggerChangePhotos** - Триггеры изменения минимума фото
* **TriggerChangeReviews** - Триггеры изменения минимума отзывов
* **TriggerRemoveFromSale** - Триггеры снятия с продажи

## Загрузка исходных данных из файлов

Импорт корневых запросов и минус слов проводится из csv-файла от SEO-аналитика Артема Багненко с административных страниц:

* `<host>/import/root-queries` - корневые запросы
* `<host>/import/negative-keywords` - минус-слова

## Загрузка данных из парсингов

Импорт поисковых запросов проводится с ftp, куда файлы подкладывает компания-партнер RUFAGO.

Папки на FTP: 

* `root_queries_to_parsing/ozon_seller_root_queries.csv` - файл корневых запросов для ежедневного парсинга
* `search_queries_to_parsing/ozon_seller_search_queries.csv` - файл поисковых запросов для еженедельного парсинга
* `root_queries_results_from_parsing/` - файлы с результатами парсинга корневых запросов (с поисковыми запросами)
* `search_queries_results_from_parsing/` - файлы с результатами еженедельного парсинга поиковых запросов (с ТОП-36)
* `hour_results_from_parsing/sku_parsing/list_sku/List_SKU.csv` - файл списка товаров для парсинга
* `hour_results_from_parsing/sku_parsing/date_sku/` - файлы с результатами парсинга карточек товара
* `hour_results_from_parsing/top36/list_top36/List_TOP36.csv` - файл списка категорий для парсинга
* `hour_results_from_parsing/top36/date_top36/` - файлы с результатами парсинга ТОП-36 в категориях

Запуск:
* Ежедневый импорт поисковых запросов - каждый день в 12:00 по МСК
* Еженедельный экспорт поисковых запросов - по средам в 00:10
* Еженедельный импорт поисковых запросов - по четвергам в 00:10
* Ежечастный импорт результатов парсинга карточек товаров и ТОП-36 в категориях товаров

При запуске файлы с FTP копируются в локальное хранилище.

**!** В беклоге периодическое удаление старых файлов.

## Параметры .env

- `RUFAGO_DISK` - место хранения файлов для парсинга, отличается у среды разработки и прода.
- `RUFAGO_INPUT_FILES_FTP_HOST` - адрес FTP, куда парсинги присылают файлы
- `RUFAGO_INPUT_FILES_FTP_USER` - пользователь
- `RUFAGO_INPUT_FILES_FTP_PASS` - пароль

- `RUFAGO_OUTPUT_FILES_FTP_HOST` - адрес FTP, откуда парсинги забирают файлы
- `RUFAGO_OUTPUT_FILES_FTP_USER` - пользователь
- `RUFAGO_OUTPUT_FILES_FTP_PASS` - пароль

- `OZON_API_HOST` - Хост API Озон
- `OZON_COMMAND_CLIEND_ID` - ID клиента по умолчанию для запросов в озон
- `OZON_COMMAND_API_KEY` - API key по умолчанию для запросов в озон
- `OZON_COMMAND_API_KEY` - API key по умолчанию для запросов в озон
- `OZON_CHUNK_TOKENS` - Список API ключей с ID клиента для запуска импорта значений характеристик в несколько потоков
- `MPSTATS_API_HOST` - Хост API MPStats
- `MPSTATS_TOKEN` - Токен
- `FTP_HOST` - адрес FTP, откуда получаем результаты парсинга карточек товаров и ТОП-36 категорий товаров
- `FTP_USERNAME` - пользователь
- `FTP_PASSWORD` - пароль 
- `CHECK_ADVERTISING_URL` - Урл для проверки участия товаров в рекламной кампании
- `CHECK_ADVERTISING_TOKEN` - Токен, отправляется при запросе на проверку участия товара в РК
- `FRONT_APP_URL` - Урл приложения фронта 


## Развертывание

Если вы хотите развернуть проект в чистую базу, то вам необходимо выполнить команду:

```
php artisan migrate --path="database/migrations/init"
```

При этом будут созданы все изначальные таблицы типа: 

v2_products, v2_web_categories и т.д. 

Далее применяем просто команду 

```
php artisan migrate
```

Будут созданы все остальные таблицы. 

Если же вы просто хотите дополнить (обновить проект) выполняйте миграцию:

```
php artisan migrate
```


При разворачивании в Docker необходимо сначала поднять контейнеры из проекта
web-app-back. Данный проект зависит от него поскольку контейнеры попадают
в сеть, которую создаёт web-app-back. Также web-app-back является проксирующим
бэкендом, который проверяет пользователей, права и роли и возвращает ответы
от virtual-assistant-back.

В папке docker/php/socket создать файл php-fpm.sock

Получить зависимости:

```
composer install
```

Очистить кеш в конфигурации:

```
php artisan config:cache
```

Очистить кеш маршрутов:

```
php artisan route:cache
```

Создать JWT secret

```
php artisan jwt:secret
```

Опубликовать ссылку на Storage

```
php artisan storage:link
```

Прописать в `.env` файле пути к FTP с результатами парсингов

```
RUFAGO_INPUT_FILES_FTP_HOST=
RUFAGO_INPUT_FILES_FTP_USER=
RUFAGO_INPUT_FILES_FTP_PASS=

RUFAGO_OUTPUT_FILES_FTP_HOST=
RUFAGO_OUTPUT_FILES_FTP_USER=
RUFAGO_OUTPUT_FILES_FTP_PASS=
```

Прописать в `.env` файле данные для FTP, Ozon, MPStats

Накатить миграции. При разворачивании в Docker необходимо для этого подключиться
к контейнеру с php-fpm и выполнять две следующие команды на нём.

```
php artisan migrate
```

Запустить сиды

```
php artisan db:seed
```


## Требования к окружению

PHP 7.4

## Тестирование

Создайте тестовую базу данных и пропишите ее имя в `.env`:

```
TEST_DATABASE=cva_testing
```

Для запуска тестов используйте:

```
# php artisan test
```

Для наполнения тестовой БД используется `TestDatabaseSeeder`.

Cron
```
* * * * * cd /var/www/ &&  /usr/local/bin/php artisan schedule:run >> /var/www/storage/logs/out.`date +\%Y-\%m-\%d`.log 2>&1
# An empty line is required at the end of this file for a valid cron file.
```
service cron start
service cron reload

