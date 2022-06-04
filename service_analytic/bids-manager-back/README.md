# Управление ставками Ozon

## Архитектура проекта

* `app/Actions/` - экшоны
    Содержат бизнес-логику методов контроллеров. 
    Имеют единственный публичный метод run(), который принимает на вход DTO, сформированный в контроллере из объекта Request. 
    Запускают таски(Tasks). Повторяющиеся сценарии выносятся в SubActions.

* `app/Console/Commands/` - консольные команды

    * `applyStrategies` - содержит команду `apply:strategies {campaignIds?*}`, позволяющую применить стратегии к РК, где
        * `campaignIds` - идентификаторы (внутренние) рекламных кампаний
        
    * `deleteOldAutoselectResults` - содержит команду `autoselect:delete_old_results`, которая выполняет очистку результатов
    автоподбора старше 3 дней

    * `generateCampaignReport` - содержит команду `generate_in_ozon:campaigns_reports {campaignIds?*}`, позволяющую запустить генерацию отчетов о реализации в Озон, где
        * `campaignIds` - идентификаторы (внутренние) рекламных кампаний

    * `getCampaignReportResponse` - содержит команду `load_from_ozon:campaigns_reports_response`, позволяющую загрузить из Озона сгенерированные отчеты о
      реализации

    * `LoadCampaignsFromOzon` - содержит команду `ozon:load-campaigns`, позволяющую загрузить из Озона список рекламных кампаний

    * `loadProductsFromOzon` - содержит команду `load_from_ozon:products`, позволяющую загрузить из Озона список товаров

    * `loadCampaignProductsFromOzon` - содержит команду `load_from_ozon:campaign_products {campaignIds?*}`, позволяющую загрузить из Озона товары к рекламным кампаниям, где
       * `campaignIds` - идентификаторы (внутренние) рекламных кампаний

    * `loadPopularitiesFromVirtualAssistant` - содержит команду `load_from_va:popularities {--campaignIds=*} {--dateFrom=}`, позволяющую загрузить из Виртуального помощника популярность ключевиков, где
       * `dateFrom` - дата начала загрузки популярности
       * `campaignIds` - идентификаторы (внутренние) рекламных кампаний

* `app/DataTransferObjects/` - DTO
    Содержат метод fromRequest, который нужен для того, чтобы не "протаскивать" объект Request за пределы контроллера.
    Сформированные таким образом DTO передаются из контроллеров в экшоны(Actions).
  
* `app/Exports/` - файлы отчетов о сущностях в Excel

    * `AnalyticsExport` - отчет со страницы "Аналитика"
    * `AutoselectResultsExport` - отчет о результатах автоподбора со страницы "Результаты автоподбора"
    * `CampaignExport` - отчет со страницы "Ставки", уровень компании
    * `KeywordsExport` - отчет со страницы "Ставки", уровень ключевых слов
    * `StatisticExport` - вспомогательный класс декорации отчетов
    * `StrategyCpoExport` - отчет со страницы "Стратегии", раздел CPO
    * `StrategyShowsExport` - отчет со страницы "Стратегии", раздел Оптимизации показов

* `app/Helpers/` - вспомогательные хелперы

    * `AnalyticsHelper` - запрос данных для страницы аналитики
    * `CampaignStatisticHelper` - запрос данных для страницы "Ставки", уровень компании
    * `ProductStatisticHelper` - запрос данных для страницы "Ставки", уровень товары
    * `KeywordStatisticHelper` - запрос данных для страницы "Ставки", уровень ключевых слов
    * `StatisticHelper` - вспомогательный класс запросов для статистики
    * `OzonHelper` - хелпер для отправки данных в Озон
    * `StrategyHelper` - хелпер для работы со стратегиями

* `app/Http/Controllers/` - контроллеры

    * `Api/AccountController.php` - контроллер для работы с аккаунтами
    * `Api/Frontend/Autoselect/AutoselectController.php` - контроллер для работы с автоподбором
        * `run` - обработка запроса на запуск автоподбора, возвращает результаты автоподбора
        * `getResultList` - обработка запроса на получение списка с результатами автоподбора
        * `getXLSResults` - обработка запроса на получение списка с результатами автоподбора в формате XLS
        * `getXLSKeywordUploadTemplate` - обработка запроса на получение шаблона для загрузки слов из XLS
    * `Api/Frontend/Campaign/CampaignController.php` - контроллер для работы с рекламными кампаниями
        * `store` - обработка запроса на создание рекламной кампании
        * `storeOzon` - обработка запроса на отправку рекламной кампании в Озон
        * `show` - обработка запроса на получение рекламной кампании по идентификатору
        * `update` - обработка запроса на изменение рекламной кампании
    * `Api/Frontend/Campaign/CampaignProductController.php` - контроллер для работы с товарами рекламных кампаний
        * `index` - обработка запроса на получение списка товаров рекламной кампании
        * `store` - обработка запроса на добавление товара к рекламной кампании
        * `storeMultiple` - обработка запроса на добавление товаров и групп к пустой рекламной кампании
        * `updateMultiple` - обработка запроса на изменение товаров и групп рекламной кампании
        * `updateStatus` - обработка запроса на обновление статуса товара рекламной кампании
        * `removeOldProducts` - архивация удалённых товаров рекламной кампании
        * `removeOldGroups` - архивация удалённых групп товаров рекламной кампании
    * `Api/Frontend/Campaign/GroupController.php` - контроллер для работы с группами товаров рекламных кампаний
        * `index` - обработка запроса на получение списка групп товаров рекламной кампании
        * `store` - обработка запроса на создание группы товаров рекламной кампании
        * `show` - обработка запроса на получение группы товаров по идентификатору
    * `Api/Frontend/Product/ProductController.php` - контроллер для работы с товарами
        * `getFilteredList` - обработка запроса на поиск товаров, названием начинающихся с заданной строки
        * `searchProductsBySku` - обработка запроса на поиск группы товаров рекламной кампании
    * `Api/Frontend/Keyword/KeywordController.php` - контроллер для работы с ключевыми словами
        * `store` - обработка запроса на создание нового ключевого слова
        * `getListByFilter` - обработка запроса на поиск ключевых слов, названием начинающихся с заданной строки
    * `Api/Frontend/Keyword/CampaignKeywordController.php` - контроллер для работы с ключевыми словами рекламной кампании
        * `index` - обработка запроса на поиск товаров, названием начинающихся с заданной строки
        * `updateMultiple` - обработка запроса на изменение ключевых слов товаров и групп рекламной кампании
        * `saveCampaignProductKeyword` - сохранение ключевых слов товаров и групп рекламной кампании
        * `archiveCampaignKeywords` - архивация удалённых ключевых слов товаров и групп рекламной кампании
    * `Api/Frontend/Stopword/StopWordController.php` - контроллер для работы с ключевыми словами
        * `store` - обработка запроса на создание нового ключевого слова
        * `getListByFilter` - обработка запроса на поиск ключевых слов, названием начинающихся с заданной строки
    * `Api/Frontend/Stopword/CampaignStopWordController.php` - контроллер для работы с ключевыми словами рекламной кампании
        * `index` - обработка запроса на поиск товаров, названием начинающихся с заданной строки
        * `updateMultiple` - обработка запроса на изменение ключевых слов товаров и групп рекламной кампании
        * `saveCampaignProductWord` - сохранение ключевых слов товаров и групп рекламной кампании
    * `Api/Frontend/Words/WordsController.php` - контроллер для работы с ключевыми и минус словами товара рекламной кампании 
        * `saveFromXls` - обработка запроса на добавление ключевых и минус слов товару/группе рекламной кампании из XLS-файла 
        * `saveFromAutoselect` - обработка запроса на добавление ключевых и минус слов товару/группе рекламной кампании из автоподбора

* `app/Http/Middleware/` - посредники

    * `CheckCommonAppToken` - проверка, что запрос пришел из Объединяющего интерфейса (Seller Expert)
    * `CheckVirtualAssistantToken` - проверка, что запрос пришел из Виртуального помощника
    * `CheckUserPermissions` - проверка, что у пользователя есть права на просмотр функционала
    
* `app/Http/Requests/` - реквесты
    Наследуют Request через базовый BaseFrontendRequest, приходящий в метод контроллера. 
    Содержат логику валидации данных и обработку ответа, если валидация не прошла.

* `app/Imports/` - импорты
    * `CampaignWordsImport` - импорт слов из XLS шаблона.
    * `CampaignKeywordsImport` и `CampaignStopwordsImport` реализуют логику отдельно взятых листов из `CampaignWordsImport`

* `app/Observers/` - наблюдатели
    * `AutoselectResultObserver` - отслеживает события сущности "Результат автоподбора"

* `app/Repositories/` - репозитории
    Содержат логику запросов на получение данных из БД, наименования в соответствии с моделью.
    Наследуются от абстрактного класса BaseRepository

* `app/Services/` - вспомогательные сервисы

    * `CalculateService` - расчет дополнительных показателей при выводе на страницы

    * `CsvService` - сервис для работы с CSV-файлами
    
    * `DatabaseService` - сервис для сохранения данных в базу

    * `OzonService` - базовый сервис для отправки запросов в Ozon:
        * `OzonPerfomanceService` - сервис для отправки запросов в Ozon Perfomance

    * `StrategyService` - сервис для работы со стратегиями

    * `VirtualAssistantService` - сервис для отправки запросов в "Виртуальный помощник"

    * `Loaders` - сервисы для загрузчиков данных
        * `LoaderService` - базовый класс 
        * `OzonCampaignsLoader` - загрузчик рекламных кампаний из Озона
        * `OzonCampaignProductsLoader` - загрузчик товаров из РК из Озона
        * `OzonCampaignsReportsResponseLoader` - загрузчик отчетов о реализации из Озона
        * `VirtualAssistantPopularitiesLoader` - загрузчик популярностей из Виртуального помощника

* `app/Tasks/` - таски
    Содержат логику простых и небольших, общих операций по получению/сохранению сущностей. Используют репозитории
  
* `config/` - конфиги

    * `common_app.php` - конфиг доступа к Seller Expert
    * `jwt.php` - параметры аутентификации
    * `ozon.php` - параметры доступа к Озон
    * `virtual_assistant.php` - параметры доступа к Виртуальному помощнику

* `database/seeds/` - сидеры базы данных

    * `CampaignPageTypeSeeder` - типы страниц РК
    * `CampaignPaymentTypeSeeder` - типы оплаты РК
    * `CampaignPlacementSeeder` - места размещения РК
    * `CampaignTypeSeeder` - типы РК
    * `StatusDeletedSeeder` - введение статуса "Удалено"
    * `StatusSeeder` - статусы
    * `StrategyStatusSeeder` - статусы стратегий
    * `StrategyTypeSeeder` - типы стратегий
    * `UserSeeder` - пользователи

## Модели данных

* **AutoselectParameter** - Параметры автоподбора
* **AutoselectResult** - Результат автоподбора
* **Campaign** - Рекламная кампания
* **CampaignProduct** - Товар в РК
* **CampaignProductStatistic** - Статистика товара в РК
* **CampaignKeyword** - Ключевик в РК
* **CampaignKeywordStatistic** - Статистика ключевика в РК
* **CampaignPageType** - Тип страницы РК
* **CampaignPaymentType** - Тип оплаты РК
* **CampaignPlacement** - Место размещения РК
* **CampaignStatistic** - Статистика РК
* **CampaignStatus** - Статусы РК
* **CampaignStopWord** - Минус-слово в РК
* **CampaignType** - Тип РК
* **CronUUIDReport** - Запросы отчетов о реализации Озон
* **Group** - Группы
* **Keyword** - Ключевое слово
* **KeywordPopularity** - Популярность ключевого слова
* **Permission** - Разрешение функционала
* **StatisticInterface** - Интерфейс для классов статистик
* **StatisticReport** - Строка отчета для сохранения в статистику
* **Status** - Статус
* **StopWord** - Минус-слово
* **Strategy** - Стратегия
* **StrategyCpo** - Стратегия CPO
* **StrategyCpoKeywordStatistic** - Статистика ключевиков в стратегии CPO
* **StrategyCpoStatistic** - Статистика РК в стратегии CPO
* **StrategyHistory** - История изменения стратегии
* **StrategyShows** - Стратегия оптимального количества показов
* **StrategyShowsKeywordStatistic** - Статистика РК в стратегии оптимального количества показов
* **StrategyStatus** - Статус стратегии
* **StrategyType** - Тип стратегии
* **User** - Пользователь
* **UserPermission** - Разрешения пользователя

## Загрузка данных из Озона

Загрузка данных из Озона производится по расписанию, представленному в файле: https://docs.google.com/spreadsheets/d/1ofOjtzCIss5jwOQ1w5YDmpOby1EZqWN7FnuzP28fCcg/edit#gid=0

Для тестовых серверов существует лаг в 10 минут, чтобы исключить большое количество одновременно отправляемых запросов.

## Параметры .env

- `APP_CODE` - код приложения (используется для Analitycs Expert)

- `OZON_SYNC` - Нужно ли отправлять результаты стратегии в Озон
- `OZON_SELLER_SERVICE_CLIENT_ID` - Сервисный аккаунт Озон, из которого тянутся категории
- `OZON_SELLER_SERVICE_API_KEY` - Сервисный аккаунт Озон, из которого тянутся категории

- `JWT_SECRET` - Код для аутентификации JWT
- `COMMON_APP_TOKEN` - Токен Объединяющего интерфейса
- `VIRTUAL_ASSISTANT_TOKEN` - Токен Виртуального помощника

## Развертывание

При разворачивании в Docker необходимо сначала поднять контейнеры из проекта 
web-app-back. Данный проект зависит от него поскольку контейнеры попадают
в сеть, которую создаёт web-app-back. Также web-app-back является проксирующим
бэкендом, который проверяет пользователей, права и роли и возвращает ответы 
от bids-manager-back.

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

Прописать в `.env` файле описанные выше параметры + подключение к БД

Создать JWT secret

```
php artisan jwt:secret
```

Опубликовать ссылку на Storage

```
php artisan storage:link
```

Накатить миграции. При разворачивании в Docker необходимо для этого подключиться
к контейнеру с php-fpm и выполнять две следующие команды на нём.

```
php artisan migrate
```

Запустить сиды

```
php artisan db:seed
```

Cron
```
* * * * * cd /var/www/ &&  /usr/local/bin/php artisan schedule:run >> /var/www/storage/logs/out.`date +\%Y-\%m-\%d`.log 2>&1
# An empty line is required at the end of this file for a valid cron file.
```
service cron start
service cron reload

## Требования к окружению

PHP 8.0
Laravel 8
