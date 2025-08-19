### Ініціаплізація проєкту

`php artisan migrate --seed`

### Запуск скрипта

`php artisan parse`

### База даних

використовується sqlite, і ціла БД доступна `database/database.sqlite`

### Зображення

Усі аватарки зберігаються в
`/storage/app/private/images`

щоб усі ці файли були доступні через браузер потрібно виконати команду
`php artisan storage:link`
