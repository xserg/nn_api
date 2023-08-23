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