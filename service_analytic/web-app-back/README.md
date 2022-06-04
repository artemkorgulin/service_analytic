## Архитектура проекта

* `app/Console/Commands/` - консольные команды
    * `UpdateUserSubscriptionsCommand.php` - содержит команду `update:subscriptions {chunk_size=200}`, для актуализации подписок пользователей, где
        * `chunk_size` - размер чанка данных
    
    * `UserMakeCommand.php` - содержит команду `user:make`, позволяющую создать нового пользователя. _Не нужна для нормальной работы платформы_

* `app/Constants/` - Константы с кодами ошибок и статусами платежа

* `app/Enums/` - Перечисления

* `app/Events/` - события на покупку и изменение тарифа пользователя

* `app/Listeners/` - слушатели событий

* `app/Http/Observers/` - обсерверы
    * `SubscriptionObserver` - при изменении подписки записываем историю и при изменении подписки отправляем изменения в ВП и УС
    * `UserObserver` - при регистрации пользователя проставляем ему бесплатный тариф

* `app/Http/Controllers/` - контроллеры

    * `Api\v1\Yookassa\YookassaController.php` - обработчик хуков от Yookassa
        * action `handleEvent` - обработка событий от Yookassa, проставляет статус платежа

    * `Api\v1\AuthController` - Контроллер с методами авторизации пользователей, посылает запросы в сервисы ВП и УС
        * action `registration` - Регистрация пользователя, после регистрации отправляется письмо для подтверждения
          email
        * action `login` - Авторизация пользователя, JWT токены, полученные от ВП и УС записываются в БД
        * action `confirmRegistration` - Подтверждение регистрации, после подтверждения пароль пользователя хешируется и
          сохраняется в БД. Возможно в дальнейшем лучше синхронизировать хэши
        * action `logout` - Выход из аккаунта, также инвалидирует токены для ВП и УС
        * action `resetPasswordRequest` - Запрос на сброс пароля, отправляет письмо с ссылкой на восстановление
        * action `resetPassword` - Сброс пароля
        * action `password` - Смена пароля
        * action `me` - Получение информации о пользователе
        * action `getSettings` - Получение настроек пользователя (ключи озона, текущий тариф, доступные тарифы и т. д.)
        * action `setSettings` - Установка настроек пользователя (ключи озона, уведомления по почте)

* `app/Services/V2` - вспомогательные сервисы

    * `OzonApi.php` - Сервис для отправки запросов в озон

    * `PaymentService.php` - Сервис работы с платежами

    * `TariffActivityService.php` - Сервис для работы с подписками

    * `YooKassaApiService.php` - Сервис для отправки запросов в Yookassa
    
* `config/` - конфиги
    * `virtual-assistant.php` - параметры для работы с ВП
    * `env.php` - параметры окружения
    * `yookassa.php` - параметры для работы с Yookassa

* `database/seeds/` - сидеры базы данных

    * `PermissionSeeder.php` - Доступы к функционалу 
    * `TariffPermissionSeeder.php` - Создаёт связь тарифов с доступами
    * `TariffSeeder.php` - Тарифы
    * `UserSeeder.php` - Пользователи
    * `UserSubscriptionSeeder.php` - Для всех пользователей без подписки создаёт дефолтную (бесплатную) подписку
    
* `routes/`
    * `api.php` - роуты для API-методов 

## Модели данных

* **AbolitionReason** - Причина отмены платежа
* **Payment** - Платёж
* **PaymentMethod** - Способ оплаты
* **PaymentRecipient** - Получатель платежа
* **Permission** - Права на доступ к функционалу
* **Subscription** - Подписка
* **SubscriptionHistory** - История подписок
* **OldTariff** - Старая механика тарифов (до 2022.03)
* **TariffPermission** - Права для тарифа
* **TariffPrice** - Цена тарифа
* **User** - Пользователь

## Параметры .env

- `VIRTUAL_ASSISTANT_URL` - Урл виртуального помощника
- `BIDS_MANAGER_URL` - Урл управления ставками
- `FRONT_APP_URL` - Урл фронэнда
- `OZON_API_HOST` - Урл Озона
- `OZON_PERFORMANCE_API_HOST` - Урл Ozon performance
- `YOOKASSA_SHOP_ID` - ID магазина в YooKassa
- `YOOKASSA_SECRET_KEY` - Секретный ключ YooKassa
- `YOOKASSA_RETURN_URL` - Урл на который редиректит YooKassa после совершения платежа
- `MAIL_FOOTER_EMAIL` - Email адрес в футере
- `JWT_SECRET` - Секретный ключ для генерации JWT токенов
