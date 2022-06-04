## Развертывание

При разворачивании в Docker необходимо сначала поднять контейнеры из проекта
web-app-back. Данный проект зависит от него поскольку контейнеры попадают
в сеть, которую создаёт web-app-back.

В папке docker/php/socket создать файл php-fpm.sock

Получить зависимости:

```
composer install
```

Очистить кэши:

```
./clear_cache.sh
```

Прописать в `.env` файле описанные выше параметры + подключение к БД

Накатить миграции. При разворачивании в Docker необходимо для этого подключиться к контейнеру с php-fpm и выполнять две
следующие команды на нём. Миграции накатываем на три схемы:
analytica - схема окружения static - схема вычисляемых данных из парсера 
analytica_oz - схема окружения озон

```
php artisan migrate --database=analytica
php artisan migrate --path=/database/migrations/static --database=static
php artisan migrate --path=/database/migrations/ozon --database=analytica_oz
```
