## Установка

Настройка

файл .env 

базы данных

DB_DATABASE=nn_base

DB_USERNAME=nn

DB_PASSWORD=nn_api


CONTROL_DB_DATABASE=nn_control

CONTROL_DB_USERNAME=nn

CONTROL_DB_PASSWORD=nn_api




## Документация Api

[/api/documentation](/api/documentation#/default)

Обновление
php artisan l5-swagger:generate

Авторизация

Введите токен авторизации в формате 'Bearer {token}'

пример: Bearer 4|YIWCgjNc9c9bD6wEez07lM0IHRJxkCBzhWdzz24Q

url открываются по клику можно сделать пробный запрос 'Try it out'

Примеры:
GET /api/comtrols - все записи таблицы control_domains

GET /api/comtrols/{cid} - запись по cid 

POST /api/comtrols - добавление новых записей

Пример

GET /api/controls/did_data/1?domain[]=google.com&domain[]=yandex.ru


