### `Laravel Commands Used:`

### To migrate single file

```
php artisan migrate:refresh --path=database/migrations/{file_name}.php
```

### To migrate all files

```
php artisan migrate
```

### To make model , migration and resource controller file all at once

```
php artisan make:model {ModelName} -mrc
m - migration, r - resource, c - controller
```

### To clear cache and optimize your project

```
php artisan optimize
```

### To work with helper functions

```
add this line in composer.json file
"files": [
    "app/helpers.php"
]
and run this command

composer dump-autoload
```
