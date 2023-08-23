## Установка

git clone git@github.com:xserg/nn_api.git

composer install


Настройка

файл .env 

базы данных

DB_DATABASE=nn_api
DB_USERNAME=nn
DB_PASSWORD=nn_api

BASE_DB_DATABASE=nn_base
BASE_DB_USERNAME=nn
BASE_DB_PASSWORD=nn_api

CONTROL_DB_DATABASE=nn_control
CONTROL_DB_USERNAME=nn
CONTROL_DB_PASSWORD=nn_api


Добавить таблицы 

users.sql, personal_access_tokens.sql

## Документация Api

[/api/documentation](/api/documentation#/default)

Обновление
php artisan l5-swagger:generate

Авторизация
/api/register
/api/login
возвращает токен авторизации апи

Введите токен авторизации в формате 'Bearer {token}'

пример: Bearer 4|YIWCgjNc9c9bD6wEez07lM0IHRJxkCBzhWdzz24Q

url открываются по клику можно сделать пробный запрос 'Try it out'

Тестировать лучше в postman

Примеры:

GET /api/controls - все записи таблицы control_domains

GET /api/controls/{cid} - запись по cid 

POST /api/controls - добавление новых записей




