## Установка

клонировать гит
composer install

## Настройка

создать файл .env из файла .env.example

## Таблицы

Добавить таблицы

users.sql, personal_access_tokens.sql

## Документация Api

[/api/documentation](/api/documentation#/default)

### Обновление
php artisan l5-swagger:generate

### Авторизация
/api/register
/api/login
возвращает токен авторизации апи

Введите токен авторизации в формате 'Bearer {token}'

пример: Bearer 4|YIWCgjNc9c9bD6wEez07lM0IHRJxkCBzhWdzz24Q

url открываются по клику можно сделать пробный запрос 'Try it out'

Тестировать лучше в postman

API wordpress
Добавлен callback - заглушка для тестирования
/api/wordpress/callback

Настройки вынесены в .env
PLUGINS_DB_DATABASE=nn_plugins
PLUGINS_DB_USERNAME=nn
PLUGINS_DB_PASSWORD=nn_api
ENCRYPT_KEY=
ADMIN_IP=
EXPIRED_PIN_SECONDS=300
